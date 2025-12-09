<?php
namespace MusicStore;

class MusicGenerator
{
    private SeededRandom $rng;
    private int $seed;
    private const SAMPLE_RATE = 44100;
    private const DURATION = 6; // 6 seconds

    public function __construct(int $seed)
    {
        $this->rng = new SeededRandom($seed);
        $this->seed = $seed;
    }

    public function generateWaveData(int $songIndex): string
    {
        $baseSeed = $this->rng->seedForIndex($songIndex);
        $samples = $this->generateAudioSamples($baseSeed);
        return $this->encodeWAV($samples);
    }

    private function generateAudioSamples(int $seed): array
    {
        $samples = [];
        $sampleCount = self::SAMPLE_RATE * self::DURATION;

        $scale = [261.63, 293.66, 329.63, 392.00, 440.00, 523.25];

        $currentTime = 0;
        $noteDuration = 0.3;

        for ($i = 0; $i < 30 && $currentTime < $sampleCount; $i++) {
            $noteSeed = $seed + $i;
            $noteIndex = $this->rng->randomInt($noteSeed, 0, count($scale) - 1);
            $frequency = $scale[$noteIndex];
            $noteSamples = (int)($noteDuration * self::SAMPLE_RATE);

            for ($j = 0; $j < $noteSamples && $currentTime < $sampleCount; $j++) {
                $t = ($currentTime++) / self::SAMPLE_RATE;

                $envelope = 1.0;
                $attackSamples = (int)(0.01 * self::SAMPLE_RATE);
                $releaseSamples = (int)(0.1 * self::SAMPLE_RATE);

                if ($j < $attackSamples) {
                    $envelope = $j / max(1, $attackSamples);
                } elseif ($j > $noteSamples - $releaseSamples) {
                    $envelope = ($noteSamples - $j) / max(1, $releaseSamples);
                }

                $sample = sin(2 * M_PI * $frequency * $t) * 0.3 * $envelope;
                $samples[] = $sample;
            }
        }

        while (count($samples) < $sampleCount) {
            $samples[] = 0;
        }

        return array_slice($samples, 0, $sampleCount);
    }

    private function encodeWAV(array $samples): string
    {
        $numChannels = 1;
        $byteRate = self::SAMPLE_RATE * $numChannels * 2;
        $blockAlign = $numChannels * 2;
        $bitsPerSample = 16;
        $dataSize = count($samples) * 2;

        $wav = '';

        $wav .= 'RIFF';
        $wav .= pack('V', 36 + $dataSize);
        $wav .= 'WAVE';

        $wav .= 'fmt ';
        $wav .= pack('V', 16);
        $wav .= pack('v', 1);
        $wav .= pack('v', $numChannels);
        $wav .= pack('V', self::SAMPLE_RATE);
        $wav .= pack('V', $byteRate);
        $wav .= pack('v', $blockAlign);
        $wav .= pack('v', $bitsPerSample);

        $wav .= 'data';
        $wav .= pack('V', $dataSize);

        foreach ($samples as $sample) {
            $intSample = (int)($sample * 32767);
            $intSample = max(-32768, min(32767, $intSample));
            $wav .= pack('s', $intSample);
        }

        return 'data:audio/wav;base64,' . base64_encode($wav);
    }
}
