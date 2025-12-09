<?php
namespace MusicStore;

class LocalizationManager
{
    private array $data = [];
    private string $currentLocale = 'en_US';

    public function __construct()
    {
        $this->loadLocale($this->currentLocale);
    }

    public function setLocale(string $locale): void
    {
        $supported = ['en_US', 'de_DE', 'uk_UA'];
        if (!in_array($locale, $supported)) {
            throw new \Exception("Unsupported locale: $locale");
        }
        $this->currentLocale = $locale;
        $this->loadLocale($locale);
    }

    private function loadLocale(string $locale): void
    {
        $path = __DIR__ . "/../data/$locale.php";
        if (!file_exists($path)) {
            throw new \Exception("Locale file not found: $path");
        }
        $this->data = include $path;
    }

    public function get(string $key): array
    {
        if (!isset($this->data[$key])) {
            throw new \Exception("Key not found: $key");
        }
        return $this->data[$key];
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }
}
