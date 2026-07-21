// ============================================================
// tasks-render.js - Полный скрипт для создания заданий

import { renderDropWordToImage } from "./renderFunctions/renderDropWordToImage.js";
import { renderSingleSelectImageQuiz } from "./renderFunctions/renderSingleSelectImageQuiz.js";
import { renderWordByImage } from "./renderFunctions/renderWordByImage.js";

// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // ПРЕДПРОСМОТР МЕДИАФАЙЛОВ
    // ============================================================

    // Аудио
    document.getElementById('audio_file')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            const preview = document.getElementById('audio-preview');
            if (preview) {
                preview.querySelector('audio source').src = url;
                preview.querySelector('audio').load();
                preview.classList.remove('d-none');
            }
        }
    });

    // Изображение
    document.getElementById('image_file')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.querySelector('img').src = url;
                preview.classList.remove('d-none');
            }
        }
    });

    // Видео
    document.getElementById('video_file')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            const preview = document.getElementById('video-preview');
            if (preview) {
                preview.querySelector('video source').src = url;
                preview.querySelector('video').load();
                preview.classList.remove('d-none');
            }
        }
    });

    // ============================================================
    // УДАЛЕНИЕ МЕДИАФАЙЛОВ
    // ============================================================
    window.removeFile = function(type) {
        const preview = document.getElementById(type + '-preview');
        const input = document.getElementById(type + '_file');
        if (preview) {
            preview.classList.add('d-none');
            if (type === 'audio' && preview.querySelector('audio source')) {
                preview.querySelector('audio source').src = '';
            } else if (type === 'image' && preview.querySelector('img')) {
                preview.querySelector('img').src = '';
            } else if (type === 'video' && preview.querySelector('video source')) {
                preview.querySelector('video source').src = '';
            }
        }
        if (input) {
            input.value = '';
        }
    };

    // ============================================================
    // ПОДСКАЗКИ
    // ============================================================
    document.getElementById('add-hint')?.addEventListener('click', function() {
        const container = document.getElementById('hints-container');
        if (!container) return;
        const count = container.querySelectorAll('.hint-group').length + 1;
        const div = document.createElement('div');
        div.className = 'input-group mb-2 hint-group';
        div.innerHTML = `
            <input type="text" class="form-control" name="hints[]" placeholder="Подсказка ${count}">
            <button type="button" class="btn btn-outline-danger remove-hint">
                <i class="bx bx-x"></i>
            </button>
        `;
        container.appendChild(div);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-hint')) {
            const group = e.target.closest('.hint-group');
            if (document.querySelectorAll('.hint-group').length > 1) {
                group.remove();
            }
        }
    });

    // ============================================================
    // ДИНАМИЧЕСКИЙ КОНФИГ С ВИЗУАЛЬНЫМ РЕДАКТОРОМ
    // ============================================================
    document.getElementById('task_type_id')?.addEventListener('change', function() {
        const typeId = this.value;
        const container = document.getElementById('config-container');
        const typeName = this.selectedOptions[0]?.text || '';

        if (!typeId) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Выберите тип задания, чтобы настроить его конфигурацию
                </div>
            `;
            return;
        }

        fetch(`/admin/tasks/default-config?task_type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }

                const config = data.config || {};
                renderConfigByType(typeId, typeName, config, container);
            })
            .catch(error => {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bx bx-error me-1"></i>
                        Ошибка загрузки конфига
                    </div>
                `;
                console.error('Error:', error);
            });
    });

    // ============================================================
    // РЕНДЕРИНГ КОНФИГА ПО ТИПУ
    // ============================================================
   function renderConfigByType(typeId, typeName, config, container) {
    console.log('redndsadsad');
    let html = `
        <div class="config-card card" id="configCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Конфигурация задания</h6>
                    <small class="text-muted">Заполните данные для типа "${typeName}"</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-variant="json-config" onclick="window.toggleViewVariant('json-config')">
                    <i class="bx bx-code-alt"></i> JSON Конфиг
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-variant="fields" onclick="window.toggleViewVariant('fields')">
                    <i class="bx bx-code-alt"></i> Поля
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.toggleJsonPreview()">
                    <i class="bx bx-code-alt"></i> Показать JSON
                </button>
            </div>
            <div class="card-body">
                <div class="json-preview" id="jsonPreview"></div>
                <!-- Общий контейнер, в который функции ниже будут добавлять свой UI -->
                <div id="configEditor"></div>
            </div>
        </div>
    `;

    container.innerHTML = html;

    const slug = getTypeSlug(typeId);

    switch(slug) {
        case 'single_select_image_quiz':
            renderSingleSelectImageQuiz(config);
            break;
        case 'drop_word_to_image':
            renderDropWordToImage(config);
            break;
        case 'word_by_image':
            renderWordByImage(config)
            break;
        default:
            renderGeneric(config);
    }

    window.updateJsonPreview();
}
    // ============================================================
    // ПОЛУЧЕНИЕ SLUG ПО ID
    // ============================================================
    function getTypeSlug(typeId) {
    const select = document.getElementById('task_type_id');
    if (!select) return '';

    const option = Array.from(select.options).find(opt => opt.value == typeId);

    return option ? option.dataset.slug : '';
}

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================================
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    window.toggleJsonPreview = function() {
        const preview = document.getElementById('jsonPreview');
        if (preview) {
            preview.classList.toggle('active');
            window.updateJsonPreview();
        }
    };

    window.updateJsonPreview = function() {
        const preview = document.getElementById('jsonPreview');
        if (!preview) return;

        try {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const config = {};
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('config[')) {
                    const match = key.match(/config\[([^\]]+)\]/);
                    if (match) {
                        // Пытаемся распарсить JSON значение
                        try {
                            config[match[1]] = JSON.parse(value);
                        } catch (e) {
                            config[match[1]] = value;
                        }
                    }
                }
            }
            preview.textContent = JSON.stringify(config, null, 2);
        } catch (e) {
            // Игнорируем ошибки
        }
    };
window.toggleViewVariant = function(variant) {
    // 1. Скрываем все блоки режимов отображения
    document.querySelectorAll('.view-variant-block').forEach(block => {
        block.classList.add('d-none');
    });

    // 2. Показываем только выбранный блок
    const activeBlock = document.getElementById(`view-${variant}`);
    if (activeBlock) {
        activeBlock.classList.remove('d-none');
    }

    // 3. Переключаем класс active у кнопок для красоты
    document.querySelectorAll('.view-variant-btn').forEach(btn => {
        if (btn.getAttribute('data-variant') === variant) {
            btn.classList.add('active', 'btn-secondary');
            btn.classList.remove('btn-outline-secondary');
        } else {
            btn.classList.remove('active', 'btn-secondary');
            btn.classList.add('btn-outline-secondary');
        }
    });
};
    // ============================================================
    // УНИВЕРСАЛЬНЫЙ РЕНДЕРИНГ
    // ============================================================
    function renderGeneric(config) {
        const editor = document.getElementById('configEditor');
        if (!editor) return;

        let html = `
            <div class="config-field-group">
                <label>Конфигурация</label>
                <textarea class="form-control" name="config" rows="10"
                          style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(config, null, 2))}</textarea>
                <small class="text-muted">Введите конфигурацию в формате JSON</small>
            </div>
        `;
        editor.innerHTML = html;
    }
    // ============================================================
    // АВТООБНОВЛЕНИЕ JSON ПРИ ИЗМЕНЕНИИ ПОЛЕЙ
    // ============================================================
    document.addEventListener('change', function(e) {
        if (e.target.closest('.config-field-group') || e.target.closest('.config-card')) {
            window.updateJsonPreview();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.closest('.config-field-group') || e.target.closest('.config-card')) {
            window.updateJsonPreview();
        }
    });
});

// ============================================================
// ОБРАБОТКА ОТПРАВКИ ФОРМЫ
// ============================================================
document.querySelector('form')?.addEventListener('submit', function(e) {
    const configField = document.querySelector('input[name="config"]');
    if (configField) {
        // Собираем все поля config из формы
        const configData = {};
        const configInputs = this.querySelectorAll('[name^="config["]');
        configInputs.forEach(input => {
            const name = input.name.replace('config[', '').replace(']', '');
            if (input.type === 'checkbox') {
                configData[name] = input.checked ? true : false;
            } else if (input.type === 'number') {
                configData[name] = input.value !== '' ? Number(input.value) : null;
            } else {
                // Пытаемся распарсить JSON для текстовых полей
                const value = input.value;
                if (value && (value.trim().startsWith('[') || value.trim().startsWith('{'))) {
                    try {
                        configData[name] = JSON.parse(value);
                    } catch (e) {
                        configData[name] = value;
                    }
                } else {
                    configData[name] = value;
                }
            }
        });
        configField.value = JSON.stringify(configData);
    }
});
