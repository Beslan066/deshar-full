export function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    export async function uploadConfigImage(file) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const formData = new FormData();
    formData.append('image', file);

    const response = await fetch('/admin/tasks/upload-config-image', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: formData
    });

    if (!response.ok) {
        throw new Error('Upload failed');
    }

    const data = await response.json();
    return data.url; // { url: "..." }
}

export function bindImageUpload(inputEl, buttonEl, onSuccess) {
    inputEl.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        try {
            buttonEl.classList.add('disabled');
            const url = await uploadConfigImage(file);
            onSuccess(url, file);
        } catch (error) {
            console.error('Upload error:', error);
            alert(`Ошибка при загрузке файла ${file.name}`);
        } finally {
            buttonEl.classList.remove('disabled');
            this.value = '';
        }
    });
}
export function generateId(prefix = '') {
    const id = Date.now().toString(36) + Math.random().toString(36).substring(2, 9);
    return prefix ? `${prefix}-${id}` : id;
}
export function shuffle(array) {
    const result = [...array];

    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [result[i], result[j]] = [result[j], result[i]];
    }

    return result;
}


export function createTextareaUpdaters(config, renderFn) {
    const textarea = document.getElementById('configJsonTextarea');

    function syncTextarea() {
        if (textarea) {
            textarea.value = JSON.stringify(config, null, 2);
        }
    }

    function notifyPreview() {
        if (typeof window.updateJsonPreview === 'function') {
            window.updateJsonPreview();
        }
    }

    function updateTextareaAndFullRender() {
        syncTextarea();
        if (typeof renderFn === 'function') {
            renderFn();
        }
        notifyPreview();
    }

    function updateTextareaWithoutFullRender() {
        syncTextarea();
        notifyPreview();
    }

    // ------------------------------------------------------------
    // Обратная синхронизация: JSON -> поля
    // ------------------------------------------------------------
    if (textarea) {
        textarea.addEventListener('input', () => {
            let parsed;
            try {
                parsed = JSON.parse(textarea.value);
            } catch (err) {
                return;
            }

            if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
                return;
            }
            Object.keys(config).forEach(key => delete config[key]);
            Object.assign(config, parsed);
            if (typeof renderFn === 'function') {
                renderFn();
            }
            notifyPreview();
        });
    }

    return { updateTextareaAndFullRender, updateTextareaWithoutFullRender };
}
export function debounce(fn, delay = 400) {
    let timer = null;
    return function debounced(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}
