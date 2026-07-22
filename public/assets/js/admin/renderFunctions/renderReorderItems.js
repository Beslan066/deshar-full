import { escapeHtml, createTextareaUpdaters,generateId } from "../helpers.js";

export function renderReorderItems(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!Array.isArray(config.data)) {
        config.data = [];
    }
    if (!Array.isArray(config.correctOrderIds)) {
        config.correctOrderIds = [];
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
                        <div class="card-header bg-light p-2 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small text-uppercase">Шаги для упорядочивания</span>
                            <button type="button" id="add-step-btn" class="btn btn-xs btn-primary py-1 px-2 small">
                                <i class="ri-add-line align-middle me-1"></i>Добавить шаг
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <!-- Сюда будут рендериться элементы списка -->
                            <div id="steps-container" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const stepsContainer = document.getElementById('steps-container');
    const addStepBtn = document.getElementById('add-step-btn');

    function renderSteps() {
        if (config.data.length === 0) {
            stepsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных шагов. Нажмите "Добавить шаг".
                </div>
            `;
            return;
        }

        stepsContainer.innerHTML = config.data.map((item, index) => {
            const safeContent = escapeHtml(item.content ?? '');

            const correctPosition = config.correctOrderIds.indexOf(item.id) !== -1
                ? config.correctOrderIds.indexOf(item.id) + 1
                : 1;

            const selectOptions = Array.from({ length: config.data.length }, (_, i) => {
                const pos = i + 1;
                return `<option value="${pos}" ${pos === correctPosition ? 'selected' : ''}>${pos}</option>`;
            }).join('');

            return `
             <div class="reorder-item row g-2 align-items-center border-bottom pb-2" data-id="${item.id}" data-index="${index}">
                 <div class="col-auto d-flex align-items-center" title="Укажите правильный порядок для этого шага">
                    <span class="text-muted small fw-bold me-1">Порядок:</span>
                    <select class="form-select form-select-sm correct-order-select" style="width: 85px; cursor: pointer;">
                        ${selectOptions}
                    </select>
                 </div>

                 <div class="col-auto">
                    <span class="badge bg-secondary">${escapeHtml(String(item.id))}</span>
                 </div>

                 <div class="col">
                    <input type="text" class="form-control form-control-sm item-content-input"
                           placeholder="Описание действия/шага..." value="${safeContent}">
                 </div>

                 <!-- Кнопка удаления -->
                 <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center item-delete-btn" title="Удалить шаг">
                        <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                    </button>
                 </div>
              </div>
            `;
        }).join('');

        stepsContainer.querySelectorAll('.item-content-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.reorder-item').dataset.index);
                config.data[idx].content = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        stepsContainer.querySelectorAll('.correct-order-select').forEach(select => {
            select.addEventListener('change', function () {
                const itemElement = this.closest('.reorder-item');
                const currentId = itemElement.dataset.id;
                const newPosition = Number(this.value) - 1;

                config.correctOrderIds = config.correctOrderIds.filter(id => id !== currentId);
                config.correctOrderIds.splice(newPosition, 0, currentId);

                updateTextareaAndFullRender();
            });
        });

        stepsContainer.querySelectorAll('.item-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemElement = this.closest('.reorder-item');
                const idx = Number(itemElement.dataset.index);
                const currentId = itemElement.dataset.id;

                config.data.splice(idx, 1);
                config.correctOrderIds = config.correctOrderIds.filter(id => id !== currentId);

                updateTextareaAndFullRender();
            });
        });
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderSteps);

    if (addStepBtn) {
        addStepBtn.addEventListener('click', () => {
            const uniqueId = generateId('step');

            config.data.push({
                id: uniqueId,
                content: ''
            });
            config.correctOrderIds.push(uniqueId);

            updateTextareaAndFullRender();
        });
    }

    renderSteps();
}
