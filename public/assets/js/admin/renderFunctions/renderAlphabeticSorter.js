import { escapeHtml, generateId, createTextareaUpdaters } from "../helpers.js";

export function renderAlphabeticSorter(config) {
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
                <!-- Слоты -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Слоты (позиции)</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-slot-btn">
                                <i class="bx bx-plus me-1"></i> Добавить слот
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="slots-container" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Для каждого слота выберите правильное слово из списка вариантов ниже.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Варианты слов -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Варианты слов</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-variant-btn">
                                <i class="bx bx-plus me-1"></i> Добавить вариант
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

    function variantOptionsHtml(selectedValue) {
        const options = config.variants.map(v => {
            const safeValue = escapeHtml(v.value);
            const isSelected = v.value === selectedValue;
            return `<option value="${safeValue}" ${isSelected ? 'selected' : ''}>${safeValue}</option>`;
        }).join('');

        return `<option value="">— не выбрано —</option>${options}`;
    }

    function renderSlots() {
        if (config.slots.length === 0) {
            slotsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных слотов. Нажмите "Добавить слот".
                </div>
            `;
            return;
        }

        slotsContainer.innerHTML = config.slots.map((slot, index) => {
            const safeTitle = escapeHtml(slot.slotTitle ?? '');
            return `
              <div class="slot-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary">${escapeHtml(String(slot.id))}</span>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm slot-title-input"
                           placeholder="Название слота" value="${safeTitle}">
                </div>
                <div class="col-auto" style="min-width: 180px;">
                    <select class="form-select form-select-sm slot-correct-select">
                        ${variantOptionsHtml(slot.correctValue)}
                    </select>
                </div>
                <div class="col-auto">
                     <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center slot-delete-btn" title="Удалить слот">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        slotsContainer.querySelectorAll('.slot-title-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.slot-item').dataset.index);
                config.slots[idx].slotTitle = this.value;
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

        slotsContainer.querySelectorAll('.slot-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.slot-item').dataset.index);
                config.slots.splice(idx, 1);
                updateTextareaAndFullRender();
            });
        });
    }

    function renderVariants() {
        if (config.variants.length === 0) {
            variantsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных вариантов. Нажмите "Добавить вариант".
                </div>
            `;
            renderSlots();
            return;
        }

        variantsContainer.innerHTML = config.variants.map((variant, index) => {
            const safeValue = escapeHtml(variant.value ?? '');
            return `
              <div class="variant-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary">${escapeHtml(String(variant.id))}</span>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm variant-value-input" value="${safeValue}">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center variant-delete-btn" title="Удалить вариант">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        variantsContainer.querySelectorAll('.variant-value-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const oldValue = config.variants[idx].value;
                const newValue = this.value;

                config.variants[idx].value = newValue;

                config.slots.forEach(slot => {
                    if (slot.correctValue === oldValue) {
                        slot.correctValue = newValue;
                    }
                });

                updateTextareaAndFullRender();
            });
        });

        variantsContainer.querySelectorAll('.variant-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const removed = config.variants[idx];
                config.variants.splice(idx, 1);

                if (removed) {
                    config.slots.forEach(slot => {
                        if (slot.correctValue === removed.value) {
                            slot.correctValue = '';
                        }
                    });
                }

                updateTextareaAndFullRender();
            });
        });

        renderSlots();
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderVariants);

    addSlotBtn.addEventListener('click', () => {
        config.slots.push({
            id: generateId('slot'),
            slotTitle: '',
            correctValue: ''
        });
        updateTextareaAndFullRender();
    });

    addVariantBtn.addEventListener('click', () => {
        config.variants.push({ id: generateId('variant'), value: '' });
        updateTextareaAndFullRender();
    });

    renderVariants();
}
