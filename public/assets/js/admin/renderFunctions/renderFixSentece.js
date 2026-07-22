import { escapeHtml, createTextareaUpdaters } from "../helpers.js";

export function renderFixSentence(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (!Array.isArray(config.words)) {
        config.words = [];
    }
    if (typeof config.sentence !== 'string') {
        config.sentence = '';
    }
    if (typeof config.correctAnswer !== 'string') {
        config.correctAnswer = '';
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
                <!-- Предложение -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Предложение</span>
                        </div>
                        <div class="card-body p-3">
                            <label class="form-label small">
                                Используйте <code>{{1}}</code> как место пропуска
                            </label>
                            <input type="text" id="sentence-input" class="form-control"
                                   placeholder="Например: {{1}} дает молоко."
                                   value="${escapeHtml(config.sentence)}">
                            <div id="sentence-preview" class="text-muted small mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Конструктор слов -->
                <div class="col-12 mb-3">
                    <div class="card border shadow-none">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Варианты слов</span>
                            <button type="button" class="btn btn-xs btn-primary py-1 px-2" id="add-word-btn">
                                <i class="bx bx-plus me-1"></i> Добавить слово
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div id="words-container" class="d-flex flex-column gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Отметьте радио-кнопкой слово, которое является правильным ответом.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const wordsContainer = document.getElementById('words-container');
    const addWordBtn = document.getElementById('add-word-btn');
    const sentenceInput = document.getElementById('sentence-input');
    const sentencePreview = document.getElementById('sentence-preview');

    function renderSentencePreview() {
        if (!sentencePreview) return;
        const filled = config.correctAnswer
            ? config.sentence.replace('{{1}}', config.correctAnswer)
            : config.sentence;
        sentencePreview.textContent = filled
            ? `Предпросмотр: ${filled}`
            : 'Предпросмотр появится после заполнения предложения.';
    }

    function renderVariants() {
        if (config.words.length === 0) {
            wordsContainer.innerHTML = `
                <div class="text-muted text-center py-4 small border border-dashed rounded bg-light">
                    Нет добавленных слов. Нажмите "Добавить слово".
                </div>
            `;
            renderSentencePreview();
            return;
        }

        wordsContainer.innerHTML = config.words.map((word, index) => {
            const isCorrect = config.correctAnswer === word;
            const safeWord = escapeHtml(word);
            return `
              <div class="word-item row g-2 align-items-center border-bottom pb-2" data-index="${index}">
                <div class="col-auto">
                    <div class="form-check d-flex align-items-center justify-content-center" style="min-height: 38px;">
                        <input type="radio" name="correct-word" class="form-check-input word-correct-radio mt-0"
                               style="cursor: pointer;"
                               title="Отметить как правильный ответ"
                               ${isCorrect ? 'checked' : ''}>
                    </div>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm word-text-input" value="${safeWord}">
                </div>
                <div class="col-auto">
                     <button type="button" class="btn btn-sm btn-icon btn-outline-danger text-center word-delete-btn" title="Удалить слово">
                          <i class="menu-icon tf-icons ri-delete-bin-line m-0"></i>
                           </button>
                </div>
              </div>
            `;
        }).join('');

        wordsContainer.querySelectorAll('.word-correct-radio').forEach(radio => {
            radio.addEventListener('change', function () {
                const idx = Number(this.closest('.word-item').dataset.index);
                config.correctAnswer = config.words[idx];
                updateTextareaWithoutFullRender();
                renderSentencePreview();
            });
        });

        wordsContainer.querySelectorAll('.word-text-input').forEach(input => {
            input.addEventListener('input', function () {
                const idx = Number(this.closest('.word-item').dataset.index);
                const oldValue = config.words[idx];
                const wasCorrect = config.correctAnswer === oldValue;

                config.words[idx] = this.value;

                if (wasCorrect) {
                    config.correctAnswer = this.value;
                }

                updateTextareaWithoutFullRender();
                renderSentencePreview();
            });
        });

        wordsContainer.querySelectorAll('.word-delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.closest('.word-item').dataset.index);
                const removed = config.words[idx];
                config.words.splice(idx, 1);

                if (config.correctAnswer === removed) {
                    config.correctAnswer = '';
                }

                updateTextareaAndFullRender();
            });
        });

        renderSentencePreview();
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderVariants);

    addWordBtn.addEventListener('click', () => {
        config.words.push('');
        updateTextareaAndFullRender();
    });

    if (sentenceInput) {
        sentenceInput.addEventListener('input', function () {
            config.sentence = this.value;
            updateTextareaWithoutFullRender();
            renderSentencePreview();
        });
    }

    renderVariants();
}
