<?php
/** إنشاء اتصال PDO موحد مع أخطاء استثناءات واستعلامات محضرة. */
declare(strict_types=1);
function createDatabaseConnection(array $c): PDO {
 $dsn=sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',$c['host'],(int)$c['port'],$c['name'],$c['charset']??'utf8mb4');
 return new PDO($dsn,(string)$c['username'],(string)$c['password'],[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false
 ]);
}