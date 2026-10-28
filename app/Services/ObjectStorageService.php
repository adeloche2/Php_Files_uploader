<?php
/**
 * محول تخزين للملفات عبر واجهة S3-compatible.
 * يعمل مع Cloudflare R2 وAWS S3 ومزودي S3-compatible الآخرين.
 */
declare(strict_types=1);

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

final class ObjectStorageService
{
    private S3Client $client;
    private string $bucket;

    public function __construct(array $settings)
    {
        $endpoint = trim((string)($settings['endpoint'] ?? ''));
        $region = trim((string)($settings['region'] ?? 'auto'));
        $this->bucket = trim((string)($settings['bucket'] ?? ''));
        $accessKey = trim((string)($settings['access_key'] ?? ''));
        $secretKey = trim((string)($settings['secret_key'] ?? ''));

        if ($endpoint === '' || $this->bucket === '' || $accessKey === '' || $secretKey === '') {
            throw new RuntimeException('S3 storage is not configured. Set S3_ENDPOINT, S3_BUCKET, S3_ACCESS_KEY_ID and S3_SECRET_ACCESS_KEY.');
        }

        $options = [
            'version' => 'latest',
            'region' => $region !== '' ? $region : 'auto',
            'credentials' => ['key' => $accessKey, 'secret' => $secretKey],
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
        ];
        $this->client = new S3Client($options);
    }

    /** إنشاء رابط PUT مؤقت؛ محتوى الصورة لا يمر عبر دالة Vercel. */
    public function presignedUploadUrl(string $key, string $mimeType): string
    {
        $command = $this->client->getCommand('PutObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ContentType' => $mimeType,
        ]);
        return (string)$this->client->createPresignedRequest($command, '+10 minutes')->getUri();
    }

    /** التحقق من الحجم ونوع المحتوى بعد رفع المتصفح المباشر. */
    public function headObject(string $key): array
    {
        return $this->client->headObject(['Bucket' => $this->bucket, 'Key' => $key])->toArray();
    }

    /** رابط عرض مؤقت؛ يسمح ببقاء الحاوية خاصة. */
    public function presignedViewUrl(string $key, string $mimeType): string
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ResponseContentType' => $mimeType,
            'ResponseContentDisposition' => 'inline',
        ]);
        return (string)$this->client->createPresignedRequest($command, '+10 minutes')->getUri();
    }

    /** رابط تنزيل مؤقت مع اسم ملف آمن ومحدد من قاعدة البيانات. */
    public function presignedDownloadUrl(string $key, string $mimeType, string $filename): string
    {
        $fallback = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'image';
        $disposition = 'attachment; filename="' . str_replace('"', '', $fallback) . '"; filename*=UTF-8\'\'' . rawurlencode($filename);
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
            'ResponseContentType' => $mimeType,
            'ResponseContentDisposition' => $disposition,
        ]);
        return (string)$this->client->createPresignedRequest($command, '+10 minutes')->getUri();
    }

    /** حذف كائن بعد طلب حذف مصادق عليه من التطبيق. */
    public function deleteObject(string $key): void
    {
        $this->client->deleteObject(['Bucket' => $this->bucket, 'Key' => $key]);
    }
}