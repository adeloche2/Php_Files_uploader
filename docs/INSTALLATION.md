# التثبيت المحلي

1. استخدم PHP 8.1+ مع pdo_mysql وfileinfo وComposer.
2. شغّل `composer install` لتثبيت AWS SDK for PHP.
3. أنشئ قاعدة بيانات ومستخدمًا بصلاحيات محدودة، ثم نفّذ `database/schema.sql`.
4. انسخ `app/Config/config.example.php` إلى `app/Config/config.php` واضبط بيانات قاعدة البيانات والتخزين.
5. أنشئ bucket خاصًا في خدمة S3-compatible واضبط endpoint ومفاتيح الوصول.
6. أنشئ APP_KEY عشوائيًا سريًا لا يقل عن 32 حرفًا.
7. لتجربة واجهة PHP محليًا، استخدم خادم PHP مع توجيه مناسب؛ ملفات CSS وJavaScript موجودة في `public/assets`.

## النشر على Vercel
راجع `docs/VERCEL.md`. لا تحتاج إلى نسخ `config.php` إلى Vercel؛ استخدم Environment Variables. نفذ `database/schema.sql` في قاعدة MySQL المُدارة، واضبط CORS في مخزن الصور.
