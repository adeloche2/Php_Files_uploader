# مركز رفع الملفات

نظام رفع ملفات بسيط وآمن مبني بلغة PHP مع واجهة مستخدم عصرية ومتجاوبة مع جميع أحجام الشاشات.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

---

## ✨ المميزات

- رفع عدة ملفات دفعة واحدة
- دعم السحب والإفلات (Drag & Drop)
- رفع الملفات دون إعادة تحميل الصفحة (AJAX) مع شريط تقدم
- التحقق من حجم الملفات ونوعها قبل الرفع
- تصفية الملفات حسب النوع
- تحميل الملفات أو حذفها بسهولة
- عرض إحصائيات لحظية لعدد الملفات والمساحة المستخدمة
- تصميم متجاوب يعمل على الهاتف واللوحي والحاسوب
- واجهة عربية كاملة مع دعم اتجاه RTL
- إنشاء أسماء فريدة تلقائياً لتجنب تعارض أسماء الملفات

---
https://github.com/adeloche2/Php_Files_uploader/blob/main/Screenshot_2026-07-03-22-53-54-845_com.android.chrome-edit.jpg

## 📁 هيكل المشروع

```text
Php_Files_uploader/
├── index.php          # الملف الرئيسي (منطق PHP + هيكل الصفحة)
├── style.css          # ملف التنسيقات
├── script.js          # ملف JavaScript
├── uploads/           # مجلد حفظ الملفات (يُنشأ تلقائياً)
└── README.md          # هذا الملف
```

---

## 🛠️ المتطلبات

- PHP 7.4 أو أحدث
- خادم محلي مثل XAMPP أو WAMP أو Laragon
- أو أي استضافة تدعم PHP

---

## 🚀 التثبيت

### 1. استنساخ المشروع

```bash
git clone https://github.com/adeloche2/Php_Files_uploader.git
cd Php_Files_uploader
```

### 2. التشغيل على خادم محلي (XAMPP مثالاً)

انسخ المشروع إلى مجلد `htdocs`:

```bash
cp -r Php_Files_uploader /xampp/htdocs/
```

ثم افتح الرابط:

```text
http://localhost/Php_Files_uploader
```

### 3. التشغيل على استضافة

ارفع جميع ملفات المشروع إلى موقعك، وتأكد من وجود مجلد `uploads` وأنه قابل للكتابة بواسطة خادم الويب.

---

## ⚙️ الإعدادات

يمكنك تعديل الإعدادات الموجودة في أعلى ملف `index.php`:

```php
// مجلد حفظ الملفات
$uploadDir = 'uploads/';

// الحد الأقصى لحجم الملف (50 ميجابايت)
$maxFileSize = 50 * 1024 * 1024;

// أنواع الملفات المسموح بها
$allowedTypes = [
    'image'    => ['jpg','jpeg','png','gif','webp','svg','bmp','ico'],
    'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','csv'],
    'archive'  => ['zip','rar','7z','tar','gz'],
    'video'    => ['mp4','avi','mov','mkv','webm'],
    'audio'    => ['mp3','wav','ogg','flac','aac'],
    'code'     => ['php','html','css','js','json','xml','sql','py']
];
```

> **ملاحظة:** إذا كنت ترغب في السماح برفع ملفات أكبر، فتأكد من تعديل إعدادات `php.ini` أيضاً.

```ini
upload_max_filesize = 50M
post_max_size = 60M
```

---

## 📂 أنواع الملفات المدعومة

| الفئة | الامتدادات |
|-------|------------|
| الصور | jpg, jpeg, png, gif, webp, svg, bmp, ico |
| المستندات | pdf, doc, docx, xls, xlsx, ppt, pptx, txt, rtf, csv |
| الأرشيفات | zip, rar, 7z, tar, gz |
| الفيديو | mp4, avi, mov, mkv, webm |
| الصوت | mp3, wav, ogg, flac, aac |
| ملفات الأكواد | php, html, css, js, json, xml, sql, py |

يمكنك إضافة أو حذف أي امتداد من مصفوفة `$allowedTypes` حسب احتياجاتك.

---

## 🔧 التقنيات المستخدمة

| التقنية | الاستخدام |
|---------|-----------|
| PHP | معالجة رفع الملفات وإدارتها |
| Bootstrap 5 | تصميم الواجهة |
| Bootstrap Icons | الأيقونات |
| Cairo Font | الخط العربي |
| Vanilla JavaScript | السحب والإفلات وAJAX |
| CSS3 | التنسيقات والتأثيرات |

---

## 📱 التوافق

- ✅ Google Chrome 80+
- ✅ Mozilla Firefox 78+
- ✅ Microsoft Edge 80+
- ✅ Safari 14+
- ✅ متصفحات الهواتف الحديثة

---

## ⚠️ ملاحظات أمنية

هذا المشروع مخصص للاستخدامات التعليمية والمشاريع البسيطة. في بيئة الإنتاج يُنصح بما يلي:

- إضافة نظام تسجيل دخول وصلاحيات للمستخدمين.
- عدم الاعتماد على امتداد الملف فقط، واستخدام `finfo()` للتحقق من نوع الملف الحقيقي.
- منح مجلد `uploads` أقل الصلاحيات اللازمة مع ضمان إمكانية الكتابة بواسطة خادم الويب.
- منع تنفيذ ملفات PHP داخل مجلد `uploads` بإضافة ملف `.htaccess`:

```apache
<FilesMatch "\.(php|phtml|php5|php7|phar)$">
    Require all denied
</FilesMatch>

Options -ExecCGI
```

> **تنبيه:** السماح برفع ملفات PHP مناسب لأغراض التطوير فقط، ولا يُنصح به في بيئات الإنتاج.

---

## 📄 الرخصة

هذا المشروع مرخص بموجب رخصة MIT.

---

## 🤝 المساهمة

المساهمات مرحب بها.

1. Fork للمشروع.
2. إنشاء فرع جديد.
3. تنفيذ التعديلات.
4. رفع الفرع.
5. إنشاء Pull Request.

---

## 👨‍💻 المطور

**Adeloche**

إذا أعجبك المشروع، فلا تنسَ منحه ⭐ على GitHub.
