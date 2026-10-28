# هندسة المشروع

- public/: صفحات الويب والأصول.
- app/Config/: إعدادات البيئة؛ config.php محلي وغير متعقب.
- app/Database/: إنشاء اتصال PDO.
- app/Helpers/: التهيئة والأمان وتنسيق الأحجام.
- app/Services/ImageService.php: التحقق من الملف ونقله إلى التخزين.
- app/Repositories/ImageRepository.php: استعلامات MySQL.
- storage/: الصور والسجلات خارج Document Root.
- database/schema.sql: جدول الصور.
- docs/: توثيق التثبيت والتهيئة والأمان.

مسار الرفع: النموذج يرسل الصور؛ PHP يتحقق من CSRF وعدد الملفات؛ ImageService يتحقق من الخطأ والحجم وMIME ومحتوى الصورة ثم يحفظها باسم عشوائي؛ ImageRepository يسجل البيانات الوصفية. إذا فشل التسجيل تحاول الصفحة حذف الملف لتقليل الملفات اليتيمة.