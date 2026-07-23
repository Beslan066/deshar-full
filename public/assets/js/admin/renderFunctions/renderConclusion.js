import { createTextareaUpdaters, escapeHtml, generateId } from "../helpers.js";

export function renderConclusion(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!config.data || !Array.isArray(config.data)) {
        config.data = [];
    }

    const html = `
        <!-- Режим 1: JSON Конфиг -->
        <div id="view-json-config" class="view-variant-block d-none">
            <div class="config-field-group mb-3">
                <label class="form-label">Конфикаруция (JSON)</label>
                <textarea id="configJsonTextarea" class="form-control" name="config" rows="12"
                          style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(config, null, 2))}</textarea>
            </div>
        </div>

        <!-- Режим 2: Визуальные поля -->
        <div id="view-fields" class="view-variant-block">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                    <span class="fw-semibold small text-uppercase">Список предложений (Conclusion)</span>
                    <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-sentence-btn">
                        <i class="bx bx-plus me-1"></i> Добавить предложение
                    </button>
                </div>
                <!-- Конструктор предложений -->
                <div id="conclusion-container" class="card-body p-3"></div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const container = document.getElementById('conclusion-container');
    const addSentenceBtn = document.getElementById('add-sentence-btn');

    function renderItems() {
        if (config.data.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных предложений. Нажмите "Добавить предложение".
                </div>
            `;
            return;
        }

        container.innerHTML = config.data.map((item, itemIdx) => {
            const safeValue = escapeHtml(item.value ?? '');

            const variantsHtml = (item.variants || []).map((v, vIdx) => {
                const safeVValue = escapeHtml(v.value ?? '');
                return `
                    <div class="d-flex align-items-center gap-2 mb-2 variant-row" data-v-index="${vIdx}">
                        <input type="text" class="form-control form-control-sm item-variant-input"
                               value="${safeVValue}" placeholder="Вариант ответа">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger item-variant-delete-btn" title="Удалить вариант">
                            <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                        </button>
                    </div>
                `;
            }).join('');

            return `
                <div class="conclusion-item border rounded p-3 mb-3 bg-white" data-item-index="${itemIdx}">
                    <div class="row g-3">
                        <!-- Текст предложения с разметкой -->
                        <div class="col-12">
                            <label class="form-label fw-medium small">Текст предложения (Выделяйте пропуски через {{слово}}):</label>
                            <div class="input-group">
                                <input type="text" class="form-control item-value-input"
                                       value="${safeValue}"
                                       placeholder="Слова в предложении {{связаны}} между собой...">
                                <button type="button" class="btn btn-outline-danger item-delete-btn" title="Удалить предложение">
                                    <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">Слова внутри двойных фигурных скобок автоматически станут скрытыми слотами.</small>
                        </div>

                        <!-- Блок управления вариантами ответов -->
                        <div class="col-12 border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-secondary small">Варианты для выбора:</span>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 item-add-variant-btn">
                                    + Добавить вариант
                                </button>
                            </div>
                            <div class="variants-list-container">
                                ${variantsHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.querySelectorAll('.item-value-input').forEach(input => {
            input.addEventListener('input', function () {
                const itemIdx = Number(this.closest('.conclusion-item').dataset.itemIndex);
                const textValue = this.value;
                config.data[itemIdx].value = textValue;

                const regex = /{{(.*?)}}/g;
                let match;
                const newSlots = [];
                let slotIdCounter = 1;

                while ((match = regex.exec(textValue)) !== null) {
                    const word = match[1].trim();
                    if (word) {
                        newSlots.push({
                            id: slotIdCounter++,
                            correct: word,
                            current: null
                        });
                    }
                }

                config.data[itemIdx].slots = newSlots;
                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.item-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemIdx = Number(this.closest('.conclusion-item').dataset.itemIndex);
                config.data.splice(itemIdx, 1);
                updateTextareaAndFullRender();
            });
        });

        container.querySelectorAll('.item-variant-input').forEach(input => {
            input.addEventListener('input', function () {
                const itemIdx = Number(this.closest('.conclusion-item').dataset.itemIndex);
                const vIdx = Number(this.closest('.variant-row').dataset.vIndex);
                config.data[itemIdx].variants[vIdx].value = this.value;
                updateTextareaWithoutFullRender();
            });
        });


        container.querySelectorAll('.item-add-variant-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemIdx = Number(this.closest('.conclusion-item').dataset.itemIndex);
                if (!config.data[itemIdx].variants) {
                    config.data[itemIdx].variants = [];
                }


                const newVariantId = generateId('conclusion-variant');
                config.data[itemIdx].variants.push({
                    id: newVariantId,
                    value: ''
                });

                updateTextareaAndFullRender();
            });
        });

        container.querySelectorAll('.item-variant-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemIdx = Number(this.closest('.conclusion-item').dataset.itemIndex);
                const vIdx = Number(this.closest('.variant-row').dataset.vIndex);
                config.data[itemIdx].variants.splice(vIdx, 1);
                updateTextareaAndFullRender();
            });
        });
    }


    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderItems);


    addSentenceBtn.addEventListener('click', () => {
        config.data.push({
            id: generateId('conclusion'),
            slots: [],
            value: '',
            variants: [],
            completed: false
        });
        updateTextareaAndFullRender();
    });
    renderItems();
}
