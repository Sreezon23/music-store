<?php
namespace MusicStore;

class SongGenerator
{
    private SeededRandom $rng;
    private LocalizationManager $localization;
    private float $likesPerSong;

    public function __construct(
        int $seed,
        string $locale,
        float $likesPerSong
    ) {
        $this->rng = new SeededRandom($seed);
        $this->localization = new LocalizationManager();
        $this->localization->setLocale($locale);
        $this->likesPerSong = max(0, min(10, $likesPerSong));
    }

    public function generateSong(int $index): array
    {
        $baseSeed = $this->rng->seedForIndex($index);

        $titles = $this->localization->get('titles');
        $title = $this->rng->randomArray($baseSeed, $titles);

        $artists = $this->localization->get('artists');
        $artist = $this->rng->randomArray($baseSeed + 1, $artists);

        $albums = $this->localization->get('albums');
        $album = $this->rng->randomArray($baseSeed + 2, $albums);

        $genres = $this->localization->get('genres');
        $genre = $this->rng->randomArray($baseSeed + 3, $genres);

        $likes = $this->calculateLikes($baseSeed + 4);


        $reviews = $this->localization->get('reviews');
        $review = $this->rng->randomArray($baseSeed + 5, $reviews);

        return [
            'index' => $index,
            'title' => $title,
            'artist' => $artist,
            'album' => $album,
            'genre' => $genre,
            'likes' => $likes,
            'review' => $review,
            'seed' => $baseSeed
        ];
    }

    private function calculateLikes(int $seed): int
    {
        $fractionalPart = $this->likesPerSong - floor($this->likesPerSong);
        $baseLikes = (int)floor($this->likesPerSong);

        if ($fractionalPart > 0) {
            $rand = $this->rng->randomFloat($seed);
            if ($rand < $fractionalPart) {
                $baseLikes++;
            }
        }

        return $baseLikes;
    }

    public function generateBatch(int $page = 1, int $pageSize = 10): array
    {
        $songs = [];
        $startIndex = ($page - 1) * $pageSize + 1;

        for ($i = 0; $i < $pageSize; $i++) {
            $songs[] = $this->generateSong($startIndex + $i);
        }

        return $songs;
    }
}
