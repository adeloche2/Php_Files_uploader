<?php
/** تنزيل الصورة بالمعرف دون كشف مسار التخزين. */
declare(strict_types=1);require dirname(__DIR__).'/app/Helpers/bootstrap.php';
$id=positiveImageId($_GET['id']??null);$im=$id?$imageRepository->find($id):null;
if(!$im){http_response_code(404);exit('الصورة غير موجودة.');}
$base=realpath($config['paths']['uploads']);$path=$base!==false?realpath($base.DIRECTORY_SEPARATOR.basename((string)$im['storage_path'])):false;
if($base===false||$path===false||!str_starts_with($path,$base.DIRECTORY_SEPARATOR)||!is_file($path)){http_response_code(404);exit('الملف غير موجود.');}
$name=(string)$im['original_name'];$ascii=preg_replace('/[^A-Za-z0-9._-]/','_',$name)?:'image.'.$im['file_extension'];
header('X-Content-Type-Options: nosniff');header('Content-Type: application/octet-stream');header('Content-Length: '.filesize($path));header('Content-Disposition: attachment; filename="'.$ascii.'"; filename*=UTF-8\'\''.rawurlencode($name));readfile($path);