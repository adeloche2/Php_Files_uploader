# Image Uploader — PHP + MySQL + Vercel

موقع عربي بواجهة RTL لرفع الصور حتى 30 MiB للصورة الواحدة، مع معرض وعرض وتنزيل الصور.

## تشغيله على Vercel
- PHP عبر Community Runtime `vercel-php@0.9.0` المحدد في `vercel.json`.
- لا يحتاج إلى Vite أو `vite build`.
- يرفع المتصفح الصور مباشرة إلى مخزن S3-compatible باستخدام رابط PUT مؤقت؛ لا تمر الصور عبر PHP Function.
- تحفظ MySQL بيانات الصور الوصفية ومفتاح الكائن، بينما تحفظ خدمة التخزين ملفات الصور بصورة دائمة.
- تعتمد الحزمة على AWS SDK for PHP لتوقيع روابط التخزين.

## المتطلبات الخارجية
1. قاعدة MySQL مُدارة يمكن الوصول إليها من Vercel.
2. مخزن S3-compatible مثل Cloudflare R2 أو AWS S3.
3. متغيرات البيئة الموجودة في `.env.example` داخل إعدادات Vercel.
4. تنفيذ `database/schema.sql` على قاعدة البيانات.
5. إعداد CORS في مخزن الصور للسماح بأصل الموقع وطريقة PUT وترويسة Content-Type.

## متغيرات البيئة
`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_CHARSET`, `S3_ENDPOINT`, `S3_REGION`, `S3_BUCKET`, `S3_ACCESS_KEY_ID`, `S3_SECRET_ACCESS_KEY`, `APP_KEY`, `APP_TIMEZONE`.

استخدم `APP_KEY` عشوائيًا سريًا لا يقل عن 32 حرفًا. لا ترفع أسرارًا إلى Git.

## الملفات المهمة
- `vercel.json`: PHP Runtime والتوجيه.
- `api/`: وظائف Vercel.
- `app/Pages/`: صفحات PHP الأساسية.
- `app/Services/ObjectStorageService.php`: روابط التخزين الموقعة وعمليات التخزين.
- `app/Repositories/ImageRepository.php`: عمليات MySQL.
- `public/assets/`: ملفات CSS وJavaScript الثابتة.
- `database/schema.sql`: مخطط قاعدة البيانات.
- `docs/VERCEL.md`: خطوات النشر والتخزين.

## تنبيهات مهمة
- لم يتم تنفيذ نشر فعلي أو اختبار اتصال بقاعدة البيانات والتخزين من هذا التعديل.
- Community PHP Runtime ليس runtime رسميًا مدمجًا من Vercel؛ راجع مستودع runtime عند تحديثه.
- لا يتضمن المشروع حسابات أو ملكية للصور. الحذف معطّل افتراضيًا؛ لا تفعّله عبر ENABLE_PUBLIC_DELETE إلا بعد إضافة مصادقة إدارية وتفويض. أضف أيضًا محددات معدل قبل استقبال رفع عام.
- فحص الإكمال يتحقق من الحجم ونوع المحتوى المعلن من التخزين، لكنه لا يعيد تحليل بايتات الصورة. أضف فحصًا وإعادة ترميز قبل استقبال ملفات عامة غير موثوقة.
- اختبر الرفع والعرض والتنزيل والحذف بعد ضبط متغيرات البيئة وCORS.
