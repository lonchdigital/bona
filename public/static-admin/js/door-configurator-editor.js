(() => {
    'use strict';
    const root = document.querySelector('[data-configurator-editor]');
    if (!root) return;
    const config = JSON.parse(document.getElementById('cfg-editor-data').textContent);
    const draft = config.draft;
    const form = document.getElementById('cfg-edit-form');
    const fields = document.getElementById('cfg-fields');
    const save = document.getElementById('cfg-save');
    const publish = document.getElementById('cfg-publish');
    const errorBox = document.getElementById('cfg-client-error');
    const status = document.getElementById('cfg-save-state');
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let dirty = false, uploading = 0;
    function markDirty() {
        dirty = true;
        publish.disabled = true;
        status.textContent = uploading ? 'Завантажуємо фото…' : 'Є незбережені зміни. Спочатку збережіть чернетку.';
        save.disabled = uploading > 0;
    }
    function error(message) { errorBox.textContent = message; errorBox.hidden = !message; }
    function variants() { return config.kind === 'door' ? draft.colors : [draft]; }
    function colorOptions(value) {
        const colors = config.colors.map(c => `<option value="${c.id}" ${Number(value) === c.id ? 'selected' : ''}>${esc(c.name)} · #${c.id}</option>`);
        if (value != null && !config.colors.some(c => c.id === Number(value))) colors.unshift(`<option value="${Number(value)}" selected>Колір #${Number(value)} видалено з товару</option>`);
        return `<option value="" ${value == null ? 'selected' : ''}>Без кольору (лише для товарів без кольорів)</option>${colors.join('')}`;
    }
    function photo(variant, index, key, label) {
        return `<label>${label}${variant[key] ? `<img src="${esc(variant[key])}" alt="${label}" loading="lazy">` : '<p class="cfg-note">Фото ще не додано</p>'}<input type="file" accept="image/png,image/jpeg,image/webp" data-upload="${key}" data-index="${index}"><small>PNG, WebP, JPEG · до 8 МБ · до 4096 px</small></label>`;
    }
    function input(label, key, value, type = 'text', extra = '') {
        return `<label>${label}<input class="form-control" data-field="${key}" type="${type}" value="${esc(value)}" ${extra}></label>`;
    }
    function coordinates(variant, property, fallback, labels) {
        const values = variant[property] || fallback;
        return `<div class="cfg-coordinates">${values.map((value, i) => input(labels[i], `${property}.${i}`, value, 'number', `step="any" min="0" max="${property === 'handle' ? 1 : 10000}" required`)).join('')}</div>`;
    }
    function render() {
        const opened = new Set([...fields.querySelectorAll('.cfg-variant[open]')].map(el => el.dataset.id));
        let html = '';
        if (config.kind === 'door') {
            html += `<div class="cfg-inputs" data-base><label>Категорія<select class="form-control" data-base-field="category"><option value="interior" ${draft.category === 'interior' ? 'selected' : ''}>Міжкімнатні</option><option value="exterior" ${draft.category === 'exterior' ? 'selected' : ''}>Вхідні</option></select></label>${input('Коротка назва · UA', 'short.uk', draft.short?.uk, 'text', 'maxlength="160"')}${input('Коротка назва · RU', 'short.ru', draft.short?.ru, 'text', 'maxlength="160"')}<div><span>Додаткові розділи</span>${[['hidden', 'Приховані'], ['mirror', 'Дзеркальні'], ['classic', 'Класичні']].map(([id, name]) => `<label class="cfg-check"><input type="checkbox" data-type="${id}" ${(draft.types || []).includes(id) ? 'checked' : ''}>${name}</label>`).join('')}</div></div>`;
        }
        html += variants().map((variant, index) => {
            const isDoor = config.kind === 'door';
            const id = variant.id || 'handle';
            const title = isDoor ? `${index + 1}. ${variant.name?.uk || config.colors.find(c => c.id === Number(variant.colorId))?.name || 'Новий відтінок'}` : 'Зображення та вигляд ручки';
            const geometry = isDoor ? `<h3 class="h6">Кадрування полотна</h3><p class="cfg-note">Координати у сітці 800 × 837. Фото будь-якого розміру масштабується до цієї сітки.</p>${coordinates(variant, 'crop', draft.crop || [0, 0, 800, 837], ['X', 'Y', 'Ширина', 'Висота'])}
                ${draft.category === 'interior' ? `<h3 class="h6 mt-4">Положення ручки</h3><p class="cfg-note">Клацніть на полотні справа або введіть частку ширини / висоти від 0 до 1.</p>${coordinates(variant, 'handle', draft.handle || [.15, .55], ['X ручки', 'Y ручки'])}<label class="mt-3">Сторона<select class="form-control" data-field="handleSide"><option value="left" ${(variant.handleSide || draft.handleSide) !== 'right' ? 'selected' : ''}>Зліва</option><option value="right" ${(variant.handleSide || draft.handleSide) === 'right' ? 'selected' : ''}>Справа</option></select></label>` : '<p class="cfg-note mt-3">На вхідних дверях зберігається вбудована фурнітура з фотографії.</p>'}` : '';
            return `<details class="cfg-variant" data-index="${index}" data-id="${esc(id)}" ${opened.has(id) || (!opened.size && index === 0) ? 'open' : ''}><summary>${esc(title)}${isDoor && variant.enabled === false ? ' · вимкнено' : ''}</summary><div class="cfg-variant-body"><div>
                ${isDoor ? `<label class="cfg-check"><input type="checkbox" data-field="enabled" ${variant.enabled !== false ? 'checked' : ''}>Включити відтінок у наступну публікацію</label>` : ''}
                <div class="cfg-inputs"><label>Колір із товару<select class="form-control" data-field="colorId">${colorOptions(variant.colorId)}</select></label>
                ${isDoor ? input('Колір кружечка', 'hex', variant.hex || '#eeeeee', 'color') : `${input('Основний колір металу', 'color', draft.color, 'color')}${input('Відблиск металу', 'accent', draft.accent, 'color')}<label>Форма розетки<select class="form-control" data-field="shape"><option value="square" ${draft.shape === 'square' ? 'selected' : ''}>Квадратна</option><option value="round" ${draft.shape === 'round' ? 'selected' : ''}>Кругла</option></select></label>`}
                ${isDoor && config.options.length ? `<label class="cfg-full">Комплектація (скло, молдинг, інші опції)<select class="form-control" data-field="optionIds" multiple size="4">${config.options.map(o => `<option value="${o.id}" ${(variant.optionIds || []).includes(o.id) ? 'selected' : ''}>${esc(o.label)}</option>`).join('')}</select><small>Не більше одного значення кожного атрибута. Ctrl / ⌘ — вибрати кілька.</small></label>` : ''}</div>
                <div class="cfg-photos">${photo(variant, index, 'image', 'Фото картки')}${isDoor && draft.category === 'interior' ? photo(variant, index, 'preview', 'Фото полотна без ручки') : ''}</div>
                ${geometry}${isDoor ? `<div class="cfg-order"><button class="btn btn-sm btn-outline-dark" type="button" data-move="-1" ${index === 0 ? 'disabled' : ''}>Вище</button><button class="btn btn-sm btn-outline-dark" type="button" data-move="1" ${index === variants().length - 1 ? 'disabled' : ''}>Нижче</button></div>` : '<p class="cfg-note">Фото використовується в списку ручок. У кімнаті ручка відображається схематично за вибраною формою та кольорами.</p>'}
                </div>${isDoor ? `<div><h3 class="h6">Полотно й точка ручки</h3><canvas class="cfg-stage" data-canvas="${index}" width="320" height="640" aria-label="Попередній перегляд полотна та точки ручки"></canvas><p class="cfg-note">Точка позначає вісь розетки. Повну сцену перевірте через «Примірка» після збереження.</p>${draft.face ? '<p class="cfg-note">Для цього фото застосовано підготовлене вирівнювання перспективи. Після заміни фото перевірте його в розширених параметрах нижче.</p>' : ''}</div>` : ''}</div></details>`;
        }).join('');
        if (config.kind === 'door' && draft.face) {
            html += `<details class="cfg-section" data-base><summary>Вирівнювання перспективи моделі</summary><p>Чотири точки полотна у сітці 800 × 837: верхня ліва, верхня права, нижня права, нижня ліва. Для звичайного фронтального фото вимкніть вирівнювання.</p><div class="cfg-coordinates">${draft.face.map((v, i) => input(`Точка ${Math.floor(i / 2) + 1} · ${i % 2 ? 'Y' : 'X'}`, `face.${i}`, v, 'number', 'min="0" max="837" step="any" required')).join('')}</div><button type="button" class="btn btn-outline-dark mt-3" id="cfg-remove-face">Використовувати звичайне кадрування</button></details>`;
        }
        fields.innerHTML = html;
        variants().forEach((_, i) => draw(i));
    }
    function set(object, path, value) {
        const keys = path.split('.');
        let at = object;
        keys.slice(0, -1).forEach(key => { if (!at[key]) at[key] = {}; at = at[key]; });
        at[keys.at(-1)] = value;
    }
    function draw(index) {
        const canvas = fields.querySelector(`[data-canvas="${index}"]`);
        if (!canvas) return;
        const v = variants()[index], crop = v.crop || draft.crop;
        if (!crop || crop[2] <= 0 || crop[3] <= 0) return;
        const source = draft.category === 'interior' ? v.preview : v.image;
        const ctx = canvas.getContext('2d');
        canvas.width = 320; canvas.height = Math.min(1600, Math.max(100, 320 * crop[3] / crop[2]));
        if (!source) { ctx.fillStyle = '#555f6b'; ctx.font = '16px sans-serif'; ctx.fillText('Додайте підготовлене фото', 16, 40); return; }
        const token = `${source}:${JSON.stringify(crop)}:${JSON.stringify(v.handle)}:${JSON.stringify(draft.face)}`;
        canvas.dataset.renderToken = token;
        const image = new Image();
        image.onload = () => {
            if (!canvas.isConnected || canvas.dataset.renderToken !== token) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (draft.face) {
                const [lx, lt, rx, rt, , rb, , lb] = draft.face;
                for (let x = 0; x < 320; x++) {
                    const t = x / 320, top = lt + (rt - lt) * t, bottom = lb + (rb - lb) * t;
                    ctx.drawImage(image, (lx + (rx - lx) * t) / 800 * image.width, top / 837 * image.height, (rx - lx) / 320 / 800 * image.width, (bottom - top) / 837 * image.height, x, 0, 1.5, canvas.height);
                }
            } else ctx.drawImage(image, crop[0] / 800 * image.width, crop[1] / 837 * image.height, crop[2] / 800 * image.width, crop[3] / 837 * image.height, 0, 0, canvas.width, canvas.height);
            if (draft.category === 'interior') {
                const point = v.handle || draft.handle || [.15, .55];
                ctx.fillStyle = '#b21f36'; ctx.strokeStyle = '#fff'; ctx.lineWidth = 3;
                ctx.beginPath(); ctx.arc(point[0] * canvas.width, point[1] * canvas.height, 7, 0, Math.PI * 2); ctx.fill(); ctx.stroke();
            }
        };
        image.onerror = () => { if (canvas.isConnected) error('Не вдалося відкрити фото. Перевірте файл або завантажте його повторно.'); };
        image.src = source;
    }
    fields.addEventListener('input', event => {
        const el = event.target;
        if (el.type === 'file') return;
        const section = el.closest('.cfg-variant');
        const variant = section ? variants()[Number(section.dataset.index)] : draft;
        const path = el.dataset.field || el.dataset.baseField;
        if (el.dataset.type) draft.types = [...fields.querySelectorAll('[data-type]:checked')].map(c => c.dataset.type);
        else if (path) {
            if (path.startsWith('crop.') && !variant.crop) variant.crop = [...draft.crop];
            if (path.startsWith('handle.') && !variant.handle) variant.handle = [...(draft.handle || [.15, .55])];
            const value = el.type === 'checkbox' ? el.checked : path === 'optionIds' ? [...el.selectedOptions].map(o => Number(o.value)) : path === 'colorId' ? (el.value ? Number(el.value) : null) : el.type === 'number' ? Number(el.value) : el.value;
            set(variant, path, value);
            if (path === 'colorId' && config.kind === 'door') variant.name = config.colors.find(c => c.id === value)?.names || { uk: 'Без кольору', ru: 'Без цвета' };
        }
        markDirty();
        if (section) draw(Number(section.dataset.index));
        if (path === 'category' || path === 'colorId') render();
    });
    fields.addEventListener('change', async event => {
        const el = event.target;
        if (!el.dataset.upload || !el.files[0]) return;
        const file = el.files[0];
        if (file.size > 8 * 1024 * 1024) { error('Фото завелике. Максимальний розмір — 8 МБ.'); el.value = ''; return; }
        const body = new FormData(); body.append('image', file);
        const variant = variants()[Number(el.dataset.index)], key = el.dataset.upload;
        uploading++; markDirty(); error(''); el.disabled = true;
        try {
            const response = await fetch(config.upload, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf }, body });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Не вдалося завантажити фото.');
            variant[key] = result.url;
        } catch (e) { error(e.message); }
        finally { uploading--; markDirty(); render(); }
    });
    fields.addEventListener('click', event => {
        const move = event.target.closest('[data-move]');
        if (move && !uploading) {
            const index = Number(move.closest('[data-index]').dataset.index), next = index + Number(move.dataset.move);
            [draft.colors[index], draft.colors[next]] = [draft.colors[next], draft.colors[index]];
            markDirty(); render();
        }
        if (event.target.id === 'cfg-remove-face') { delete draft.face; markDirty(); render(); }
        const canvas = event.target.closest('[data-canvas]');
        if (canvas && draft.category === 'interior') {
            const rect = canvas.getBoundingClientRect(), scale = Math.min(rect.width / canvas.width, rect.height / canvas.height);
            const width = canvas.width * scale, height = canvas.height * scale;
            const x = (event.clientX - rect.left - (rect.width - width) / 2) / width;
            const y = (event.clientY - rect.top - (rect.height - height) / 2) / height;
            if (x >= 0 && x <= 1 && y >= 0 && y <= 1) { variants()[Number(canvas.dataset.canvas)].handle = [Number(x.toFixed(4)), Number(y.toFixed(4))]; markDirty(); render(); }
        }
    });
    document.getElementById('cfg-add-color')?.addEventListener('click', () => {
        if (uploading) return;
        const color = config.colors.find(c => !draft.colors.some(v => Number(v.colorId) === c.id)) || config.colors[0];
        draft.colors.push({ id: `finish-${crypto.randomUUID()}`, colorId: color?.id || null, name: color?.names || { uk: 'Без кольору', ru: 'Без цвета' }, hex: '#eeeeee', enabled: false });
        markDirty(); render();
        const last = fields.querySelector('.cfg-variant:last-of-type');
        if (last) { last.open = true; last.scrollIntoView({ behavior: 'auto', block: 'start' }); }
    });
    form.querySelector('[name="sort_order"]').addEventListener('input', markDirty);
    form.addEventListener('submit', event => {
        if (uploading) { event.preventDefault(); return; }
        document.getElementById('cfg-payload').value = JSON.stringify(draft);
        dirty = false;
        save.disabled = true;
        status.textContent = 'Зберігаємо чернетку…';
    });
    window.addEventListener('beforeunload', event => { if (dirty || uploading) { event.preventDefault(); event.returnValue = ''; } });
    render(); save.disabled = false;
    if (config.unsaved) markDirty();
    else status.textContent = 'Усі зміни збережені.';
})();
