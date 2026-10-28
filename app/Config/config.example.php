<?php
/** نموذج إعدادات محلي؛ انسخه إلى config.php ولا ترفع الأسرار إلى Git. */
declare(strict_types=1);
return [
 'database'=>['host'=>'127.0.0.1','port'=>3306,'name'=>'image_uploader','username'=>'image_uploader_user','password'=>'CHANGE_THIS_PASSWORD','charset'=>'utf8mb4'],
 'upload'=>['max_file_size'=>30*1024*1024,'max_files_per_request'=>10,'allowed_mime_types'=>['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/bmp'=>'bmp']],
 'paths'=>['uploads'=>dirname(__DIR__,2).'/storage/uploads','logs'=>dirname(__DIR__,2).'/storage/logs'],
 'app'=>['debug'=>false,'timezone'=>'Africa/Algiers'],
];