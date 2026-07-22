import { escapeHtml, createTextareaUpdaters } from "../helpers.js";

export function renderWordPicker(config) {
    const editor = document.getElementById('configEditor');

    if (!editor) return;

    if (typeof config.text !== 'string') {
        config.text = '';
    }
    if (!Array.isArray(config.correctValues)) {
        config.correctValues = [];
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
                        <div class="card-header bg-light p-2 border-bottom">
                            <span class="fw-semibold small text-uppercase">Предложение</span>
                        </div>
                        <div class="card-body p-3">
                            <input type="text" id="sentence-input" class="form-control mb-3"
                                   placeholder="Введите предложение" value="${escapeHtml(config.text)}">

                            <div id="words-container" class="d-flex flex-wrap gap-2"></div>
                            <small class="text-muted d-block mt-2">
                                Кликните по слову, чтобы отметить его правильным (клик ещё раз — снять отметку).
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    editor.innerHTML = html;

    const sentenceInput = document.getElementById('sentence-input');
    const wordsContainer = document.getElementById('words-container');

    function renderWords() {
        const tokens = config.text.split(/\s+/).filter(Boolean);

        if (tokens.length === 0) {
            wordsContainer.innerHTML = `
                <div class="text-muted small">Введите предложение выше, чтобы выбрать слова.</div>
            `;
            return;
        }

        wordsContainer.innerHTML = tokens.map((word, index) => {
            const isSelected = config.correctValues.includes(word);
            const safeWord = escapeHtml(word);
            return `
                <button type="button"
                        class="btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-secondary'} word-token-btn"
                        data-word-index="${index}">
                    ${safeWord}
                </button>
            `;
        }).join('');

        wordsContainer.querySelectorAll('.word-token-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = Number(this.dataset.wordIndex);
                const word = tokens[idx];

                if (config.correctValues.includes(word)) {
                    config.correctValues = config.correctValues.filter(w => w !== word);
                } else {
                    config.correctValues.push(word);
                }

                updateTextareaAndFullRender();
            });
        });
    }

    const { updateTextareaAndFullRender, updateTextareaWithoutFullRender } =
        createTextareaUpdaters(config, renderWords);


    if (sentenceInput) {
        sentenceInput.addEventListener('input', function () {
            config.text = this.value;
            updateTextareaWithoutFullRender();
        });
    }

    renderWords();
}
