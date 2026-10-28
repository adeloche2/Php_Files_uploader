<?php
/** الصفحة الرئيسية: معرض الصور ورفعها مباشرة إلى مخزن S3-compatible. */
declare(strict_types=1);
require dirname(__DIR__) . '/Helpers/bootstrap.php';
require_once dirname(__DIR__) . '/Services/ObjectStorageService.php';

$message = '';
$type = 'info';

/* معالجة الحذف؛ المصادقة وتحديد ملكية الصور يجب إضافتهما قبل النشر العام. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $message = 'تعذر التحقق من الطلب.';
        $type = 'danger';
    } else {
        $id = positiveImageId($_POST['image_id'] ?? null);
        $image = $id ? $imageRepository->find($id) : null;
        if (!$image) {
            $message = 'الصورة غير موجودة.';
            $type = 'warning';
        } else {
            try {
                $storage = new ObjectStorageService($config['storage']);
                $storage->deleteObject((string)$image['storage_path']);
                $imageRepository->delete($id);
                $message = 'تم حذف الصورة.';
                $type = 'success';
            } catch (Throwable $ex) {
                error_log('Image deletion failed: ' . $ex->getMessage());
                $message = 'تعذر حذف الصورة. راجع إعدادات التخزين.';
                $type = 'danger';
            }
        }
    }
}

/* الرفع الفعلي يتم عبر JavaScript ورابط PUT موقّع، لا عبر جسم طلب PHP. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $message = 'يتطلب رفع الصور اتصال JavaScript. حدّث الصفحة ثم أعد المحاولة.';
    $type = 'warning';
}

$stats = $imageRepository->stats();
$images = $imageRepository->all(100);
$max = (int)$config['upload']['max_files_per_request'];
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>مركز الصور</title>
<link rel="stylesheet" href="/assets/css/style.css">
<script defer src="/assets/js/uploader.js"></script>
</head>
<body>
<main class="container">
<header class="site-header">
  <div class="brand-mark">IMG</div>
  <div><p class="eyebrow">IMAGE WORKSPACE</p><h1>مركز الصور</h1><p class="muted">ارفع صورك ونظّمها في مكان واحد.</p></div>
</header>
<section class="stats">
  <article class="stat-card"><span>عدد الصور</span><strong><?= (int)$stats['image_count'] ?></strong></article>
  <article class="stat-card"><span>المساحة المستخدمة</span><strong><?= e(formatBytes((int)$stats['total_bytes'])) ?></strong></article>
  <article class="stat-card"><span>حد الصورة</span><strong>30 MiB</strong></article>
</section>
<?php if ($message !== ''): ?><div class="notice notice-<?= e($type) ?>" role="status"><?= e($message) ?></div><?php endif; ?>
<section class="panel">
  <div class="section-heading"><div><p class="eyebrow">UPLOAD</p><h2>رفع صور جديدة</h2></div><span class="soft-badge">حتى <?= $max ?> صور</span></div>
  <form id="upload-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <label id="drop-zone" class="drop-zone" for="images">
      <span class="upload-symbol">↑</span><strong>اسحب الصور إلى هنا</strong>
      <span class="muted">أو اضغط لاختيار الملفات</span><span class="browse-button">اختيار الصور</span>
      <span class="file-rules">JPEG · PNG · WebP · GIF · BMP — حتى 30 MiB للصورة</span>
      <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp" multiple required>
    </label>
    <div id="selected-files" class="selected-files" aria-live="polite"></div>
    <div class="upload-footer"><p class="muted small">يتم إرسال الصور مباشرة إلى التخزين الدائم، دون تمرير محتواها عبر Vercel.</p><button class="primary-button" type="submit">رفع الصور</button></div>
    <div id="upload-progress" class="progress-wrap" hidden><div class="progress-label"><span id="progress-label">جاري الإرسال...</span><span id="progress-percent">0%</span></div><progress id="progress-bar" value="0" max="100"></progress></div>
  </form>
</section>
<section class="gallery-section">
  <div class="section-heading"><div><p class="eyebrow">LIBRARY</p><h2>معرض الصور</h2></div><span class="muted"><?= count($images) ?> أحدث صورة</span></div>
  <?php if (!$images): ?>
    <div class="empty-state"><span>▧</span><h3>لا توجد صور بعد</h3><p>ارفع أول صورة لتظهر هنا.</p></div>
  <?php else: ?>
    <div class="gallery-grid">
    <?php foreach ($images as $im): ?>
      <article class="image-card">
        <a class="image-preview" href="/image.php?id=<?= (int)$im['id'] ?>" target="_blank" rel="noopener"><img src="/image.php?id=<?= (int)$im['id'] ?>" alt="<?= e($im['original_name']) ?>" loading="lazy"></a>
        <div class="image-info">
          <h3 title="<?= e($im['original_name']) ?>"><?= e($im['original_name']) ?></h3>
          <p><?= e(formatBytes((int)$im['file_size'])) ?> · <?= $im['image_width'] ? (int)$im['image_width'] . '×' . (int)$im['image_height'] : 'الأبعاد غير مسجلة' ?></p>
          <p class="muted small"><?= e((string)$im['uploaded_at']) ?></p>
          <div class="card-actions">
            <a class="secondary-button" href="/download.php?id=<?= (int)$im['id'] ?>">تنزيل</a>
            <form method="post" class="delete-form" onsubmit="return confirm('هل تريد حذف الصورة نهائيًا؟')">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="image_id" value="<?= (int)$im['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="danger-button" type="submit">حذف</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<footer class="site-footer">مركز الصور · PHP + MySQL · RTL</footer>
</main>
</body>
</html>