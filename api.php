<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://ai-ready-check.de');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { json_error('Nur POST erlaubt', 405); }

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Crawler.php';
require_once __DIR__ . '/Generator.php';
require_once __DIR__ . '/providers/ProviderFactory.php';

$config = require __DIR__ . '/config.php';

// Rate Limiting
rateLimit($config);

// Input
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { json_error('Ungültiger Request-Body'); }

$action = $body['action'] ?? 'full';

try {
    if ($action === 'crawl') {
        // Nur crawlen, noch nicht generieren
        $url = filter_var(trim($body['url'] ?? ''), FILTER_VALIDATE_URL);
        if (!$url) json_error('Ungültige URL');

        $crawler = new Crawler($config);
        $page    = $crawler->fetch($url);

        json_ok([
            'page'     => $page,
            'provider' => ProviderFactory::available()[$config['ai_provider']] ?? 'Regelbasiert',
            'ai_ready' => $config['ai_provider'] !== 'none' && !empty($config['ai_api_key']),
        ]);
    }

    if ($action === 'generate') {
        $page      = $body['page']      ?? null;
        $overrides = $body['overrides'] ?? [];
        if (!$page) json_error('Seitendaten fehlen');

        $provider  = ProviderFactory::create($config);
        $generator = new MetaTagGenerator($provider);
        $result    = $generator->generate($page, $overrides);

        json_ok($result);
    }

    json_error('Unbekannte Aktion');

} catch (InvalidArgumentException $e) {
    json_error($e->getMessage(), 400);
} catch (RuntimeException $e) {
    json_error($e->getMessage(), 502);
} catch (Throwable $e) {
    error_log('[metatag-optimizer] ' . $e->getMessage());
    json_error('Interner Fehler', 500);
}

// ── Hilfsfunktionen ───────────────────────────────────────────────────────────

function json_ok(array $data): never
{
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $msg, int $code = 400): never
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function rateLimit(array $config): void
{
    $dir  = $config['rate_limit_dir'];
    $max  = $config['rate_limit_requests'];
    $win  = $config['rate_limit_window'];
    $ip   = preg_replace('/[^a-f0-9.:]/i', '', $_SERVER['REMOTE_ADDR'] ?? '0');
    $file = "$dir/rl_$ip.json";

    if (!is_dir($dir)) mkdir($dir, 0700, true);

    $now  = time();
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['ts' => $now, 'count' => 0];

    if ($now - $data['ts'] > $win) {
        $data = ['ts' => $now, 'count' => 0];
    }

    $data['count']++;
    file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > $max) {
        json_error("Zu viele Anfragen – bitte warte {$win} Sekunden", 429);
    }
}
