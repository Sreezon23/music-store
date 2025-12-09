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

    /**
     * Generate a simple WAV file for browser playback
     * Returns base64-encoded WAV data as a data URI
     */
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

        // Musical pentatonic scale (C major pentatonic) for pleasant sound
        $scale = [261.63, 293.66, 329.63, 392.00, 440.00, 523.25]; // C D E G A C

        $currentTime = 0;
        $noteDuration = 0.3; // Duration per note in seconds

        // Generate a simple melody
        for ($i = 0; $i < 30 && $currentTime < $sampleCount; $i++) {
            $noteSeed = $seed + $i;
            $noteIndex = $this->rng->randomInt($noteSeed, 0, count($scale) - 1);
            $frequency = $scale[$noteIndex];
            $noteSamples = (int)($noteDuration * self::SAMPLE_RATE);

            for ($j = 0; $j < $noteSamples && $currentTime < $sampleCount; $j++) {
                $t = ($currentTime++) / self::SAMPLE_RATE;

                // Apply envelope (attack-sustain-release)
                $envelope = 1.0;
                $attackSamples = (int)(0.01 * self::SAMPLE_RATE); // 10ms attack
                $releaseSamples = (int)(0.1 * self::SAMPLE_RATE); // 100ms release

                if ($j < $attackSamples) {
                    $envelope = $j / max(1, $attackSamples);
                } elseif ($j > $noteSamples - $releaseSamples) {
                    $envelope = ($noteSamples - $j) / max(1, $releaseSamples);
                }

                // Generate sine wave
                $sample = sin(2 * M_PI * $frequency * $t) * 0.3 * $envelope;
                $samples[] = $sample;
            }
        }

        // Pad with silence if needed
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

        // RIFF header
        $wav .= 'RIFF';
        $wav .= pack('V', 36 + $dataSize);
        $wav .= 'WAVE';

        // fmt sub-chunk
        $wav .= 'fmt ';
        $wav .= pack('V', 16); // Subchunk1Size
        $wav .= pack('v', 1); // AudioFormat (1 = PCM)
        $wav .= pack('v', $numChannels);
        $wav .= pack('V', self::SAMPLE_RATE);
        $wav .= pack('V', $byteRate);
        $wav .= pack('v', $blockAlign);
        $wav .= pack('v', $bitsPerSample);

        // data sub-chunk
        $wav .= 'data';
        $wav .= pack('V', $dataSize);

        // Audio samples - convert float to 16-bit integer
        foreach ($samples as $sample) {
            $intSample = (int)($sample * 32767);
            // Clamp to valid 16-bit range
            $intSample = max(-32768, min(32767, $intSample));
            $wav .= pack('s', $intSample);
        }

        return 'data:audio/wav;base64,' . base64_encode($wav);
    }
}
