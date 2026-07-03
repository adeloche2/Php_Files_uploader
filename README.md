```markdown
# مركز رفع الملفات

نظام رفع ملفات بسيط وآمن مبني بلغة PHP مع واجهة مستخدم عصرية متجاوبة مع جميع أحجام الشاشات.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/MIT-License-green?style=flat-square)

---

## ✨ المميزات

- **رفع متعدد الملفات** — اختر عدة ملفات دفعة واحدة
- **السحب والإفلات (Drag & Drop)** — اسحب الملفات مباشرة إلى منطقة الرفع
- **رفع بدون إعادة تحميل (AJAX)** — مع شريط تقدم متحرك
- **التحقق من الملفات** — فحص الحجم والنوع قبل الرفع
- **فلترة حسب النوع** — تصفية الملفات المرفوعة (صور، مستندات، أرشيفات...)
- **تحميل وحذف** — إدارة الملفات المرفوعة بسهولة
- **إحصائيات لحظية** — عدد الملفات والمساحة المستخدمة
- **تصميم متجاوب** — يعمل على الهاتف والتابلت والحاسوب
- **واجهة عربية كاملة** — دعم كامل لاتجاه RTL
- **أسماء فريدة تلقائياً** — تجنب تعارض أسماء الملفات

---

## 📁 هيكل الملفات

```
file-uploader/
├── index.php          # الملف الرئيسي (منطق PHP + هيكل HTML)
├── style.css          # ملف التنسيقات
├── script.js          # ملف الجافاسكريبت
├── uploads/           # مجلد حفظ الملفات (يُنشأ تلقائياً)
└── README.md          # هذا الملف
```

---

## 🛠️ المتطلبات

- **PHP** 7.4 أو أحدث
- خادم محلي مثل **XAMPP** أو **WAMP** أو **Laragon**
- أو أي استضافة تدعم PHP

---

## 🚀 التثبيت

### 1. استنساخ المشروع

```bash
git clone https://github.com/adeloche2/Php_Files_uploader.git
cd file-uploader
```

### 2. على خادم محلي (XAMPP مثلاً)

```bash
# انسخ المجلد إلى مسار الخادم
cp -r file-uploader /xampp/htdocs/upload
```

ثم افتح في المتصفح:

```
http://localhost/upload
```

### 3. على استضافة

ارفع الملفات الثلاثة (`index.php`, `style.css`, `script.js`) إلى المجلد الرئيسي لموقعك عبر FTP أو لوحة التحكم.

---

## ⚙️ الإعدادات

افتح `index.php` وعدّل المتغيرات في أعلى الملف حسب حاجتك:

```php
// مجلد حفظ الملفات
$uploadDir = 'uploads/';

// الحد الأقصى لحجم الملف بالبايت (50 ميجابايت)
$maxFileSize = 50 * 1024 * 1024;

// أنواع الملفات المسموحة
$allowedTypes = [
    'image'    => ['jpg','jpeg','png','gif','webp','svg','bmp','ico'],
    'document' => ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','csv'],
    'archive'  => ['zip','rar','7z','tar','gz'],
    'video'    => ['mp4','avi','mov','mkv','webm'],
    'audio'    => ['mp3','wav','ogg','flac','aac'],
    'code'     => ['php','html','css','js','json','xml','sql','py']
];
```

> **ملاحظة:** تأكد أيضاً من إعداد `upload_max_filesize` و `post_max_size` في ملف `php.ini` ليتوافق مع الحد الأقصى المطلوب.

```ini
upload_max_filesize = 50M
post_max_size = 60M
```

---

## 🎯 أنواع الملفات المدعومة

| الفئة | الامتدادات |
|-------|-----------|
| صور | jpg, jpeg, png, gif, webp, svg, bmp, ico |
| مستندات | pdf, doc, docx, xls, xlsx, ppt, pptx, txt, rtf, csv |
| أرشيفات | zip, rar, 7z, tar, gz |
| فيديو | mp4, avi, mov, mkv, webm |
| صوت | mp3, wav, ogg, flac, aac |
| أكواد | php, html, css, js, json, xml, sql, py |

يمكنك إضافة أو حذف امتدادات من مصفوفة `$allowedTypes` في `index.php`.

---

## 🔧 التقنيات المستخدمة

| التقنية | الغرض |
|---------|-------|
| **PHP** | معالجة رفع وحذف الملفات |
| **Bootstrap 5** | أساس الشبكة والمكونات (RTL) |
| **Bootstrap Icons** | الأيقونات |
| **Cairo Font** | خط عربي من Google Fonts |
| **Vanilla JavaScript** | السحب والإفلات، AJAX، التفاعلات |
| **CSS3** | التنسيقات والتأثيرات والاستجابة |

---

## 📱 التوافق

- ✅ Chrome 80+
- ✅ Firefox 78+
- ✅ Safari 14+
- ✅ Edge 80+
- ✅ متصفحات الهواتف الحديثة

---

## ⚠️ ملاحظات الأمان

> هذا المشروع مصمم لأغراض تعليمية وبسيطة. للاستخدام في الإنتاج:

- أضف **مصادقة تسجيل دخول** لمنع الوصول العام
- لا تعتمد على امتداد الملف وحده — استخدم `finfo()` للتحقق من النوع الفعلي
- غيّر صلاحيات مجلد `uploads` إلى `755` بدلاً من `777`
- أضف `.htaccess` في مجلد `uploads` لمنع تنفيذ ملفات PHP:

```apache
# uploads/.htaccess
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

---

## 📄 الرخصة

هذا المشروع مرخص تحت رخصة [MIT](LICENSE).

---

## 🤝 المساهمة

التبليغات والمساهمات بالكود مرحب بها! اتبع الخطوات التالية:

1. Fork المشروع
2. أنشئ فرع جديد (`git checkout -b feature/اسم-الميزة`)
3. Commit التغييرات (`git commit -m 'إضافة ميزة كذا'`)
4. ارفع الفرع (`git push origin feature/اسم-الميزة`)
5. أنشئ Pull Request

---

<p align="center">
  صنع بـ ❤️ باستخدام PHP
</p>
```
