<?php
/** يتحقق من صور الرفع باستخدام MIME الفعلي وأبعاد الصورة، ثم يخزن باسم عشوائي. */
declare(strict_types=1);
final class ImageService {
 public function __construct(private array $settings,private array $paths){}
 public function validateAndStore(array $f): array {
  $dir=$this->paths['uploads'];if(!is_dir($dir)&&!mkdir($dir,0750,true)&&!is_dir($dir))throw new RuntimeException('تعذر إنشاء مجلد التخزين.');
  if(!is_writable($dir))throw new RuntimeException('مجلد التخزين غير قابل للكتابة.');
  $err=(int)($f['error']??UPLOAD_ERR_NO_FILE);
  if($err!==UPLOAD_ERR_OK)throw new RuntimeException(match($err){UPLOAD_ERR_INI_SIZE,UPLOAD_ERR_FORM_SIZE=>'تجاوز الملف حد الحجم في PHP.',UPLOAD_ERR_PARTIAL=>'وصل جزء من الملف فقط.',UPLOAD_ERR_NO_FILE=>'لم يتم اختيار ملف.',UPLOAD_ERR_NO_TMP_DIR=>'مجلد الرفع المؤقت مفقود.',UPLOAD_ERR_CANT_WRITE=>'تعذرت كتابة الملف المؤقت.',default=>'فشل الرفع.'});
  $tmp=(string)($f['tmp_name']??'');$size=(int)($f['size']??0);
  if($size<1||$size>(int)$this->settings['max_file_size'])throw new RuntimeException('الحجم يجب ألا يتجاوز 30 MiB.');
  if($tmp===''||!is_uploaded_file($tmp))throw new RuntimeException('الملف ليس رفعًا صالحًا عبر PHP.');
  $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);$types=$this->settings['allowed_mime_types'];
  if(!is_string($mime)||!isset($types[$mime]))throw new RuntimeException('نوع الصورة غير مدعوم.');
  $info=@getimagesize($tmp);if(!is_array($info)||empty($info[0])||empty($info[1]))throw new RuntimeException('الملف ليس صورة صالحة.');
  $name=bin2hex(random_bytes(20)).'.'.$types[$mime];$dest=rtrim($dir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
  if(!move_uploaded_file($tmp,$dest))throw new RuntimeException('تعذر حفظ الصورة.');
  @chmod($dest,0640);
  $original=basename(str_replace('\\','/',(string)($f['name']??'image')));
  $original=trim(preg_replace('/[[:cntrl:]]/u','',$original)??'');
  if($original==='')$original='image.'.$types[$mime];
  $original=function_exists('mb_substr')?mb_substr($original,0,250,'UTF-8'):substr($original,0,250);
  return ['original_name'=>$original,'stored_name'=>$name,'mime_type'=>$mime,'file_extension'=>$types[$mime],'file_size'=>(int)filesize($dest),'storage_path'=>$name,'image_width'=>(int)$info[0],'image_height'=>(int)$info[1],'_absolute_path'=>$dest];
 }
}