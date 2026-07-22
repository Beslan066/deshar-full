import { escapeHtml,bindImageUpload, generateId } from "../helpers.js";

export function renderDropWordToImage(config) {
    const editor = document.getElementById('configEditor');
    if (!editor) return;

    config.items = Array.isArray(config.items) ? config.items : [];
    config.variants = Array.isArray(config.variants) ? config.variants : [];

    editor.innerHTML = `
        <!-- Режим 1: JSON Конфиг -->
        <div id="view-json-config" class="view-variant-block d-none">
            <div class="config-field-group mb-3">
                <label class="form-label">Конфигурация (JSON)</label>
                <textarea id="configJsonTextarea" class="form-control" name="config" rows="12"
                          style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(config, null, 2))}</textarea>
            </div>
        </div>

        <!-- Режим 2: Визуальные поля -->
        <div id="view-fields" class="view-variant-block">

            <div class="card border shadow-none mb-3">
                <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                    <span class="fw-semibold small text-uppercase">Варианты ответов</span>
                    <button type="button" id="add-variant-btn" class="btn btn-xs btn-primary py-1 px-2">
                        <i class="bx bx-plus me-1"></i> Добавить вариант
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="variants-container" class="d-flex flex-column gap-2"></div>
                </div>
            </div>

            <div class="card border shadow-none">
                <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                    <span class="fw-semibold small text-uppercase">Элементы (картинка → правильный вариант)</span>
                    <button type="button" id="add-item-btn" class="btn btn-xs btn-primary py-1 px-2">
                        <i class="bx bx-plus me-1"></i> Добавить элемент
                    </button>
                </div>
                <div class="card-body p-3">
                    <div id="items-container" class="d-flex flex-column gap-3"></div>
                </div>
            </div>

        </div>
    `;

    function nextId(arr) {
        return arr.length ? Math.max(...arr.map(x => x.id)) + 1 : 1;
    }

    function renderVariants() {
        const container = document.getElementById('variants-container');
        if (!container) return;

        if (config.variants.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-3 small border border-dashed rounded bg-light">
                    Нет вариантов. Нажмите "Добавить вариант".
                </div>`;
            return;
        }

        container.innerHTML = config.variants.map((variant, i) => `
            <div class="d-flex gap-2 align-items-center" data-variant-index="${i}">
               <span class="text-muted small" style="display: inline-block; width: 70px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" title="${variant.id}">
    id: ${variant.id}
</span>
                <input type="text" class="form-control form-control-sm variant-value-input"
                       value="${escapeHtml(variant.value || '')}"
                       placeholder="Текст варианта">
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-variant-btn">
                    <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                </button>
            </div>
        `).join('');

        container.querySelectorAll('.variant-value-input').forEach((input, i) => {
            input.addEventListener('input', (e) => {
                config.variants[i].value = e.target.value;
                syncJsonTextarea();
            });
        });

        container.querySelectorAll('.remove-variant-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                config.variants.splice(i, 1);
                renderVariants();
                renderItems();
                syncJsonTextarea();
            });
        });
    }



    function renderItems() {
        const container = document.getElementById('items-container');
        if (!container) return;

        if (config.items.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных элементов. Нажмите "Добавить элемент".
                </div>`;
            return;
        }

        container.innerHTML = config.items.map((item, index) => {
            const hasImage = item.imageUrl && item.imageUrl.trim() !== '';
            const safeUrl = escapeHtml(item.imageUrl || '');

            return `
            <div class="item-row row g-2 align-items-center border-bottom pb-3" data-index="${index}">
                      <span class="text-muted small" style="display: inline-block; width: 70px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" title="${item.id}">
    id: ${item.id}
</span>

                <!-- Инпут для ссылки на картинку -->
                <div class="col">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                        <input type="text" class="form-control form-control-sm item-image-input"
                               placeholder="Вставьте путь к изображению или загрузите файл"
                               value="${safeUrl}">
                    </div>
                </div>

                <!-- Превью -->
                <div class="col-auto" style="width: 50px;">
                    <div class="border rounded text-center bg-light item-preview" style="height: 38px; width: 45px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        ${hasImage
                            ? `<img src="${safeUrl}" style="max-width:100%; max-height:100%; object-fit: cover;">`
                            : `<i class="bx bx-image text-muted font-size-18"></i>`
                        }
                    </div>
                </div>

                <!-- Загрузка файла -->
                <div class="col-auto">
                    <label class="btn btn-sm btn-outline-secondary btn-icon mb-0 item-upload-btn text-center" title="Загрузить изображение">
                        <i class="menu-icon tf-icons ri-upload-line m-0"></i>
                        <input type="file" class="d-none item-file-input" accept="image/*">
                    </label>
                </div>

                <!-- Выбор правильного варианта -->
                <div class="col-auto">
                    <select class="form-select form-select-sm item-variant-select">
                        <option value="">— выберите вариант —</option>
                        ${config.variants.map(v => `
                            <option value="${v.id}" ${v.id === item.correctVariantId ? 'selected' : ''}>
                                ${escapeHtml(v.value || '')}
                            </option>
                        `).join('')}
                    </select>
                </div>

                <!-- Удаление -->
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-item-btn">
                        <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                    </button>
                </div>
            </div>
            `;
        }).join('');

        container.querySelectorAll('.item-image-input').forEach((input, i) => {
            input.addEventListener('input', (e) => {
                config.items[i].imageUrl = e.target.value.trim();
                syncJsonTextarea();
                updateItemPreview(i);
            });
        });

        container.querySelectorAll('.item-variant-select').forEach((select, i) => {
            select.addEventListener('change', (e) => {
                config.items[i].correctVariantId = e.target.value ? e.target.value : null;
                syncJsonTextarea();
            });
        });

        container.querySelectorAll('.remove-item-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                config.items.splice(i, 1);
                renderItems();
                syncJsonTextarea();
            });
        });

        container.querySelectorAll('.item-row').forEach((row) => {
            const index = Number(row.dataset.index);
            const fileInput = row.querySelector('.item-file-input');
            const uploadBtn = row.querySelector('.item-upload-btn');

            bindImageUpload(fileInput, uploadBtn, (url) => {
                config.items[index].imageUrl = url;
                syncJsonTextarea();
                renderItems();
            });
        });
    }

    function updateItemPreview(index) {
        const row = document.querySelector(`.item-row[data-index="${index}"]`);
        if (!row) return;
        const preview = row.querySelector('.item-preview');
        const url = config.items[index].imageUrl;
        preview.innerHTML = url
            ? `<img src="${escapeHtml(url)}" style="max-width:100%; max-height:100%; object-fit: cover;">`
            : `<i class="bx bx-image text-muted font-size-18"></i>`;
    }

    function syncJsonTextarea() {
        const textarea = document.getElementById('configJsonTextarea');
        if (textarea) textarea.value = JSON.stringify(config, null, 2);
        if (typeof window.updateJsonPreview === 'function') {
            window.updateJsonPreview();
        }
    }

    document.getElementById('add-variant-btn').addEventListener('click', () => {
        config.variants.push({ id: generateId("variant"), value: '' });
        renderVariants();
        renderItems();
        syncJsonTextarea();
    });

    document.getElementById('add-item-btn').addEventListener('click', () => {
        config.items.push({ id:generateId("item"), imageUrl: '', correctVariantId: null });
        renderItems();
        syncJsonTextarea();
    });

    renderVariants();
    renderItems();
}
