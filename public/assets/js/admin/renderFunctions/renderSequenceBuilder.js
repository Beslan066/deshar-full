import { escapeHtml, createTextareaUpdaters, generateId, debounce } from "../helpers.js";

export function renderSequenceBuilder(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;


    if (!Array.isArray(config.slots)) {
        config.slots = [];
    }
    if (!Array.isArray(config.variants)) {
        config.variants = [];
    }


    const html = `
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
            <div class="row">
                <!-- Секция 1: Элементы цепочки (Слоты) -->
                <div class="col-md-7 mb-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-header bg-light p-2 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-uppercase">Элементы цепочки (Слоты)</span>
                            <button type="button" id="add-slot-btn" class="btn btn-xs btn-primary py-1 px-2 small">
                                <i class="ri-add-line align-middle me-1"></i>Добавить слот
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="slots-container" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Секция 2: Варианты ответов -->
                <div class="col-md-5 mb-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-header bg-light p-2 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-uppercase">Варианты ответов</span>
                            <button type="button" id="add-variant-btn" class="btn btn-xs btn-outline-primary py-1 px-2 small">
                                <i class="ri-add-line align-middle me-1"></i>Добавить вариант
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="variants-container" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const slotsContainer = document.getElementById('slots-container');
    const variantsContainer = document.getElementById('variants-container');
    const addSlotBtn = document.getElementById('add-slot-btn');
    const addVariantBtn = document.getElementById('add-variant-btn');


    function renderLists() {
        renderSlotsList();
        renderVariantsList();
    }



    function renderSlotsList() {
        if (config.slots.length === 0) {
            slotsContainer.innerHTML = `
                <div class="text-muted text-center py-3 small border border-dashed rounded bg-light">
                    Цепочка пуста. Добавьте элемент.
                </div>
            `;
            return;
        }

        slotsContainer.innerHTML = config.slots.map((slot, index) => {
            const safeContent = escapeHtml(slot.content ?? '');


            const variantOptions = config.variants.map(v => {
                const isSelected = slot.correctValue === v.content;
                return `<option value="${escapeHtml(v.content ?? '')}" ${isSelected ? 'selected' : ''}>${escapeHtml(v.content ?? '')}</option>`;
            }).join('');

            return `
                <div class="slot-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                    <div class="col-auto">
                        <span class="badge bg-secondary">${escapeHtml(String(slot.slotId))}</span>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm slot-content-input"
                               placeholder="Описание или текст цепочки..." value="${safeContent}">
                    </div>
                    <div class="col-auto" style="min-width: 120px;">
                        <select class="form-select form-select-sm slot-correct-select" title="Правильный ответ">
                            <option value="">-- Ответ --</option>
                            ${variantOptions}
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger item-delete-slot-btn" title="Удалить слот">
                            <i class="ri-delete-bin-line m-0"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');


        slotsContainer.querySelectorAll('.slot-content-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.slot-item').dataset.index);
                config.slots[idx].content = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        slotsContainer.querySelectorAll('.slot-correct-select').forEach(select => {
            select.addEventListener('change', function () {
                const idx = Number(this.closest('.slot-item').dataset.index);
                config.slots[idx].correctValue = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        slotsContainer.querySelectorAll('.item-delete-slot-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.slot-item').dataset.index);
                config.slots.splice(idx, 1);
                updateTextareaAndFullRender();
            });
        });
    }


    function renderVariantsList() {
        if (config.variants.length === 0) {
            variantsContainer.innerHTML = `
                <div class="text-muted text-center py-3 small border border-dashed rounded bg-light">
                    Нет вариантов. Добавьте значение.
                </div>
            `;
            return;
        }

        variantsContainer.innerHTML = config.variants.map((variant, index) => {
            const safeContent = escapeHtml(variant.content ?? '');
            return `
                <div class="variant-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                    <div class="col-auto">
                        <span class="badge bg-light text-dark border">${escapeHtml(String(variant.id))}</span>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm variant-content-input"
                               placeholder="Значение..." value="${safeContent}">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger item-delete-variant-btn" title="Удалить вариант">
                            <i class="ri-delete-bin-line m-0"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');


        variantsContainer.querySelectorAll('.variant-content-input').forEach(input => {
            const debouncedUpdate = debounce((idx, oldValue, newValue) => {
                config.variants[idx].content = newValue;
                config.slots.forEach(slot => {
                    if (slot.correctValue === oldValue) {
                        slot.correctValue = newValue;
                    }
                });
                updateTextareaAndFullRender();
            }, 400);

            input.addEventListener('input', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const oldValue = config.variants[idx].content;
                const newValue = this.value;
                debouncedUpdate(idx, oldValue, newValue);
            });
        });

        variantsContainer.querySelectorAll('.item-delete-variant-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const removedValue = config.variants[idx].content;
                config.variants.splice(idx, 1);
                config.slots.forEach(slot => {
                    if (slot.correctValue === removedValue) {
                        slot.correctValue = "";
                    }
                });

                updateTextareaAndFullRender();
            });
        });
    }


    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderLists);

    if (addSlotBtn) {
        addSlotBtn.addEventListener('click', () => {
            config.slots.push({ slotId: generateId("seq-slot"), content: '', correctValue: '' });
            updateTextareaAndFullRender();
        });
    }
    if (addVariantBtn) {
        addVariantBtn.addEventListener('click', () => {
            config.variants.push({ id: generateId("seq-variant"), content: '' });
            updateTextareaAndFullRender();
        });
    }
    renderLists();
}
