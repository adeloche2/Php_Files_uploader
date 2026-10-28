<?php
/**
 * نقطة دخول موحدة لـ Vercel PHP Runtime.
 * vercel.json يوجه المسارات الديناميكية إلى هنا، بينما تُخدم ملفات CSS/JS الثابتة من filesystem.
 */
declare(strict_types=1);

$path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';

switch ($path) {
    case '/':
    case '/index.php':
        require dirname(__DIR__) . '/public/index.php';
        break;

    case '/image.php':
        require dirname(__DIR__) . '/public/image.php';
        break;

    case '/download.php':
        require dirname(__DIR__) . '/public/download.php';
        break;

    case '/api/upload-url':
    case '/api/upload-url.php':
        require __DIR__ . '/upload-url.php';
        break;

    case '/api/upload-complete':
    case '/api/upload-complete.php':
        require __DIR__ . '/upload-complete.php';
        break;

    default:
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
}