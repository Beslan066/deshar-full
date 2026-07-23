import { createTextareaUpdaters, debounce, escapeHtml, generateId } from "../helpers.js";

export function renderDropWordToText(config) {
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
                    <span class="fw-semibold small text-uppercase">Элементы (текст → правильный вариант)</span>
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

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderAll);

    const debouncedSync = debounce(() => {
        updateTextareaWithoutFullRender();
    }, 400);

    function renderAll() {
        renderItems();
        renderVariants();
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
        const processInputDebounced = debounce((variantId, newValue) => {
            updateItemOptionsLabel(variantId, newValue);
            updateTextareaWithoutFullRender();
        }, 400);
        container.querySelectorAll('.variant-value-input').forEach((input, i) => {
            input.addEventListener('input', (e) => {
                const variant = config.variants[i];
                const newValue = e.target.value;
                variant.value = newValue;
                processInputDebounced(variant.id, newValue);
            });
        });


        container.querySelectorAll('.remove-variant-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                config.variants.splice(i, 1);
                updateTextareaAndFullRender();
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
            const safeContent = escapeHtml(item.content || '');

            return `
            <div class="item-row row g-2 align-items-center border-bottom pb-3" data-index="${index}">
                      <span class="text-muted small" style="display: inline-block; width: 70px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" title="${item.id}">
    id: ${item.id}
</span>

                <!-- Текст элемента -->
                <div class="col">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                        <input type="text" class="form-control form-control-sm item-content-input"
                               placeholder="text"
                               value="${safeContent}">
                    </div>
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

        container.querySelectorAll('.item-content-input').forEach((input, i) => {
            input.addEventListener('input', (e) => {
                config.items[i].content = e.target.value.trim();
                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.item-variant-select').forEach((select, i) => {
            select.addEventListener('change', (e) => {
                config.items[i].correctVariantId = e.target.value ? e.target.value : null;
                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.remove-item-btn').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                config.items.splice(i, 1);
                updateTextareaAndFullRender();
            });
        });
    }

    function updateItemOptionsLabel(variantId, text) {
        document.querySelectorAll('.item-variant-select').forEach((select) => {
            const option = Array.from(select.options).find(o => String(o.value) === String(variantId));
            if (option) option.textContent = text;
        });
    }

    document.getElementById('add-variant-btn').addEventListener('click', () => {
        config.variants.push({ id: generateId("variant"), value: '' });
        updateTextareaAndFullRender();
    });

    document.getElementById('add-item-btn').addEventListener('click', () => {
        config.items.push({ id: generateId("item"), content: '', correctVariantId: null });
        updateTextareaAndFullRender();
    });

    renderAll();
}
