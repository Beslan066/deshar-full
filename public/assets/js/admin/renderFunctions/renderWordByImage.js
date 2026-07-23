import { escapeHtml, bindImageUpload, generateId, shuffle } from "../helpers.js";

export function renderWordByImage(config) {
    console.log('renderWordByImage');
    const editor = document.getElementById('configEditor');

    if (!editor) return;
    if (typeof config.imageUrl !== 'string') {
        config.imageUrl = '';
    }
    if (typeof config.correctAnswer !== 'string') {
        config.correctAnswer = '';
    }
    if (!config.availableLetters || !Array.isArray(config.availableLetters)) {
        config.availableLetters = [];
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
                <!-- Картинка задания -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Изображение</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="border rounded text-center bg-light" id="main-image-preview"
                                     style="height: 70px; width: 70px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    ${config.imageUrl
            ? `<img src="${escapeHtml(config.imageUrl)}" style="max-width:100%; max-height:100%; object-fit: cover;">`
            : `<i class="bx bx-image text-muted font-size-24"></i>`
        }
                                </div>
                                <div class="flex-grow-1">
                                    <div class="input-group input-group-merge input-group-sm mb-2">
                                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                                        <input type="text" id="main-image-url-input" class="form-control"
                                               placeholder="Вставьте путь к изображению или загрузите файл"
                                               value="${escapeHtml(config.imageUrl)}">
                                    </div>
                                    <label class="btn btn-sm btn-outline-secondary btn-icon mb-0" title="Загрузить изображение">
                                        <i class="menu-icon tf-icons ri-upload-line m-0"></i>
                                        <input type="file" id="main-image-file-input" class="d-none" accept="image/*">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <div class="config-field-group">
                        <label class="form-label">Правильный ответ (слово)</label>
                        <input type="text" id="correct-answer-input" class="form-control"
                               placeholder="Например: Дом" value="${escapeHtml(config.correctAnswer)}">
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Доступные буквы</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" onclick="window.addLetter()">
                                <i class="bx bx-plus me-1"></i> Добавить букву
                            </button>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="shuffle-letters-btn">
                                Перемешать
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="letters-container" class="d-flex flex-wrap gap-2">
                                <!-- Сюда JS будет рендерить буквы -->
                            </div>
                            <small class="text-muted d-block mt-2">
                                Буквы
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    editor.innerHTML = html;

    function renderMainImagePreview() {
        const preview = document.getElementById('main-image-preview');
        if (!preview) return;
        preview.innerHTML = config.imageUrl
            ? `<img src="${escapeHtml(config.imageUrl)}" style="max-width:100%; max-height:100%; object-fit: cover;">`
            : `<i class="bx bx-image text-muted font-size-24"></i>`;

        const urlInput = document.getElementById('main-image-url-input');
        if (urlInput && urlInput.value !== config.imageUrl) {
            urlInput.value = config.imageUrl;
        }
    }

    const mainUrlInput = document.getElementById('main-image-url-input');
    if (mainUrlInput) {
        mainUrlInput.addEventListener('input', function () {
            config.imageUrl = this.value.trim();
            renderMainImagePreview();
            updateTextareaWithoutFullRender();
        });
    }

    const mainFileInput = document.getElementById('main-image-file-input');
    const mainUploadBtn = mainFileInput ? mainFileInput.closest('label') : null;
    if (mainFileInput && mainUploadBtn) {
        bindImageUpload(mainFileInput, mainUploadBtn, (url) => {
            config.imageUrl = url;
            updateTextareaAndFullRender();
        });
    }
    const shuffleLettersBtn = document.getElementById('shuffle-letters-btn');
    if (shuffleLettersBtn) {
        shuffleLettersBtn.addEventListener('click', function () {
            config.availableLetters = shuffle(config.availableLetters);
            updateTextareaAndFullRender();
        });
    }

    const correctAnswerInput = document.getElementById('correct-answer-input');
    if (correctAnswerInput) {
        correctAnswerInput.addEventListener('input', function () {
            config.correctAnswer = this.value;
            updateTextareaWithoutFullRender();
        });
    }

    function renderLetters() {
        const container = document.getElementById('letters-container');
        if (!container) return;

        if (config.availableLetters.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light w-100">
                    Нет добавленных букв. Нажмите "Добавить букву".
                </div>
            `;
            return;
        }

        container.innerHTML = config.availableLetters.map((letterObj, index) => {
            const safeLetter = escapeHtml(letterObj.letter || '');
            return `
                <div class="letter-item d-flex align-items-center border rounded p-1 gap-1" data-index="${index}">
                    <input type="text" maxlength="1" class="form-control form-control-sm letter-char-input text-center"
                           style="width: 42px;" value="${safeLetter}">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="window.deleteLetter(${index})">
                        <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                    </button>
                </div>
            `;
        }).join('');

        container.querySelectorAll('.letter-char-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.letter-item').dataset.index);
                const value = this.value.slice(0, 1);
                this.value = value;
                config.availableLetters[idx].letter = value;
                updateTextareaWithoutFullRender();
            });
        });
    }

    window.addLetter = function () {
        config.availableLetters.push({
            id: generateId("letter"),
            letter: ""
        });

        updateTextareaAndFullRender();
    };

    window.deleteLetter = function (index) {
        config.availableLetters.splice(index, 1);
        updateTextareaAndFullRender();
    };

    function updateTextareaAndFullRender() {
        const textarea = document.getElementById('configJsonTextarea');
        if (textarea) {
            textarea.value = JSON.stringify(config, null, 2);
        }
        renderMainImagePreview();
        renderLetters();
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

    renderLetters();
}
