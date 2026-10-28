# إعدادات الرفع حتى 30 MiB

الحد في التطبيق هو 30 × 1024 × 1024 = 31,457,280 بايت.

إعدادات PHP المقترحة كبداية:
```ini
file_uploads = On
upload_max_filesize = 30M
post_max_size = 100M
max_file_uploads = 10
max_input_time = 180
max_execution_time = 180
memory_limit = 256M
```

يجب أن يكون post_max_size أكبر من مجموع الملفات والبيانات الإضافية في الطلب. قد تمنع حدود Apache أو Nginx أو الاستضافة استقبال الملفات الكبيرة. عشرة ملفات بحجم 30 MiB تتطلب أكثر من 300 MiB لكل طلب؛ خفّض max_files_per_request في الاستضافة المحدودة. تختلف إعدادات PHP CLI عن إعدادات PHP-FPM أو Apache.