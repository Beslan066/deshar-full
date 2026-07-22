import { createTextareaUpdaters, escapeHtml, generateId } from "../helpers.js";

export function renderSingleQuiz(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!Array.isArray(config.variants)) {
        config.variants = [];
    }
    if (!config.correctVariantId) {
        config.correctVariantId = null;
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
                    <div class="col-12 mb-3">
                        <div class="card border shadow-none">
                         <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
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



    const variantsContainer = document.getElementById('variants-container');
    const addBtn = document.getElementById('add-variant-btn');
    function renderVariants() {
        if (!variantsContainer) return;
        if (config.variants.length === 0) {
            variantsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных букв. Нажмите "Добавить букву".
                </div>
            `;
            return;
        }

        variantsContainer.innerHTML = config.variants.map((variant, index) => {
            const safeContent = escapeHtml(variant.title ?? '');
            const isCorrect = config.correctVariantId === variant.id;
            return `
             <div class="variant-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
             <div class="col-auto">
                <span class="text-muted small fw-bold">${variant.itemNumber}.</span>
             </div>
             <div class="col-auto">
                    <div class="form-check d-flex align-items-center justify-content-center" style="min-height: 38px;">
                        <input type="radio"
       name="correctVariant"
       class="form-check-input variant-correct-checkbox mt-0"
       style="cursor: pointer;"
       title="Отметить как правильный вариант"
       ${isCorrect ? 'checked' : ''}>
                    </div>
                </div>
                <div class="col-auto">
                    <span class="badge bg-secondary">${escapeHtml(String(variant.id))}</span>
                </div>
                <div class="col">
                    <input type="text" maxLength="1" class="form-control form-control-sm variant-content-input"
                           placeholder="текст" value="${safeContent}">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center variant-delete-btn"  title="Удалить вариант">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `
        }).join('');
        variantsContainer.querySelectorAll('.variant-content-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                config.variants[idx].title = this.value;
                updateTextareaWithoutFullRender();
            });
        });
        variantsContainer.querySelectorAll('.variant-correct-checkbox').forEach(radio => {
            radio.addEventListener('change', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const variant = config.variants[idx];

                if (this.checked) {
                    config.correctVariantId = variant.id;
                }

                updateTextareaAndFullRender();
            });
        });
        variantsContainer.querySelectorAll('.variant-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                const removed = config.variants[idx];

                config.variants.splice(idx, 1);

                if (removed && config.correctVariantId === removed.id) {
                    config.correctVariantId = null;
                }

                config.variants.forEach((v, i) => {
                    if (v.itemNumber !== undefined) {
                        v.itemNumber = i + 1;
                    }
                });

                updateTextareaAndFullRender();
            });
        });
    }


    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderVariants);

    addBtn.addEventListener('click', () => {
        const nextItemNumber = config.variants.length + 1;

        config.variants.push({
            id: generateId('variant'),
            title: '',
            itemNumber: nextItemNumber
        });
        updateTextareaAndFullRender();
    });
    renderVariants();

}

