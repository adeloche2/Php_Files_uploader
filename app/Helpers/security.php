<?php
/** أدوات أمان وتنسيق مشتركة. */
declare(strict_types=1);
function e(?string $v): string { return htmlspecialchars($v??'',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function csrfToken(): string {
 if(!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
 return (string)$_SESSION['csrf_token'];
}
function verifyCsrfToken(mixed $v): bool {
 return is_string($v)&&isset($_SESSION['csrf_token'])&&is_string($_SESSION['csrf_token'])&&hash_equals($_SESSION['csrf_token'],$v);
}
function positiveImageId(mixed $v): ?int { $id=filter_var($v,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]); return $id===false?null:$id; }
function formatBytes(int $n): string {
 if($n>=1048576)return number_format($n/1048576,2).' MiB';
 if($n>=1024)return number_format($n/1024,1).' KiB';
 return $n.' B';
}