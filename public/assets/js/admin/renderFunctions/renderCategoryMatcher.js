import { escapeHtml, generateId, createTextareaUpdaters, debounce } from "../helpers.js";

export function renderCategoryMatcher(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!Array.isArray(config.categories)) {
        config.categories = [];
    }
    if (!Array.isArray(config.items)) {
        config.items = [];
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
                <!-- Категории -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Категории</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-category-btn">
                                <i class="bx bx-plus me-1"></i> Добавить категорию
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="categories-container" class="d-flex flex-column gap-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Элементы -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Элементы</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-item-btn">
                                <i class="bx bx-plus me-1"></i> Добавить элемент
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="items-container" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Цвет элемента наследуется от выбранной категории и меняется вместе с ней.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const categoriesContainer = document.getElementById('categories-container');
    const itemsContainer = document.getElementById('items-container');
    const addCategoryBtn = document.getElementById('add-category-btn');
    const addItemBtn = document.getElementById('add-item-btn');

    const HEX_COLOR_RE = /^#[0-9a-fA-F]{6}$/;

    function normalizeHex(value, fallback = '#cccccc') {
        return HEX_COLOR_RE.test(value) ? value : fallback;
    }

    function findCategory(id) {
        return config.categories.find(c => c.id === id) || null;
    }

    function categoryOptionsHtml(selectedId) {
        const options = config.categories.map(c => {
            const safeId = escapeHtml(c.id);
            const safeLabel = escapeHtml(c.label ?? c.id);
            return `<option value="${safeId}" ${c.id === selectedId ? 'selected' : ''}>${safeLabel}</option>`;
        }).join('');

        return `<option value="">— не выбрано —</option>${options}`;
    }

    // ============================================================
    // КАТЕГОРИИ
    // ============================================================

    function renderCategories() {
        if (config.categories.length === 0) {
            categoriesContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных категорий. Нажмите "Добавить категорию".
                </div>
            `;
            renderItems();
            return;
        }

        categoriesContainer.innerHTML = config.categories.map((cat, index) => {
            const safeLabel = escapeHtml(cat.label ?? '');
            const safeColor = normalizeHex(cat.color);
            return `
              <div class="category-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary text-truncate" style="max-width: 120px;" title="${escapeHtml(cat.id)}">${escapeHtml(cat.id)}</span>
                </div>
                <div class="col-auto">
                    <input type="color" class="form-control form-control-color category-color-input"
                           title="Выбрать цвет" value="${safeColor}">
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm category-label-input"
                           placeholder="Название категории" value="${safeLabel}">
                </div>
                <div class="col-auto">
                     <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center category-delete-btn" title="Удалить категорию">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        categoriesContainer.querySelectorAll('.category-color-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.category-item').dataset.index);
                const category = config.categories[idx];
                const oldColor = normalizeHex(category.color);
                const newColor = normalizeHex(this.value);

                category.color = newColor;

                config.items.forEach(item => {
                    if (item.correct === category.id) {
                        item.color = newColor;
                    }
                });

                // Цвет не завязан на текстовый фокус (нативная палитра),
                // но дёргать полную пересборку на каждый шаг слайдера тоже
                // избыточно — debounce сглаживает частые события 'input'.
                updateTextareaWithoutFullRender();
                debouncedFullRender();
            });
        });

        categoriesContainer.querySelectorAll('.category-label-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.category-item').dataset.index);
                config.categories[idx].label = this.value;

                // Синхронизируем JSON сразу, но НЕ пересобираем DOM на каждый символ —
                // иначе фокус слетает с инпута. Полная перерисовка (нужна, чтобы
                // обновить название категории в выпадающем списке у элементов) — с задержкой.
                updateTextareaWithoutFullRender();
                debouncedFullRender();
            });
        });

        categoriesContainer.querySelectorAll('.category-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.category-item').dataset.index);
                const removed = config.categories[idx];
                config.categories.splice(idx, 1);

                if (removed) {
                    // Элементы, привязанные к удалённой категории, теряют связь и цвет.
                    config.items.forEach(item => {
                        if (item.correct === removed.id) {
                            item.correct = '';
                            item.color = '';
                        }
                    });
                }

                updateTextareaAndFullRender();
            });
        });

        renderItems();
    }

    // ============================================================
    // ЭЛЕМЕНТЫ
    // ============================================================

    function renderItems() {
        if (config.items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных элементов. Нажмите "Добавить элемент".
                </div>
            `;
            return;
        }

        itemsContainer.innerHTML = config.items.map((item, index) => {
            const safeLabel = escapeHtml(item.label ?? '');
            const swatchColor = normalizeHex(item.color, '#ffffff');
            return `
              <div class="item-row row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <span class="badge bg-secondary text-truncate" style="max-width: 120px;" title="${escapeHtml(item.id)}">${escapeHtml(item.id)}</span>
                </div>
                <div class="col-auto">
                    <div class="border rounded" title="Цвет наследуется от категории"
                         style="width: 24px; height: 24px; background: ${swatchColor};"></div>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm item-label-input"
                           placeholder="Название элемента" value="${safeLabel}">
                </div>
                <div class="col-auto" style="min-width: 180px;">
                    <select class="form-select form-select-sm item-category-select">
                        ${categoryOptionsHtml(item.correct)}
                    </select>
                </div>
                <div class="col-auto">
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center item-delete-btn" title="Удалить элемент">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        itemsContainer.querySelectorAll('.item-label-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.item-row').dataset.index);
                config.items[idx].label = this.value;
                updateTextareaWithoutFullRender();
            });
        });

        itemsContainer.querySelectorAll('.item-category-select').forEach(select => {
            select.addEventListener('change', function () {
                const idx = Number(this.closest('.item-row').dataset.index);
                const item = config.items[idx];
                const category = findCategory(this.value);

                item.correct = this.value;
                item.color = category ? normalizeHex(category.color) : '';

                updateTextareaAndFullRender();
            });
        });

        itemsContainer.querySelectorAll('.item-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.item-row').dataset.index);
                config.items.splice(idx, 1);
                updateTextareaAndFullRender();
            });
        });
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderCategories);

    const debouncedFullRender = debounce(() => {
        updateTextareaAndFullRender();
    }, 400);

    addCategoryBtn.addEventListener('click', () => {
        config.categories.push({
            id: generateId('cat'),
            color: '#cccccc',
            label: ''
        });
        updateTextareaAndFullRender();
    });

    addItemBtn.addEventListener('click', () => {
        const firstCategory = config.categories[0] || null;
        config.items.push({
            id: generateId('item'),
            color: firstCategory ? normalizeHex(firstCategory.color) : '',
            label: '',
            correct: firstCategory ? firstCategory.id : ''
        });
        updateTextareaAndFullRender();
    });

    renderCategories();
}
