<?php
require_once __DIR__ . '/../vendor/autoload.php';

use MusicStore\SongGenerator;
use MusicStore\SeededRandom;
use MusicStore\MusicGenerator;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'getSongs':
            handleGetSongs();
            break;

        case 'getAudio':
            handleGetAudio();
            break;

        case 'getLocales':
            echo json_encode([
                'locales' => [
                    ['code' => 'en_US', 'name' => 'English (USA)'],
                    ['code' => 'de_DE', 'name' => 'Deutsch (Deutschland)'],
                    ['code' => 'uk_UA', 'name' => 'Українська (Україна)']
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGetSongs(): void
{
    $seed = isset($_GET['seed']) ? (int)$_GET['seed'] : 0;
    $locale = $_GET['locale'] ?? 'en_US';
    $likes = isset($_GET['likes']) ? (float)$_GET['likes'] : 5.0;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;

    $generator = new SongGenerator($seed, $locale, $likes);
    $songs = $generator->generateBatch($page, $pageSize);

    echo json_encode([
        'success' => true,
        'page' => $page,
        'pageSize' => $pageSize,
        'songs' => $songs,
        'seed' => $seed,
        'locale' => $locale,
        'likes' => $likes
    ]);
}

function handleGetAudio(): void
{
    $seed = isset($_GET['seed']) ? (int)$_GET['seed'] : 0;
    $songIndex = isset($_GET['index']) ? (int)$_GET['index'] : 1;

    $rng = new SeededRandom($seed);
    $audioSeed = $rng->seedForIndex($songIndex);

    $musicGen = new MusicGenerator($audioSeed);
    $wavData = $musicGen->generateWaveData($songIndex);

    echo json_encode([
        'success' => true,
        'audio' => $wavData
    ]);
}
