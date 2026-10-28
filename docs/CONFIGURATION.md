# إعدادات الرفع

الحد الأقصى للصورة الواحدة هو 30 MiB = 31,457,280 بايت. الأنواع المسموحة افتراضيًا: JPEG وPNG وWebP وGIF وBMP.

## النشر على Vercel
الملف لا يُرسل إلى PHP Function؛ يحصل المتصفح على رابط PUT موقّع ويرفع مباشرة إلى مخزن S3-compatible. لذلك لا تحتاج وظيفة PHP إلى استقبال ملف بحجم 30 MiB. راجع `docs/VERCEL.md` لضبط متغيرات البيئة وCORS.

## الاستضافة التقليدية
إذا استخدمت نسخة PHP تقليدية مع الرفع المحلي، فقد تحتاج إلى إعدادات مثل:
```ini
file_uploads = On
upload_max_filesize = 30M
post_max_size = 100M
max_file_uploads = 10
max_input_time = 180
max_execution_time = 180
memory_limit = 256M
```
إعدادات PHP وحدود الخادم قد تختلف بين CLI وPHP-FPM وApache، وقد تمنع الاستضافة استقبال الملفات الكبيرة.
