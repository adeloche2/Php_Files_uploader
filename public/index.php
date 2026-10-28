<?php
/** الصفحة الرئيسية لرفع الصور وعرض أحدثها. الحذف غير مناسب للنشر العام دون مصادقة. */
declare(strict_types=1);
require dirname(__DIR__).'/app/Helpers/bootstrap.php';
$message='';$type='info';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
 if(!verifyCsrfToken($_POST['csrf_token']??null)){http_response_code(403);$message='تعذر التحقق من الطلب.';$type='danger';}
 else{$id=positiveImageId($_POST['image_id']??null);$image=$id?$imageRepository->find($id):null;
  if(!$image){$message='الصورة غير موجودة.';$type='warning';}
  else{$base=realpath($config['paths']['uploads']);$path=$base!==false?$base.DIRECTORY_SEPARATOR.basename((string)$image['storage_path']):'';
   if($path===''||!is_file($path)){$imageRepository->delete($id);$message='الملف غير موجود؛ تم تنظيف السجل.';$type='warning';}
   elseif(!unlink($path)){$message='تعذر حذف الملف.';$type='danger';}
   elseif(!$imageRepository->delete($id)){error_log('Image row delete failed: '.$id);$message='حذف الملف لكن تعذر حذف سجل قاعدة البيانات.';$type='danger';}
   else{$message='تم حذف الصورة.';$type='success';}
  }
 }
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='upload'){
 if(!verifyCsrfToken($_POST['csrf_token']??null)){http_response_code(403);$message='تعذر التحقق من الطلب.';$type='danger';}
 elseif(!isset($_FILES['images'])){$message='لم يستلم الخادم ملفات. قد يكون الطلب أكبر من post_max_size.';$type='danger';}
 else{$f=$_FILES['images'];$count=count(is_array($f['name']??null)?$f['name']:[]);$max=(int)$config['upload']['max_files_per_request'];
  if($count<1||$count>$max){$message='اختر صورًا، مع الالتزام بالحد الأقصى '.$max.' صور للطلب.';$type='warning';}
  else{$ok=0;$errors=[];
   for($i=0;$i<$count;$i++){
    $one=['name'=>$f['name'][$i]??'','tmp_name'=>$f['tmp_name'][$i]??'','error'=>$f['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$f['size'][$i]??0];
    try{$data=$imageService->validateAndStore($one);$path=$data['_absolute_path'];unset($data['_absolute_path']);
     try{$imageRepository->create($data);$ok++;}
     catch(Throwable $dbError){if(is_file($path))@unlink($path);throw $dbError;}
    }catch(Throwable $ex){$errors[]=((string)$one['name']).': '.$ex->getMessage();error_log('Upload failed: '.$ex->getMessage());}
   }
   if($ok){$message='تم رفع '.$ok.' صورة بنجاح.';$type=$errors?'warning':'success';}
   if($errors){$message.=($message?' ':'').'تعذر رفع بعض الصور: '.implode(' — ',$errors);if(!$ok)$type='danger';}
  }
 }
}
$stats=$imageRepository->stats();$images=$imageRepository->all(100);$max=(int)$config['upload']['max_files_per_request'];
?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>مركز الصور</title><link rel="stylesheet" href="assets/css/style.css"><script defer src="assets/js/uploader.js"></script></head><body><main class="container">
<header class="site-header"><div class="brand-mark">IMG</div><div><p class="eyebrow">IMAGE WORKSPACE</p><h1>مركز الصور</h1><p class="muted">ارفع صورك ونظّمها في مكان واحد.</p></div></header>
<section class="stats"><article class="stat-card"><span>عدد الصور</span><strong><?= (int)$stats['image_count'] ?></strong></article><article class="stat-card"><span>المساحة المستخدمة</span><strong><?= e(formatBytes((int)$stats['total_bytes'])) ?></strong></article><article class="stat-card"><span>حد الصورة</span><strong>30 MiB</strong></article></section>
<?php if($message!==''):?><div class="notice notice-<?=e($type)?>" role="status"><?=e($message)?></div><?php endif;?>
<section class="panel"><div class="section-heading"><div><p class="eyebrow">UPLOAD</p><h2>رفع صور جديدة</h2></div><span class="soft-badge">حتى <?=$max?> صور</span></div>
<form id="upload-form" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload"><input type="hidden" name="csrf_token" value="<?=e(csrfToken())?>">
<label id="drop-zone" class="drop-zone" for="images"><span class="upload-symbol">↑</span><strong>اسحب الصور إلى هنا</strong><span class="muted">أو اضغط لاختيار الملفات</span><span class="browse-button">اختيار الصور</span><span class="file-rules">JPEG · PNG · WebP · GIF · BMP — حتى 30 MiB للصورة</span><input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp" multiple required></label>
<div id="selected-files" class="selected-files" aria-live="polite"></div><div class="upload-footer"><p class="muted small">تعتمد إمكانية الرفع أيضًا على إعدادات الخادم والاستضافة.</p><button class="primary-button" type="submit">رفع الصور</button></div>
<div id="upload-progress" class="progress-wrap" hidden><div class="progress-label"><span>جاري الإرسال...</span><span id="progress-percent">0%</span></div><progress id="progress-bar" value="0" max="100"></progress></div></form></section>
<section class="gallery-section"><div class="section-heading"><div><p class="eyebrow">LIBRARY</p><h2>معرض الصور</h2></div><span class="muted"><?=count($images)?> أحدث صورة</span></div>
<?php if(!$images):?><div class="empty-state"><span>▧</span><h3>لا توجد صور بعد</h3><p>ارفع أول صورة لتظهر هنا.</p></div><?php else:?><div class="gallery-grid"><?php foreach($images as $im):?><article class="image-card"><a class="image-preview" href="image.php?id=<?=(int)$im['id']?>" target="_blank" rel="noopener"><img src="image.php?id=<?=(int)$im['id']?>" alt="<?=e($im['original_name'])?>" loading="lazy"></a><div class="image-info"><h3 title="<?=e($im['original_name'])?>"><?=e($im['original_name'])?></h3><p><?=e(formatBytes((int)$im['file_size']))?> · <?=(int)$im['image_width']?>×<?=(int)$im['image_height']?></p><p class="muted small"><?=e((string)$im['uploaded_at'])?></p><div class="card-actions"><a class="secondary-button" href="download.php?id=<?=(int)$im['id']?>">تنزيل</a><form method="post" class="delete-form" onsubmit="return confirm('هل تريد حذف الصورة نهائيًا؟')"><input type="hidden" name="action" value="delete"><input type="hidden" name="image_id" value="<?=(int)$im['id']?>"><input type="hidden" name="csrf_token" value="<?=e(csrfToken())?>"><button class="danger-button" type="submit">حذف</button></form></div></div></article><?php endforeach;?></div><?php endif;?></section>
<footer class="site-footer">مركز الصور · PHP + MySQL · RTL</footer></main></body></html>