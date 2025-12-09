<?php
namespace MusicStore;

class SeededRandom
{
    private int $seed;

    public function __construct(int $seed)
    {
        $this->seed = abs($seed) % (PHP_INT_MAX - 1);
    }

    public function seedForIndex(int $index): int
    {
        $combined = abs((int)(($this->seed * 73856093) ^ ($index * 19349663)));
        return $combined % PHP_INT_MAX;
    }

    public function randomInt(int $seed, int $min, int $max): int
    {
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }
        
        mt_srand($seed);
        return mt_rand($min, $max);
    }

    public function randomFloat(int $seed, float $min = 0, float $max = 1): float
    {
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }
        
        mt_srand($seed);
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

    public function randomArray(int $seed, array $array)
    {
        if (empty($array)) {
            return null;
        }
        
        mt_srand($seed);
        $index = mt_rand(0, count($array) - 1);
        return $array[$index];
    }
}
