<?php
 $uploadDir = 'uploads/';
 $maxFileSize = 50 * 1024 * 1024;
 $allowedTypes = [
    'image'    => ['jpg','jpeg','png','gif','webp','svg','bmp','ico'],
    'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','csv'],
    'archive'  => ['zip','rar','7z','tar','gz'],
    'video'    => ['mp4','avi','mov','mkv','webm'],
    'audio'    => ['mp3','wav','ogg','flac','aac'],
    'code'     => ['php','html','css','js','json','xml','sql','py']
];

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

 $message = '';
 $messageType = '';

// رفع الملفات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $files = $_FILES['files'];
    $successCount = 0;
    $errors = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $name = $files['name'][$i];
        $size = $files['size'][$i];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($size > $maxFileSize) {
            $errors[] = "\"$name\" يتجاوز 50 ميجابايت";
            continue;
        }

        $allowed = false;
        foreach ($allowedTypes as $exts) {
            if (in_array($ext, $exts)) { $allowed = true; break; }
        }

        if (!$allowed) {
            $errors[] = "\"$name\" نوع غير مدعوم";
            continue;
        }

        $newName = uniqid('f_', true) . '.' . $ext;
        if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $newName)) {
            $successCount++;
        }
    }

    if ($successCount > 0) {
        $message = "تم رفع $successCount ملف بنجاح";
        $messageType = 'success';
    }
    if (!empty($errors)) {
        $message .= ($message ? ' | ' : '') . implode(' | ', $errors);
        $messageType = $messageType === 'success' ? 'warning' : 'danger';
    }
}

// حذف ملف
if (isset($_GET['delete'])) {
    $file = $uploadDir . basename($_GET['delete']);
    if (file_exists($file) && unlink($file)) {
        $message = 'تم حذف الملف';
        $messageType = 'success';
    }
}

// جلب الملفات الموجودة
 $existingFiles = [];
 $totalSize = 0;
if (is_dir($uploadDir)) {
    $items = scandir($uploadDir, SCANDIR_SORT_DESCENDING);
    foreach ($items as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $uploadDir . $f;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $existingFiles[] = [
            'name' => $f,
            'size' => filesize($path),
            'ext'  => $ext,
            'time' => filemtime($path)
        ];
        $totalSize += filesize($path);
    }
}

function formatSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function getCategory($ext) {
    $map = [
        'jpg|jpeg|png|gif|webp|svg|bmp|ico' => 'image',
        'pdf|doc|docx|xls|xlsx|ppt|pptx|txt|rtf|csv' => 'document',
        'zip|rar|7z|tar|gz' => 'archive',
        'mp4|avi|mov|mkv|webm' => 'video',
        'mp3|wav|ogg|flac|aac' => 'audio',
        'php|html|css|js|json|xml|sql|py' => 'code'
    ];
    foreach ($map as $exts => $cat) {
        if (preg_match("/^($exts)$/", $ext)) return $cat;
    }
    return 'other';
}

function getIcon($ext) {
    $icons = [
        'image' => 'bi-image', 'document' => 'bi-file-earmark-text',
        'archive' => 'bi-file-earmark-zip', 'video' => 'bi-camera-video',
        'audio' => 'bi-music-note-beamed', 'code' => 'bi-code-slash',
        'other' => 'bi-file-earmark'
    ];
    return $icons[getCategory($ext)] ?? 'bi-file-earmark';
}

function getColor($ext) {
    $colors = [
        'image' => '#10b981', 'document' => '#3b82f6',
        'archive' => '#f59e0b', 'video' => '#ef4444',
        'audio' => '#8b5cf6', 'code' => '#06b6d4', 'other' => '#6b7280'
    ];
    return $colors[getCategory($ext)] ?? '#6b7280';
}

function getCatLabel($ext) {
    $labels = [
        'image' => 'صورة', 'document' => 'مستند', 'archive' => 'أرشيف',
        'video' => 'فيديو', 'audio' => 'صوت', 'code' => 'كود', 'other' => 'ملف'
    ];
    return $labels[getCategory($ext)] ?? 'ملف';
}

 $totalFiles = count($existingFiles);

// جمع الامتدادات المسموحة للـ accept
 $allExts = [];
foreach ($allowedTypes as $exts) $allExts = array_merge($allExts, $exts);
 $acceptStr = implode(',', array_map(function($e) { return '.' . $e; }, $allExts));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع الملفات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- إشعار -->
    <div id="toastBox"></div>

    <div class="container main-wrap">

        <!-- الهيدر -->
        <header class="text-center py-4">
            <div class="logo-box"><i class="bi bi-cloud-arrow-up-fill"></i></div>
            <h1>مركز رفع الملفات</h1>
            <p class="text-muted">ارفع ملفاتك بسهولة — صور، مستندات، أرشيفات وأكثر</p>
        </header>

        <!-- الإحصائيات -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-files stat-ico" style="color:#10b981"></i>
                    <div class="stat-val"><?= $totalFiles ?></div>
                    <div class="stat-lbl">ملف</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-hdd stat-ico" style="color:#06b6d4"></i>
                    <div class="stat-val"><?= formatSize($totalSize) ?></div>
                    <div class="stat-lbl">مستخدم</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-filetype-php stat-ico" style="color:#f59e0b"></i>
                    <div class="stat-val"><?= count($allExts) ?></div>
                    <div class="stat-lbl">نوع مدعوم</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-shield-check stat-ico" style="color:#8b5cf6"></i>
                    <div class="stat-val">50MB</div>
                    <div class="stat-lbl">حد أقصى</div>
                </div>
            </div>
        </div>

        <!-- منطقة الرفع -->
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="drop-zone" id="dropZone" tabindex="0" role="button" aria-label="اسحب الملفات هنا أو اضغط للاختيار">
                <i class="bi bi-cloud-arrow-up drop-ico"></i>
                <h5>اسحب الملفات وأفلتها هنا</h5>
                <p class="text-muted mb-2">أو اضغط لاختيار الملفات من جهازك</p>
                <span class="btn-browse">اختيار ملفات</span>
                <input type="file" name="files[]" id="fileInput" multiple accept="<?= $acceptStr ?>" hidden>
            </div>

            <!-- الملفات المختارة -->
            <div id="selectedBox" class="mt-3" style="display:none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold"><i class="bi bi-check2-circle text-success"></i> الملفات المختارة (<span id="selCount">0</span>)</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearBtn"><i class="bi bi-x-lg"></i> إلغاء الكل</button>
                </div>
                <div id="selList"></div>
            </div>

            <!-- أزرار الرفع -->
            <div id="actionsBox" class="mt-3 d-flex gap-2" style="display:none">
                <button type="submit" class="btn btn-upload" id="uploadBtn">
                    <i class="bi bi-upload"></i> رفع الآن
                    <span class="spinner-border spinner-border-sm ms-1" id="spinner" style="display:none"></span>
                </button>
            </div>
        </form>

        <!-- شريط التقدم -->
        <div id="progressBox" class="mt-3" style="display:none">
            <div class="progress-card">
                <div class="d-flex justify-content-between mb-1">
                    <small class="fw-bold" id="progLabel">جاري الرفع...</small>
                    <small class="text-success fw-bold" id="progPct">0%</small>
                </div>
                <div class="progress" style="height:8px; border-radius:8px; background:#1e293b">
                    <div class="progress-bar" id="progBar" style="width:0%; border-radius:8px; background:linear-gradient(90deg,#059669,#10b981,#34d399)"></div>
                </div>
            </div>
        </div>

        <!-- الملفات المرفوعة -->
        <?php if ($totalFiles > 0): ?>
        <section class="mt-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="mb-0"><i class="bi bi-folder2-open"></i> الملفات المرفوعة <span class="badge bg-success"><?= $totalFiles ?></span></h5>
                <div class="d-flex flex-wrap gap-1" id="filterBtns">
                    <button class="btn btn-sm btn-filter active" data-filter="all">الكل</button>
                    <?php
                    $cats = [];
                    foreach ($existingFiles as $f) {
                        $c = getCategory($f['ext']);
                        $cats[$c] = ($cats[$c] ?? 0) + 1;
                    }
                    $catLabels = ['image'=>'صور','document'=>'مستندات','archive'=>'أرشيفات','video'=>'فيديو','audio'=>'صوت','code'=>'أكواد','other'=>'أخرى'];
                    foreach ($cats as $cat => $cnt):
                    ?>
                    <button class="btn btn-sm btn-filter" data-filter="<?= $cat ?>"><?= $catLabels[$cat] ?? $cat ?> (<?= $cnt ?>)</button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-3" id="filesGrid">
                <?php foreach ($existingFiles as $file):
                    $color = getColor($file['ext']);
                    $icon  = getIcon($file['ext']);
                    $label = getCatLabel($file['ext']);
                ?>
                <div class="col-sm-6 col-lg-4 file-col" data-cat="<?= getCategory($file['ext']) ?>">
                    <div class="file-card">
                        <div class="d-flex align-items-start gap-3">
                            <div class="f-icon" style="background:<?= $color ?>18; color:<?= $color ?>">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="f-name" title="<?= htmlspecialchars($file['name']) ?>"><?= htmlspecialchars($file['name']) ?></div>
                                <span class="f-badge" style="background:<?= $color ?>18; color:<?= $color ?>"><?= $label ?> · .<?= $file['ext'] ?></span>
                            </div>
                        </div>
                        <div class="f-footer">
                            <span class="f-size"><?= formatSize($file['size']) ?></span>
                            <span class="f-time"><?= date('d/m/Y H:i', $file['time']) ?></span>
                            <div class="f-actions">
                                <a href="<?= $uploadDir . $file['name'] ?>" download class="btn btn-sm f-btn f-btn-dl" title="تحميل"><i class="bi bi-download"></i></a>
                                <a href="?delete=<?= urlencode($file['name']) ?>" class="btn btn-sm f-btn f-btn-del" title="حذف" onclick="return confirm('حذف هذا الملف؟')"><i class="bi bi-trash3"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <footer class="text-center text-muted py-4 mt-5 border-top">
            <small>مركز رفع الملفات — جميع الملفات محفوظة محلياً على الخادم</small>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // تمرير رسالة PHP للجافاسكريبت
    window.phpMessage = <?= json_encode(['text' => $message, 'type' => $messageType]) ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>