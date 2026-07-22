import { escapeHtml, generateId, createTextareaUpdaters, debounce } from "../helpers.js";

export function renderColorizeWords(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!Array.isArray(config.tools)) {
        config.tools = [];
    }
    if (!Array.isArray(config.variants)) {
        config.variants = [];
    }

    ensureEraseTool();

    function ensureEraseTool() {
        const hasErase = config.tools.some(t => t.type === 'erase');
        if (!hasErase) {
            config.tools.push({ type: 'erase', toolName: 'Стереть' });
        }
    }

    const HEX_COLOR_RE = /^#[0-9a-fA-F]{6}$/;

    function normalizeHex(value, fallback = '#cccccc') {
        return HEX_COLOR_RE.test(value) ? value : fallback;
    }

    function paintTools() {
        return config.tools.filter(t => t.type === 'paint');
    }

    function eraseTool() {
        return config.tools.find(t => t.type === 'erase');
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
                <!-- Инструменты -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Инструменты</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-tool-btn">
                                <i class="bx bx-plus me-1"></i> Добавить маркер
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="tools-container" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Инструмент "Стереть" присутствует всегда и не может быть удалён.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Слова -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Слова</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-variant-btn">
                                <i class="bx bx-plus me-1"></i> Добавить слово
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="variants-container" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Для каждого слова выберите маркер, которым оно должно быть закрашено.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const toolsContainer = document.getElementById('tools-container');
    const variantsContainer = document.getElementById('variants-container');
    const addToolBtn = document.getElementById('add-tool-btn');
    const addVariantBtn = document.getElementById('add-variant-btn');

    function colorOptionsHtml(selectedColor) {
        const options = paintTools().map(t => {
            const color = normalizeHex(t.toolColor);
            const label = escapeHtml(t.toolName || color);
            const isSelected = color === selectedColor;
            return `<option value="${color}" ${isSelected ? 'selected' : ''}>${label}</option>`;
        }).join('');

        return `<option value="">— не выбрано —</option>${options}`;
    }

    // ============================================================
    // ИНСТРУМЕНТЫ
    // ============================================================

    function renderTools() {
        const rows = config.tools.map((tool, index) => {
            const isErase = tool.type === 'erase';
            const safeName = escapeHtml(tool.toolName ?? '');

            if (isErase) {
                return `
                  <div class="tool-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                    <div class="col-auto">
                        <span class="badge bg-dark">erase</span>
                    </div>
                    <div class="col-auto" style="width: 24px;"></div>
                    <div class="col">
                        <input type="text" class="form-control form-control-sm tool-name-input" value="${safeName}">
                    </div>
                    <div class="col-auto">
                        <span class="text-muted small">по умолчанию, нельзя удалить</span>
                    </div>
                  </div>
                `;
            }

            const safeColor = normalizeHex(tool.toolColor);
            return `
              <div class="tool-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary">paint</span>
                </div>
                <div class="col-auto">
                    <input type="color" class="form-control form-control-color tool-color-input"
                           title="Выбрать цвет" value="${safeColor}">
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm tool-name-input"
                           placeholder="Название маркера" value="${safeName}">
                </div>
                <div class="col-auto">
                         <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center tool-delete-btn" title="Удалить маркер">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        toolsContainer.innerHTML = rows;

        toolsContainer.querySelectorAll('.tool-name-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.tool-item').dataset.index);
                config.tools[idx].toolName = this.value;
                updateTextareaWithoutFullRender();
                debouncedFullRender();
            });
        });

        toolsContainer.querySelectorAll('.tool-color-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.tool-item').dataset.index);
                const tool = config.tools[idx];
                const oldColor = normalizeHex(tool.toolColor);
                const newColor = normalizeHex(this.value);

                tool.toolColor = newColor;


                config.variants.forEach(variant => {
                    if (variant.correctColor === oldColor) {
                        variant.correctColor = newColor;
                    }
                });
                updateTextareaWithoutFullRender();
                debouncedFullRender();
            });
        });

        toolsContainer.querySelectorAll('.tool-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.tool-item').dataset.index);
                const removed = config.tools[idx];

                if (removed.type === 'erase') return;

                config.tools.splice(idx, 1);

                const removedColor = normalizeHex(removed.toolColor, null);
                if (removedColor) {
                    config.variants.forEach(variant => {
                        if (variant.correctColor === removedColor) {
                            variant.correctColor = '';
                        }
                    });
                }

                updateTextareaAndFullRender();
            });
        });
    }

    // ============================================================
    // СЛОВА (VARIANTS)
    // ============================================================

    function renderVariants() {
        ensureEraseTool();
        renderTools();

        if (config.variants.length === 0) {
            variantsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных слов. Нажмите "Добавить слово".
                </div>
            `;
            return;
        }

        variantsContainer.innerHTML = config.variants.map((variant, index) => {
            const safeContent = escapeHtml(variant.content ?? '');
            const safeColor = normalizeHex(variant.correctColor, '#ffffff');
            return `
              <div class="variant-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary">${escapeHtml(String(variant.id))}</span>
                </div>
                <div class="col-auto">
                    <div class="border rounded" title="Цвет наследуется от инструмента"
                         style="width: 24px; height: 24px; background: ${safeColor};"></div>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm variant-content-input"
                           placeholder="Слово" value="${safeContent}">
                </div>
                <div class="col-auto" style="min-width: 180px;">
                    <select class="form-select form-select-sm variant-color-select">
                        ${colorOptionsHtml(normalizeHex(variant.correctColor, ''))}
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center variant-delete-btn"  title="Удалить слово">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        variantsContainer.querySelectorAll('.variant-content-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                config.variants[idx].content = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        variantsContainer.querySelectorAll('.variant-color-select').forEach(select => {
            select.addEventListener('change', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                config.variants[idx].correctColor = this.value;
                updateTextareaAndFullRender();
            });
        });

        variantsContainer.querySelectorAll('.variant-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);
                config.variants.splice(idx, 1);
                updateTextareaAndFullRender();
            });
        });
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderVariants);

    const debouncedFullRender = debounce(() => {
        updateTextareaAndFullRender();
    }, 400);

    addToolBtn.addEventListener('click', () => {
        config.tools.push({ type: 'paint', toolName: '', toolColor: '#cccccc' });
        updateTextareaAndFullRender();
    });

    addVariantBtn.addEventListener('click', () => {
        config.variants.push({ id: generateId('variant'), content: '', correctColor: '' });
        updateTextareaAndFullRender();
    });

    renderVariants();
}
