/**
 * يرفع الصور مباشرة إلى S3-compatible storage عبر روابط مؤقتة.
 * هذا يتجنب إرسال الصور الكبيرة إلى PHP Function في Vercel.
 */
'use strict';
(() => {
  const input = document.getElementById('images');
  const zone = document.getElementById('drop-zone');
  const form = document.getElementById('upload-form');
  const list = document.getElementById('selected-files');
  const wrap = document.getElementById('upload-progress');
  const bar = document.getElementById('progress-bar');
  const pct = document.getElementById('progress-percent');
  const label = document.getElementById('progress-label');
  const submit = form?.querySelector('button[type="submit"]');
  if (!input || !zone || !form || !list || !wrap || !bar || !pct || !submit) return;

  const maxBytes = 30 * 1024 * 1024;
  const maxFiles = 10;
  const allowed = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp']);
  const csrf = form.querySelector('input[name="csrf_token"]')?.value || '';
  let chosen = [];

  const sizeLabel = n => n >= 1048576 ? (n / 1048576).toFixed(2) + ' MiB' : n >= 1024 ? (n / 1024).toFixed(1) + ' KiB' : n + ' B';
  function syncInput() {
    const dt = new DataTransfer();
    chosen.forEach(file => dt.items.add(file));
    input.files = dt.files;
  }
  function render() {
    list.replaceChildren();
    chosen.forEach((file, index) => {
      const row = document.createElement('div');
      row.className = 'selected-file';
      const fileLabel = document.createElement('span');
      fileLabel.textContent = file.name + ' · ' + sizeLabel(file.size);
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'secondary-button';
      remove.textContent = 'إزالة';
      remove.addEventListener('click', () => { chosen.splice(index, 1); syncInput(); render(); });
      row.append(fileLabel, remove);
      list.append(row);
    });
  }
  function addFiles(files) {
    const errors = [];
    const known = new Set(chosen.map(f => [f.name, f.size, f.lastModified].join(':')));
    for (const file of Array.from(files)) {
      const key = [file.name, file.size, file.lastModified].join(':');
      if (!allowed.has(file.type)) errors.push(file.name + ': نوع غير مدعوم.');
      else if (!file.size || file.size > maxBytes) errors.push(file.name + ': يجب ألا يتجاوز الحجم 30 MiB.');
      else if (known.has(key)) continue;
      else if (chosen.length >= maxFiles) errors.push('الحد الأقصى ' + maxFiles + ' صور في الطلب.');
      else { chosen.push(file); known.add(key); }
    }
    syncInput(); render();
    if (errors.length) alert(errors.join('\n'));
  }
  function requestJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    }).then(async response => {
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data.error || ('HTTP ' + response.status));
      return data;
    });
  }
  function putFile(url, file, onProgress) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('PUT', url, true);
      xhr.setRequestHeader('Content-Type', file.type);
      xhr.upload.addEventListener('progress', event => {
        if (event.lengthComputable) onProgress(event.loaded / event.total);
      });
      xhr.addEventListener('load', () => xhr.status >= 200 && xhr.status < 300 ? resolve() : reject(new Error('تعذر رفع الملف إلى مخزن الصور (HTTP ' + xhr.status + '). راجع إعداد CORS.')));
      xhr.addEventListener('error', () => reject(new Error('فشل الاتصال بمخزن الصور. تحقق من CORS والاتصال.')));
      xhr.send(file);
    });
  }

  input.addEventListener('change', () => addFiles(input.files));
  ['dragenter', 'dragover'].forEach(type => zone.addEventListener(type, event => { event.preventDefault(); zone.classList.add('is-dragging'); }));
  ['dragleave', 'drop'].forEach(type => zone.addEventListener(type, event => { event.preventDefault(); zone.classList.remove('is-dragging'); }));
  zone.addEventListener('drop', event => { if (event.dataTransfer?.files) addFiles(event.dataTransfer.files); });

  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!chosen.length) { alert('اختر صورة واحدة على الأقل.'); return; }
    submit.disabled = true;
    submit.textContent = 'جاري الرفع...';
    wrap.hidden = false;
    const files = [...chosen];
    let completed = 0;
    const errors = [];
    for (const file of files) {
      label.textContent = 'رفع ' + file.name;
      let currentProgress = 0;
      const updateProgress = fraction => {
        currentProgress = fraction;
        const percent = Math.round(((completed + currentProgress) / files.length) * 100);
        bar.value = percent;
        pct.textContent = percent + '%';
      };
      try {
        const ticket = await requestJson('/api/upload-url', { name: file.name, size: file.size, type: file.type });
        await putFile(ticket.upload_url, file, updateProgress);
        await requestJson('/api/upload-complete', {
          key: ticket.key,
          name: ticket.name,
          size: ticket.size,
          mime_type: ticket.mime_type,
          extension: ticket.extension,
          upload_token: ticket.upload_token
        });
      } catch (error) {
        errors.push(file.name + ': ' + (error?.message || 'فشل الرفع.'));
      }
      completed++;
      updateProgress(0);
    }
    submit.disabled = false;
    submit.textContent = 'رفع الصور';
    if (errors.length) {
      alert('اكتمل الرفع مع وجود أخطاء:\n' + errors.join('\n'));
      if (completed > errors.length) location.reload();
    } else {
      label.textContent = 'اكتمل الرفع';
      bar.value = 100;
      pct.textContent = '100%';
      location.reload();
    }
  });
})();