<?php
/**
 * يتحقق من وجود الملف المرفوع في التخزين ويسجل بياناته في MySQL.
 * لا يقبل هذا المسار مفتاح تخزين من دون توقيع HMAC صادر عن upload-url.php.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/Helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/Services/ObjectStorageService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function uploadCompleteResponse(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    uploadCompleteResponse(405, ['error' => 'طريقة الطلب غير مسموحة.']);
}
if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    uploadCompleteResponse(403, ['error' => 'تعذر التحقق من الطلب.']);
}
$input = json_decode((string)file_get_contents('php://input'), true);
if (!is_array($input)) {
    uploadCompleteResponse(400, ['error' => 'بيانات الطلب غير صالحة.']);
}
$key = (string)($input['key'] ?? '');
$name = (string)($input['name'] ?? '');
$size = filter_var($input['size'] ?? null, FILTER_VALIDATE_INT);
$mime = (string)($input['mime_type'] ?? '');
$extension = (string)($input['extension'] ?? '');
$signature = (string)($input['upload_token'] ?? '');
$issuedAt = filter_var($input['issued_at'] ?? null, FILTER_VALIDATE_INT);
$allowed = $config['upload']['allowed_mime_types'];

if (!preg_match('#^images/[a-f0-9]{48}\.[a-z0-9]{1,8}$#', $key)
    || $size === false || $size < 1 || $size > (int)$config['upload']['max_file_size']
    || !isset($allowed[$mime]) || $allowed[$mime] !== $extension
    || $signature === '' || $issuedAt === false || $issuedAt < time() - 900 || $issuedAt > time() + 60) {
    uploadCompleteResponse(400, ['error' => 'بيانات الصورة غير صالحة.']);
}
$expected = hash_hmac('sha256', implode("\n", [(string)$issuedAt, $key, $name, (string)$size, $mime]), (string)$config['app']['key']);
if (!hash_equals($expected, $signature)) {
    uploadCompleteResponse(403, ['error' => 'رمز إكمال الرفع غير صالح.']);
}

try {
    $storage = new ObjectStorageService($config['storage']);
    $head = $storage->headObject($key);
    $actualSize = (int)($head['ContentLength'] ?? 0);
    $actualMime = (string)($head['ContentType'] ?? '');
    if ($actualSize !== (int)$size || $actualSize < 1 || $actualSize > (int)$config['upload']['max_file_size'] || $actualMime !== $mime) {
        $storage->deleteObject($key);
        uploadCompleteResponse(422, ['error' => 'الملف المخزن لا يطابق الحجم أو نوع المحتوى المعلن.']);
    }

    $existing = $imageRepository->findByStoredName(basename($key));
    if ($existing !== null) {
        if ((string)$existing['storage_path'] === $key) {
            uploadCompleteResponse(200, ['ok' => true, 'already_registered' => true]);
        }
        uploadCompleteResponse(409, ['error' => 'مفتاح التخزين مسجل مسبقًا.']);
    }

    $imageRepository->create([
        'original_name' => $name,
        'stored_name' => basename($key),
        'mime_type' => $mime,
        'file_extension' => $extension,
        'file_size' => $actualSize,
        'storage_path' => $key,
        'image_width' => null,
        'image_height' => null,
    ]);
    uploadCompleteResponse(201, ['ok' => true]);
} catch (PDOException $ex) {
    error_log('Could not register uploaded image: ' . $ex->getMessage());
    // لا نحذف الكائن هنا: قد يكون السجل قد أُنشئ بالفعل ثم فشل الرد، وحذفه يكسر الصورة المسجلة.
    uploadCompleteResponse(500, ['error' => 'تم استلام الملف لكن تعذر تسجيل بياناته. أعد المحاولة.']);
} catch (Throwable $ex) {
    error_log('Upload completion failed: ' . $ex->getMessage());
    uploadCompleteResponse(404, ['error' => 'تعذر التحقق من الملف المرفوع. تأكد من إعدادات التخزين ثم أعد المحاولة.']);
}