<?php
/** عرض صورة مخزنة خارج جذر الويب باستخدام معرف قاعدة البيانات فقط. */
declare(strict_types=1);require dirname(__DIR__).'/app/Helpers/bootstrap.php';
$id=positiveImageId($_GET['id']??null);$im=$id?$imageRepository->find($id):null;
if(!$im){http_response_code(404);exit('الصورة غير موجودة.');}
$base=realpath($config['paths']['uploads']);$path=$base!==false?realpath($base.DIRECTORY_SEPARATOR.basename((string)$im['storage_path'])):false;
if($base===false||$path===false||!str_starts_with($path,$base.DIRECTORY_SEPARATOR)||!is_file($path)){http_response_code(404);exit('الصورة غير موجودة.');}
header('X-Content-Type-Options: nosniff');header('Cache-Control: private, max-age=300');header('Content-Type: '.$im['mime_type']);header('Content-Length: '.filesize($path));readfile($path);