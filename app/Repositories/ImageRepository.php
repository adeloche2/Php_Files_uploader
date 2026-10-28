<?php
/** طبقة SQL للصور؛ لا تتعامل مع ملفات نظام التشغيل. */
declare(strict_types=1);
final class ImageRepository {
 public function __construct(private PDO $pdo){}
 public function create(array $i): int {
  $s=$this->pdo->prepare('INSERT INTO images(original_name,stored_name,mime_type,file_extension,file_size,storage_path,image_width,image_height,uploaded_at) VALUES(:original_name,:stored_name,:mime_type,:file_extension,:file_size,:storage_path,:image_width,:image_height,CURRENT_TIMESTAMP)');
  $s->execute([':original_name'=>$i['original_name'],':stored_name'=>$i['stored_name'],':mime_type'=>$i['mime_type'],':file_extension'=>$i['file_extension'],':file_size'=>$i['file_size'],':storage_path'=>$i['storage_path'],':image_width'=>$i['image_width'],':image_height'=>$i['image_height']]);
  return (int)$this->pdo->lastInsertId();
 }
 public function all(int $limit=100): array { $limit=max(1,min(500,$limit)); return $this->pdo->query('SELECT * FROM images ORDER BY id DESC LIMIT '.$limit)->fetchAll(); }
 public function find(int $id): ?array { $s=$this->pdo->prepare('SELECT * FROM images WHERE id=:id LIMIT 1');$s->execute([':id'=>$id]);$r=$s->fetch();return $r===false?null:$r; }
 public function findByStoredName(string $name): ?array { $s=$this->pdo->prepare('SELECT * FROM images WHERE stored_name=:name LIMIT 1');$s->execute([':name'=>$name]);$r=$s->fetch();return $r===false?null:$r; }
 public function delete(int $id): bool { $s=$this->pdo->prepare('DELETE FROM images WHERE id=:id');$s->execute([':id'=>$id]);return $s->rowCount()===1; }
 public function stats(): array { $r=$this->pdo->query('SELECT COUNT(*) image_count,COALESCE(SUM(file_size),0) total_bytes FROM images')->fetch();return ['image_count'=>(int)$r['image_count'],'total_bytes'=>(int)$r['total_bytes']]; }
}