<?php
/**
 * ينشئ رابط رفع مؤقتًا إلى مخزن S3-compatible.
 * بيانات الصورة لا تمر عبر Vercel؛ المتصفح يرفع مباشرة إلى مخزن الملفات.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/Helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/Services/ObjectStorageService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function uploadUrlResponse(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    uploadUrlResponse(405, ['error' => 'طريقة الطلب غير مسموحة.']);
}
if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    uploadUrlResponse(403, ['error' => 'تعذر التحقق من الطلب. حدّث الصفحة ثم أعد المحاولة.']);
}
if (!isset($config['app']['key']) || strlen((string)$config['app']['key']) < 32 || str_contains((string)$config['app']['key'], 'CHANGE_THIS')) {
    uploadUrlResponse(500, ['error' => 'يجب ضبط APP_KEY بقيمة عشوائية سرية لا تقل عن 32 حرفًا.']);
}

$input = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($input)) {
    uploadUrlResponse(400, ['error' => 'بيانات الطلب غير صالحة.']);
}
$name = (string)($input['name'] ?? '');
$size = filter_var($input['size'] ?? null, FILTER_VALIDATE_INT);
$mime = (string)($input['type'] ?? '');
$allowed = $config['upload']['allowed_mime_types'];

if ($size === false || $size < 1 || $size > (int)$config['upload']['max_file_size']) {
    uploadUrlResponse(413, ['error' => 'حجم الصورة يجب ألا يتجاوز 30 MiB.']);
}
if (!isset($allowed[$mime])) {
    uploadUrlResponse(415, ['error' => 'نوع الصورة غير مدعوم.']);
}
$name = basename(str_replace('\\', '/', $name));
$name = trim(preg_replace('/[[:cntrl:]]/u', '', $name) ?? '');
if ($name === '') {
    $name = 'image.' . $allowed[$mime];
}
$name = function_exists('mb_substr') ? mb_substr($name, 0, 250, 'UTF-8') : substr($name, 0, 250);

$key = 'images/' . bin2hex(random_bytes(24)) . '.' . $allowed[$mime];
$issuedAt = time();
$signature = hash_hmac('sha256', implode("\n", [(string)$issuedAt, $key, $name, (string)$size, $mime]), (string)$config['app']['key']);

try {
    $storage = new ObjectStorageService($config['storage']);
    $url = $storage->presignedUploadUrl($key, $mime);
    uploadUrlResponse(200, [
        'upload_url' => $url,
        'key' => $key,
        'upload_token' => $signature,
        'issued_at' => $issuedAt,
        'name' => $name,
        'size' => (int)$size,
        'mime_type' => $mime,
        'extension' => $allowed[$mime],
    ]);
} catch (Throwable $ex) {
    error_log('Unable to create signed upload URL: ' . $ex->getMessage());
    uploadUrlResponse(500, ['error' => 'تعذر تجهيز رفع الصورة. تحقق من إعدادات التخزين.']);
}