<?php
/** تهيئة مشتركة للإعدادات والجلسة واتصال قاعدة البيانات والخدمات. */
declare(strict_types=1);
$cp=dirname(__DIR__).'/Config/config.php';
if(!is_file($cp)){http_response_code(500);exit('انسخ config.example.php إلى config.php وأدخل بيانات قاعدة البيانات.');}
$config=require $cp;date_default_timezone_set($config['app']['timezone']??'UTC');
if(session_status()!==PHP_SESSION_ACTIVE){$https=isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off';session_set_cookie_params(['httponly'=>true,'secure'=>$https,'samesite'=>'Lax','path'=>'/']);session_start();}
require_once dirname(__DIR__).'/Helpers/security.php';
require_once dirname(__DIR__).'/Database/connection.php';
require_once dirname(__DIR__).'/Repositories/ImageRepository.php';
require_once dirname(__DIR__).'/Services/ImageService.php';
try{$pdo=createDatabaseConnection($config['database']);$imageRepository=new ImageRepository($pdo);$imageService=new ImageService($config['upload'],$config['paths']);}
catch(Throwable $ex){error_log('Application bootstrap failed: '.$ex->getMessage());http_response_code(500);exit('تعذر تشغيل التطبيق. راجع إعدادات قاعدة البيانات وسجل الخادم.');}