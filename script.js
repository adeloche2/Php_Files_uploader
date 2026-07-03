// === العناصر ===
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const selectedBox = document.getElementById('selectedBox');
const selList = document.getElementById('selList');
const selCount = document.getElementById('selCount');
const actionsBox = document.getElementById('actionsBox');
const clearBtn = document.getElementById('clearBtn');
const uploadForm = document.getElementById('uploadForm');
const uploadBtn = document.getElementById('uploadBtn');
const spinner = document.getElementById('spinner');
const progressBox = document.getElementById('progressBox');
const progBar = document.getElementById('progBar');
const progPct = document.getElementById('progPct');
const progLabel = document.getElementById('progLabel');
const toastBox = document.getElementById('toastBox');

let selectedFiles = [];
const MAX_SIZE = 50 * 1024 * 1024;
const ALLOWED = ['jpg','jpeg','png','gif','webp','svg','bmp','ico','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf','csv','zip','rar','7z','tar','gz','mp4','avi','mov','mkv','webm','mp3','wav','ogg','flac','aac','php','html','css','js','json','xml','sql','py'];

// === ألوان وأيقونات حسب النوع ===
function getColor(ext) {
    const m = { 'jpg|jpeg|png|gif|webp|svg|bmp|ico':'#10b981', 'pdf|doc|docx|xls|xlsx|ppt|pptx|txt|rtf|csv':'#3b82f6', 'zip|rar|7z|tar|gz':'#f59e0b', 'mp4|avi|mov|mkv|webm':'#ef4444', 'mp3|wav|ogg|flac|aac':'#8b5cf6', 'php|html|css|js|json|xml|sql|py':'#06b6d4' };
    for (const [k,v] of Object.entries(m)) if (new RegExp('^('+k+')$').test(ext)) return v;
    return '#6b7280';
}

function getIconClass(ext) {
    const m = { 'jpg|jpeg|png|gif|webp|svg|bmp|ico':'bi-image', 'pdf|doc|docx|xls|xlsx|ppt|pptx|txt|rtf|csv':'bi-file-earmark-text', 'zip|rar|7z|tar|gz':'bi-file-earmark-zip', 'mp4|avi|mov|mkv|webm':'bi-camera-video', 'mp3|wav|ogg|flac|aac':'bi-music-note-beamed', 'php|html|css|js|json|xml|sql|py':'bi-code-slash' };
    for (const [k,v] of Object.entries(m)) if (new RegExp('^('+k+')$').test(ext)) return v;
    return 'bi-file-earmark';
}

function fmtSize(b) {
    if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
    if (b >= 1024) return (b / 1024).toFixed(1) + ' KB';
    return b + ' B';
}

// === Toast ===
function toast(text, type = 'success') {
    const el = document.createElement('div');
    el.className = 'toast-item ' + type;
    const icons = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' };
    el.innerHTML = '<i class="bi ' + (icons[type] || icons.success) + '"></i> ' + text;
    toastBox.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// === رسالة PHP ===
if (window.phpMessage && window.phpMessage.text) {
    toast(window.phpMessage.text, window.phpMessage.type || 'success');
}

// === معالجة الملفات ===
function handleFiles(files) {
    for (let i = 0; i < files.length; i++) {
        const f = files[i];
        const ext = f.name.split('.').pop().toLowerCase();

        if (f.size > MAX_SIZE) {
            toast('"' + f.name + '" يتجاوز 50 ميجابايت', 'danger');
            continue;
        }
        if (!ALLOWED.includes(ext)) {
            toast('"' + f.name + '" نوع غير مدعوم', 'danger');
            continue;
        }
        if (selectedFiles.some(s => s.name === f.name && s.size === f.size)) continue;

        selectedFiles.push(f);
    }
    render();
}

function render() {
    if (selectedFiles.length === 0) {
        selectedBox.style.display = 'none';
        actionsBox.style.display = 'none';
        return;
    }

    selectedBox.style.display = 'block';
    actionsBox.style.display = 'flex';
    selCount.textContent = selectedFiles.length;

    selList.innerHTML = selectedFiles.map((f, i) => {
        const ext = f.name.split('.').pop().toLowerCase();
        const c = getColor(ext);
        const ic = getIconClass(ext);
        return '<div class="sel-item">' +
            '<div class="si-icon" style="background:' + c + '18;color:' + c + '"><i class="bi ' + ic + '"></i></div>' +
            '<div class="si-name">' + f.name + '</div>' +
            '<span class="si-size">' + fmtSize(f.size) + '</span>' +
            '<button type="button" class="si-del" onclick="removeFile(' + i + ')"><i class="bi bi-x-lg"></i></button>' +
            '</div>';
    }).join('');
}

function removeFile(i) {
    selectedFiles.splice(i, 1);
    render();
}

// === أحداث الرفع ===
dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });
fileInput.addEventListener('change', e => { handleFiles(e.target.files); fileInput.value = ''; });

// السحب والإفلات
['dragenter', 'dragover'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag-over'); }));
['dragleave', 'drop'].forEach(ev => dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); }));
dropZone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));
['dragover', 'drop'].forEach(ev => document.body.addEventListener(ev, e => e.preventDefault()));

// إلغاء الكل
clearBtn.addEventListener('click', () => { selectedFiles = []; render(); });

// === رفع AJAX ===
uploadForm.addEventListener('submit', e => {
    e.preventDefault();
    if (selectedFiles.length === 0) { toast('اختر ملفاً واحداً على الأقل', 'warning'); return; }

    const fd = new FormData();
    selectedFiles.forEach(f => fd.append('files[]', f));

    progressBox.style.display = 'block';
    uploadBtn.disabled = true;
    spinner.style.display = 'inline-block';
    progLabel.textContent = 'جاري رفع ' + selectedFiles.length + ' ملف...';

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            progBar.style.width = pct + '%';
            progPct.textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            progBar.style.width = '100%';
            progPct.textContent = '100%';
            progLabel.textContent = 'تم بنجاح!';
            setTimeout(() => window.location.reload(), 1000);
        } else {
            toast('خطأ أثناء الرفع', 'danger');
            resetUI();
        }
    });

    xhr.addEventListener('error', () => { toast('فشل الاتصال', 'danger'); resetUI(); });
    xhr.open('POST', location.href, true);
    xhr.send(fd);
});

function resetUI() {
    progressBox.style.display = 'none';
    progBar.style.width = '0%';
    progPct.textContent = '0%';
    uploadBtn.disabled = false;
    spinner.style.display = 'none';
}

// === فلاتر ===
document.querySelectorAll('.btn-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        document.querySelectorAll('.file-col').forEach(col => {
            col.style.display = (filter === 'all' || col.dataset.cat === filter) ? '' : 'none';
        });
    });
});