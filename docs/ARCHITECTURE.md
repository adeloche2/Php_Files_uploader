# هندسة المشروع

- `api/`: وظائف Vercel التي تستخدم Community PHP Runtime.
- `api/index.php`: الصفحة الرئيسية ومسار fallback.
- `api/upload-url.php`: ينشئ رابط رفع مؤقتًا موقّعًا.
- `api/upload-complete.php`: يتحقق من وجود الكائن وحجمه ونوع المحتوى، ثم يسجل metadata في MySQL.
- `api/image.php` و`api/download.php`: واجهات لإنشاء روابط GET موقعة قصيرة الأجل.
- `app/Pages/`: منطق صفحات الواجهة.
- `app/Helpers/bootstrap.php`: تحميل إعدادات Environment Variables وإنشاء PDO.
- `app/Repositories/ImageRepository.php`: استعلامات MySQL.
- `app/Services/ObjectStorageService.php`: عمليات S3-compatible وتوقيع الروابط.
- `app/Services/ImageService.php`: خدمة رفع محلي للبيئات التقليدية، ولا تمر ملفات Vercel عبرها.
- `public/assets/`: CSS وJavaScript الثابتة.
- `database/schema.sql`: مخطط قاعدة البيانات.
- `docs/`: توثيق التثبيت والتهيئة والأمان.

## مسار الرفع على Vercel
1. المتصفح يرسل اسم الصورة وحجمها وMIME إلى `/api/upload-url`.
2. PHP يتحقق من البيانات ويصدر رابط PUT موقّعًا ومفتاحًا عشوائيًا مع توقيع إكمال HMAC.
3. المتصفح يرفع الصورة مباشرة إلى bucket خاص؛ لا يمر جسم الملف عبر Vercel Function.
4. المتصفح يرسل بيانات الإكمال إلى `/api/upload-complete`.
5. PHP يتحقق من توقيع الإكمال وبيانات HEAD من التخزين، ثم يحفظ metadata ومفتاح الكائن في MySQL.
6. العرض والتنزيل يستخدمان روابط GET موقعة مؤقتة.

## بيانات دائمة
ملفات الصور في object storage، وبيانات الفهرسة في MySQL. لا تعتمد على قرص Vercel المحلي لحفظ الملفات أو جلسات PHP.