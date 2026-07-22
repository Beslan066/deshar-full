import { escapeHtml,bindImageUpload, generateId } from "../helpers.js";

export function renderSingleSelectImageQuiz(config) {
    console.log('renderSingleSelectImageQuiz');
    const editor = document.getElementById('configEditor');

    if (!editor) return;
    if (!config.variants || !Array.isArray(config.variants)) {
        config.variants = [];
    }
    if (typeof config.shuffle_variants !== 'boolean') {
        config.shuffle_variants = true;
    }
    if (typeof config.correct_variant_id === 'undefined') {
        config.correct_variant_id = null;
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
                <!-- Конструктор вариантов -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Варианты ответов</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" onclick="window.addQuizVariant()">
                                <i class="bx bx-plus me-1"></i> Добавить вариант
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="variants-container" class="d-flex flex-column gap-3">
                                <!-- Сюда JS будет рендерить варианты -->
                            </div>
                            <small class="text-muted d-block mt-2">
                                Отметьте галочкой вариант, который является правильным ответом.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    // ============================================================
    // ГЕНЕРАЦИЯ СТРОКОВОГО ID ВАРИАНТА
    // ============================================================


    function slugifyImagePath(imageUrl) {
        if (!imageUrl) return '';
        const fileName = imageUrl.split('/').pop().split('?')[0];
        const withoutExt = fileName.replace(/\.[^.]+$/, '');
        return withoutExt
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }


    // ============================================================
    // ЛОГИКА КОНСТРУКТОРА ВАРИАНТОВ
    // ============================================================

    function renderVariants() {
        const container = document.getElementById('variants-container');
        if (!container) return;

        if (config.variants.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных вариантов. Нажмите "Добавить вариант".
                </div>
            `;
            return;
        }

        container.innerHTML = config.variants.map((variant, index) => {
            const hasImage = variant.imageUrl && variant.imageUrl.trim() !== '';
            const safeUrl = escapeHtml(variant.imageUrl || '');
            const safeId = escapeHtml(String(variant.id));
            const isCorrect = config.correct_variant_id === variant.id;
            return `
                <div class="variant-item row g-2 align-items-center border-bottom pb-3" data-index="${index}">
                    <!-- Отметка "правильный вариант" -->
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

                    <!-- Инпут для вставки/просмотра ссылки -->
                    <div class="col">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-link"></i></span>
                            <input type="text" class="form-control form-control-sm variant-url-input"
                                   placeholder="Вставьте путь к изображению или загрузите файл"
                                   value="${safeUrl}">
                        </div>
                    </div>

                    <!-- Превью конкретного варианта -->
                    <div class="col-auto" style="width: 50px;">
                        <div class="border rounded text-center bg-light variant-preview" style="height: 38px; width: 45px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            ${hasImage
                                ? `<img src="${safeUrl}" style="max-width:100%; max-height:100%; object-fit: cover;">`
                                : `<i class="bx bx-image text-muted font-size-18"></i>`
                            }
                        </div>
                    </div>

                   <!-- Загрузка файла для этого варианта -->
                   <div class="col-auto">
                   <label class="btn btn-sm btn-outline-secondary btn-icon mb-0 variant-upload-btn text-center" title="Загрузить изображение">
                    <i class="menu-icon tf-icons ri-upload-line m-0"></i>
                     <input type="file" class="d-none variant-file-input" accept="image/*">
                     </label>
                      </div>
                       <!-- Удаление варианта -->
                        <div class="col-auto">
                         <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center" onclick="window.deleteQuizVariant(${index})">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                            </div>
                </div>
            `;
        }).join('');

        container.querySelectorAll('.variant-correct-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const idx = Number(this.closest('.variant-item').dataset.index);

                if (this.checked) {
                    config.correct_variant_id = config.variants[idx].id;
                } else {
                    config.correct_variant_id = null;
                }

                container.querySelectorAll('.variant-correct-checkbox').forEach(other => {
                    if (other !== this) other.checked = false;
                });

                updateTextareaWithoutFullRender();
            });
        });

        container.querySelectorAll('.variant-url-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = this.closest('.variant-item').dataset.index;
                config.variants[idx].imageUrl = this.value.trim();
                updateTextareaWithoutFullRender();
            });
        });

       container.querySelectorAll('.variant-item').forEach((variantItem) => {
    const idx = Number(variantItem.dataset.index);
    const fileInput = variantItem.querySelector('.variant-file-input');
    const uploadBtn = variantItem.querySelector('.variant-upload-btn');

    bindImageUpload(fileInput, uploadBtn, (url) => {
        const oldId = config.variants[idx].id;
        const wasCorrect = config.correct_variant_id === oldId;
        const newId = generateId("variant");

        config.variants[idx].imageUrl = url;
        config.variants[idx].id = newId;

        if (wasCorrect) {
            config.correct_variant_id = newId;
        }

        updateTextareaAndFullRender();
    });
});
    }

    window.addQuizVariant = function () {
        config.variants.push({
            id: generateId("variant"),
            imageUrl: ""
        });

        updateTextareaAndFullRender();
    };

    window.deleteQuizVariant = function (index) {
        const removedId = config.variants[index]?.id;
        config.variants.splice(index, 1);

        if (config.correct_variant_id === removedId) {
            config.correct_variant_id = null;
        }

        updateTextareaAndFullRender();
    };

    function updateTextareaAndFullRender() {
        const textarea = document.getElementById('configJsonTextarea');
        if (textarea) {
            textarea.value = JSON.stringify(config, null, 2);
        }
        renderVariants();
        if (typeof window.updateJsonPreview === 'function') {
            window.updateJsonPreview();
        }
    }

    function updateTextareaWithoutFullRender() {
        const textarea = document.getElementById('configJsonTextarea');
        if (textarea) {
            textarea.value = JSON.stringify(config, null, 2);
        }
        if (typeof window.updateJsonPreview === 'function') {
            window.updateJsonPreview();
        }
    }
    renderVariants();
}
