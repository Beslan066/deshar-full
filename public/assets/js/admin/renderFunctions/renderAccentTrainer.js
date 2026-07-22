import { createTextareaUpdaters, escapeHtml, generateId } from "../helpers.js";

export function renderAccentTrainer(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;
    if (!config.variants || !Array.isArray(config.variants)) {
        config.variants = [];
    }
    if (!Array.isArray(config.correct_variant_ids)) {
        config.correct_variant_ids = [];
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
                <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                    <span class="fw-semibold small text-uppercase">Варианты ответов</span>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-variant-btn">
                            <i class="bx bx-plus me-1"></i> Добавить вариант
                        </button>
                    </div>
                </div>
                <!-- Конструктор вариантов -->
                <div id="variants-container"></div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const container = document.getElementById('variants-container');
    const addBtn = document.getElementById('add-variant-btn');


    function renderVariants() {
        if (config.variants.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных вариантов. Нажмите "Добавить вариант".
                </div>
            `;
            return;
        }

        container.innerHTML = config.variants.map((variant, index) => {
            const isCorrect = config.correct_variant_ids.includes(variant.id);
            const safeId = escapeHtml(String(variant.id));
            const safeLetter = escapeHtml(variant.letter ?? '');
            return `
              <div class="variant-item row g-2 align-items-center border-bottom pb-3" data-index="${index}">
                <div class="col-auto">
                    <div class="form-check d-flex align-items-center justify-content-center" style="min-height: 38px;">
                        <input type="checkbox" class="form-check-input variant-correct-checkbox mt-0"
                               style="cursor: pointer;"
                               title="Отметить как правильный вариант"
                               ${isCorrect ? 'checked' : ''}>
                    </div>
                </div>
                <!-- ID варианта (строка) -->
                <div class="col-auto">
                    <span class="badge bg-secondary text-truncate" style="max-width: 140px;" title="${safeId}">${safeId}</span>
                </div>
                <!-- Буква (редактируемая) -->
                <div class="col-auto">
                    <input type="text" maxlength="1"
                           class="form-control form-control-sm variant-letter-input text-center"
                           style="width: 48px;"
                           value="${safeLetter}">
                </div>
                <div class="col-auto ms-auto">
                     <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center variant-delete-btn" title="Удалить вариант">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        container.querySelectorAll('.variant-correct-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const variant = config.variants[idx];

                if (this.checked) {
                    if (!config.correct_variant_ids.includes(variant.id)) {
                        config.correct_variant_ids.push(variant.id);
                    }
                } else {
                    config.correct_variant_ids = config.correct_variant_ids.filter(id => id !== variant.id);
                }

                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.variant-letter-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                config.variants[idx].letter = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.variant-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const removed = config.variants[idx];
                config.variants.splice(idx, 1);

                if (removed) {
                    config.correct_variant_ids = config.correct_variant_ids.filter(id => id !== removed.id);
                }

                updateTextareaAndFullRender();
            });
        });
    }
    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderVariants);
    addBtn.addEventListener('click', () => {
        config.variants.push({ id: generateId('variant'), letter: '' });
        updateTextareaAndFullRender();
    });

    renderVariants();
}
