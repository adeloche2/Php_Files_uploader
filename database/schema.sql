-- مخطط قاعدة بيانات الصور. أنشئ قاعدة البيانات ثم نفذ هذا الملف.
CREATE TABLE IF NOT EXISTS images (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 original_name VARCHAR(250) NOT NULL,
 stored_name VARCHAR(80) NOT NULL,
 mime_type VARCHAR(100) NOT NULL,
 file_extension VARCHAR(12) NOT NULL,
 file_size BIGINT UNSIGNED NOT NULL,
 storage_path VARCHAR(255) NOT NULL,
 image_width INT UNSIGNED NULL,
 image_height INT UNSIGNED NULL,
 uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_images_stored_name(stored_name),
 KEY idx_images_uploaded_at(uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;