<?php
/** إنشاء رابط تنزيل مؤقت دون كشف بيانات اعتماد مخزن الكائنات. */
declare(strict_types=1);
require dirname(__DIR__) . '/Helpers/bootstrap.php';
require_once dirname(__DIR__) . '/Services/ObjectStorageService.php';
$id = positiveImageId($_GET['id'] ?? null);
$image = $id ? $imageRepository->find($id) : null;
if (!$image) { http_response_code(404); exit('الصورة غير موجودة.'); }
try {
    $storage = new ObjectStorageService($config['storage']);
    $url = $storage->presignedDownloadUrl((string)$image['storage_path'], (string)$image['mime_type'], (string)$image['original_name']);
    header('Cache-Control: private, no-store');
    header('Location: ' . $url, true, 302);
    exit;
} catch (Throwable $ex) {
    error_log('Image download URL failed: ' . $ex->getMessage());
    http_response_code(502);
    exit('تعذر تنزيل الصورة حاليًا.');
}