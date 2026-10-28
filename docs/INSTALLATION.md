# التثبيت

1. استخدم PHP 8.1+ مع pdo_mysql وfileinfo، وMySQL/MariaDB.
2. أنشئ قاعدة بيانات ومستخدمًا بصلاحيات محدودة، ثم نفّذ database/schema.sql.
3. انسخ app/Config/config.example.php إلى app/Config/config.php وعدّل بيانات الاتصال.
4. اضبط Document Root ليشير إلى public/ فقط، مع إبقاء app/ وstorage/ خارجه.
5. امنح PHP صلاحية الكتابة إلى storage/uploads/ وstorage/logs/ دون أذونات عامة.
6. راجع docs/CONFIGURATION.md، ثم اختبر الصور الصحيحة والملفات غير الصورية والصور الأكبر من الحد قبل النشر.