<?php
/**
 * تهيئة التطبيق وقراءة الإعدادات من Environment Variables أو ملف config.php محلي.
 * هذا الأسلوب يتجنب الحاجة إلى ملف إعدادات سري ضمن نشر Vercel.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$localConfig = dirname(__DIR__) . '/Config/config.php';
$exampleConfig = dirname(__DIR__) . '/Config/config.example.php';
$config = require is_file($localConfig) ? $localConfig : $exampleConfig;

$env = static function (string $name, mixed $default = null): mixed {
    $value = getenv($name);
    return $value === false || $value === '' ? $default : $value;
};

/* متغيرات الاتصال بقاعدة البيانات؛ إذا لم توجد تُستخدم القيم من ملف الإعدادات المحلي. */
$config['database']['host'] = $env('DB_HOST', $config['database']['host'] ?? '127.0.0.1');
$config['database']['port'] = (int)$env('DB_PORT', $config['database']['port'] ?? 3306);
$config['database']['name'] = $env('DB_DATABASE', $config['database']['name'] ?? '');
$config['database']['username'] = $env('DB_USERNAME', $config['database']['username'] ?? '');
$config['database']['password'] = $env('DB_PASSWORD', $config['database']['password'] ?? '');
$config['database']['charset'] = $env('DB_CHARSET', $config['database']['charset'] ?? 'utf8mb4');

/* بيانات اعتماد مخزن S3-compatible؛ لا تضع هذه القيم في الملفات المتعقبة بـ Git. */
$config['storage']['endpoint'] = $env('S3_ENDPOINT', $config['storage']['endpoint'] ?? '');
$config['storage']['region'] = $env('S3_REGION', $config['storage']['region'] ?? 'auto');
$config['storage']['bucket'] = $env('S3_BUCKET', $config['storage']['bucket'] ?? '');
$config['storage']['access_key'] = $env('S3_ACCESS_KEY_ID', $config['storage']['access_key'] ?? '');
$config['storage']['secret_key'] = $env('S3_SECRET_ACCESS_KEY', $config['storage']['secret_key'] ?? '');
$config['app']['key'] = $env('APP_KEY', $config['app']['key'] ?? '');
$config['app']['timezone'] = $env('APP_TIMEZONE', $config['app']['timezone'] ?? 'UTC');

date_default_timezone_set((string)$config['app']['timezone']);

/* Composer dependencies مثل AWS SDK for PHP. */
$autoload = $root . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

require_once dirname(__DIR__) . '/Helpers/security.php';
require_once dirname(__DIR__) . '/Database/connection.php';
require_once dirname(__DIR__) . '/Repositories/ImageRepository.php';
require_once dirname(__DIR__) . '/Services/ImageService.php';

try {
    $pdo = createDatabaseConnection($config['database']);
    $imageRepository = new ImageRepository($pdo);
    $imageService = new ImageService($config['upload'], $config['paths']);
} catch (Throwable $ex) {
    error_log('Application bootstrap failed: ' . $ex->getMessage());
    http_response_code(500);
    exit('تعذر تشغيل التطبيق. راجع متغيرات البيئة واتصال قاعدة البيانات.');
}
