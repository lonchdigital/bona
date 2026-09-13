import { handleBasket } from '../common/cart';

export default function initDoorConfigurator() {
const root = document.querySelector('[data-door-studio]');
if (!root) return;
const config = JSON.parse(document.getElementById('door-studio-data').textContent);
const PRODUCTS = config.products, HANDLES = config.handles;
if (!PRODUCTS.length) return;
const t = text => config.ui[text] || text;
const esc = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character]));
const ASSETS = config.assets;
const ROOMS = [
  { id: 'living', name: t("Вітальня"), image: 'room-living-v5.webp', category: 'interior', floor: 728, wall: 702, base: [184, 174, 163], door: [644, 205, 523] },
  { id: 'bedroom', name: t("Спальня"), image: 'room-bedroom-v2.webp', category: 'interior', floor: 756, wall: 726, base: [170, 169, 165], door: [953, 169, 587] },
  { id: 'hall', name: t("Передпокій"), image: 'room-hall-v2.webp', category: 'interior', floor: 828, wall: 799, base: [160, 160, 156], door: [944, 252, 576] },
  { id: 'entry', name: t("Вхідні"), image: 'room-entry-v2.webp', category: 'exterior', floor: 809, wall: 736, base: [181, 184, 186], door: [955, 280, 529] },
];
// Clean imagegen-edited copies are only for the scene/detail/export. Keep catalog photos intact.
const HANDLE_FREE_IMAGES = {
  'new-york-ivory.jpg': 'new-york-ivory-no-handle.webp',
  'new-york-white.jpg': 'new-york-white-no-handle.webp',
  'new-york-anthracite.jpg': 'new-york-anthracite-no-handle.webp',
  'ostin.webp': 'ostin-no-handle.webp',
  'molding.webp': 'molding-no-handle.webp',
  'glasso.webp': 'glasso-no-handle.webp',
  'hidden-primed.webp': 'hidden-primed-no-handle.webp',
  'hidden-black-edge.webp': 'hidden-black-edge-no-handle.webp',
  'mirror-silver.webp': 'mirror-silver-no-handle.webp',
  'mirror-bronze.webp': 'mirror-bronze-no-handle.webp',
  'classic-milan.webp': 'classic-milan-no-handle.webp',
  'classic-nice.webp': 'classic-nice-no-handle.webp',
};
function previewDoorImage(p, f) {
  // Never fall back to a photo with baked-in hardware for an interchangeable interior handle.
  if (p.category === 'interior') {
    const image = HANDLE_FREE_IMAGES[f.image];
    if (!image) throw new Error(t("Для цієї моделі ще не підготовлено зображення без ручки."));
    return image;
  }
  return f.image; // Entrance doors retain their actual integrated hardware.
}
const DOOR_TYPES = [
  { id: 'interior', name: t("Міжкімнатні"), category: 'interior', hint: t("Моделі для примірки з каталогу Bona Doors") },
  { id: 'hidden', name: t("Приховані"), category: 'interior', hint: t("Прихований монтаж — без широкої лиштви") },
  { id: 'mirror', name: t("Дзеркальні"), category: 'interior', hint: t("Дзеркальне полотно · відображення з фото каталогу") },
  { id: 'classic', name: t("Класичні"), category: 'interior', hint: t("Класичні фільончасті моделі Omega") },
  { id: 'exterior', name: t("Вхідні"), category: 'exterior', hint: t("Вхідні моделі для вулиці з каталогу Bona Doors") },
];
function productsForType(category, type) {
  if (!DOOR_TYPES.some(t => t.id === type && t.category === category)) return [];
  return PRODUCTS.filter(p => p.category === category && (type === category || p.types?.includes(type)));
}
function availableDoorTypes() { return DOOR_TYPES.filter(t => productsForType(t.category, t.id).length); }
// A type change is atomic: model, finish and previous choice remain consistent.
function withDoorType(current, type) {
  const category = DOOR_TYPES.find(t => t.id === type)?.category;
  const available = productsForType(category, type);
  if (!available.length) return current;
  const choices = { ...(current.typeSelections && typeof current.typeSelections === 'object' ? current.typeSelections : {}) };
  if (productsForType(PRODUCTS.find(p => p.id === current.product)?.category, current.doorType).some(p => p.id === current.product)) choices[current.doorType] = { product: current.product, color: current.color };
  const previous = choices[type];
  const selected = available.find(p => p.id === previous?.product) || available.find(p => p.id === current.product) || available[0];
  const color = previous?.product === selected.id ? previous.color : current.product === selected.id ? current.color : null;
  return { ...current, doorType: type, [category]: selected.id, product: selected.id, color: selected.colors.some(c => c.id === color) ? color : selected.colors[0].id, typeSelections: choices };
}
const DEFAULT_WALL = '#ffffff';
const WALL_PALETTE_VERSION = 1;
const PALETTE = [
  { name: t("Білий"), hex: DEFAULT_WALL },
  { name: t("Теплий кашемір"), hex: '#c2b8a8' },
  { name: t("Молочний"), hex: '#e8e3d9' },
  { name: t("Темно-сірий"), hex: '#494949' },
  { name: t("Пилова олива"), hex: '#868c76' },
  { name: t("Теракота"), hex: '#b38974' },
  { name: t("Димчасто-синій"), hex: '#8e9aa2' },
];
function restoredWall(saved) {
  if (/^#[0-9a-f]{6}$/i.test(saved?.wall)) return saved.wall.toLowerCase();
  // Upgrade the old photo-colored default once, without losing a deliberately selected color.
  if (saved?.wall === 'original' && saved.wallPaletteVersion === WALL_PALETTE_VERSION) return 'original';
  return DEFAULT_WALL;
}
const DEFAULT = { room: "living", product: PRODUCTS[0].id, color: PRODUCTS[0].colors[0].id, wall: DEFAULT_WALL, wallPaletteVersion: WALL_PALETTE_VERSION, handle: null, doorType: PRODUCTS[0].category, tab: "doors" };
const STORAGE = "bona-configurator-selection-v1";
let state = { ...DEFAULT };
try {
  const saved = JSON.parse(localStorage.getItem(STORAGE));
  if (saved && ROOMS.some(r => r.id === saved.room) && PRODUCTS.some(p => p.id === saved.product) && (saved.wall === 'original' || /^#[0-9a-f]{6}$/i.test(saved.wall))) {
    state = { ...DEFAULT, ...saved, wall: restoredWall(saved), wallPaletteVersion: WALL_PALETTE_VERSION, tab: 'doors' };
  }
} catch (_) { /* Private browsing and storage-disabled mode are supported. */ }
if (!HANDLES.some(h => h.id === state.handle)) state.handle = DEFAULT.handle;
if (!product().colors.some(c => c.id === state.color)) state.color = product().colors[0].id;
if (!productsForType(product().category, state.doorType).some(p => p.id === state.product)) state.doorType = product().category;

const $ = id => root.querySelector(`#studio-${id}`);
const canvas = $('room-canvas');
const ctx = canvas.getContext('2d');
const images = new Map();
const wallBases = new Map();
const wallTints = new Map();
const mobileLayout = matchMedia('(max-width: 900px)');
let renderVersion = 0;
let toastTimer;
let colorFrame;
let rendered = false;
function room() { return ROOMS.find(r => r.id === state.room); }
function product() { return PRODUCTS.find(p => p.id === state.product); }
function finish() { return product().colors.find(c => c.id === state.color) || product().colors[0]; }
function handle() { return product().category === 'interior' ? HANDLES.find(h => h.id === state.handle) : undefined; }
function money(value) { return new Intl.NumberFormat(config.locale === 'ru' ? 'ru-UA' : 'uk-UA').format(value) + ' ₴'; }
function remember() { try { localStorage.setItem(STORAGE, JSON.stringify(state)); } catch (_) {} }
function checkIcon() { return '<span class="selected-check"><svg aria-hidden="true"><use href="#studio-i-check"/></svg></span>'; }
function loadImage(file) {
  if (!images.has(file)) {
    images.set(file, new Promise((resolve, reject) => {
      const image = new Image();
      image.onload = () => resolve(image);
      image.onerror = () => { images.delete(file); reject(new Error(t("Не вдалося завантажити зображення"))); };
      image.src = ASSETS + file;
    }));
  }
  return images.get(file);
}
function toast(message) { clearTimeout(toastTimer); $('toast').textContent = message; $('toast').classList.add('visible'); toastTimer = setTimeout(() => $('toast').classList.remove('visible'), 4200); }

function initControls() {
  $('rooms').innerHTML = ROOMS.map(r => `<button class="room-option" data-room="${r.id}" aria-pressed="${r.id === state.room}">${esc(r.name)}</button>`).join('');
  $('wall-palette').innerHTML = PALETTE.map(c => `<button class="swatch" style="--swatch:${c.hex}" data-wall="${c.hex}" aria-label="${esc(c.name)}" title="${esc(c.name)}" aria-pressed="${state.wall === c.hex}"></button>`).join('') + `<button class="swatch original-swatch" style="--swatch:#b0afaa" data-wall="original" aria-label="${esc(t("Оригінальний колір кімнати"))}" title="${esc(t("Оригінальний колір кімнати"))}"><span>↺</span></button>`;
  $('handle-grid').innerHTML = HANDLES.map(h => `<button class="handle-card" data-handle="${h.id}" aria-label="${esc(h.name)}, ${esc(h.finish)}" aria-pressed="${state.handle === h.id}"><img src="${ASSETS + h.image}" alt="" width="100" height="65"><span><strong>${esc(h.name)}</strong><small>${esc(h.finish)}</small><small>${money(h.price)}</small></span>${checkIcon()}</button>`).join('');
  renderProducts();
  syncUI();
  applyLayout();
}
function renderProducts() {
  const available = productsForType(product().category, state.doorType), types = availableDoorTypes();
  $('door-grid').innerHTML = available.map(p => `<button class="product-card${p.flush ? ' product-card--interior-photo' : ''}" data-product="${p.id}" aria-label="${esc(p.name)}" aria-pressed="${p.id === state.product}"><span class="product-photo"><img src="${ASSETS + (p.id === state.product ? finish().image : p.colors[0].image)}" alt="" width="800" height="837"></span><span class="product-brand">${esc(p.brand)}</span><strong>${esc(p.short)}</strong>${checkIcon()}</button>`).join('');
  $('door-grid').scrollLeft = 0;
  $('door-grid').scrollTop = 0;
  $('door-help').textContent = DOOR_TYPES.find(t => t.id === state.doorType).hint;
  $('door-types').innerHTML = types.map(t => `<button class="door-type" data-door-type="${t.id}" aria-pressed="${t.id === state.doorType}">${t.name}</button>`).join('');
  $('door-type-select').innerHTML = types.map(t => `<option value="${t.id}" ${t.id === state.doorType ? 'selected' : ''}>${t.name}</option>`).join('');
}
function syncUI() {
  const p = product(), c = finish(), h = handle(), r = room();
  root.querySelectorAll('button[data-room]').forEach(b => b.setAttribute('aria-pressed', b.dataset.room === r.id));
  root.querySelectorAll('button[data-product]').forEach(b => {
    const item = PRODUCTS.find(item => item.id === b.dataset.product), active = item.id === p.id;
    b.setAttribute('aria-pressed', active);
    b.querySelector('img').src = ASSETS + (active ? c.image : item.colors[0].image);
  });
  root.querySelectorAll('button[data-wall]').forEach(b => b.setAttribute('aria-pressed', b.dataset.wall === state.wall));
  root.querySelectorAll('button[data-handle]').forEach(b => { b.setAttribute('aria-pressed', b.dataset.handle === h?.id); b.disabled = false; });
  const interior = p.category === 'interior';
  $('selected-availability').textContent = p.availability;
  $('selected-product-link').href = p.url;
  $('handle-grid').hidden = !interior; $('handle-help').hidden = !interior; $('no-handle').hidden = !interior; $('interior-handles').hidden = interior;
  $('no-handle').setAttribute('aria-pressed', !h);
  $('custom-color').value = state.wall === 'original' ? '#b0afaa' : state.wall;
  $('wall-hex').textContent = state.wall === 'original' ? t("Обрати колір") : state.wall.toUpperCase();
  $('wall-name').textContent = state.wall === 'original' ? t("Як на фото") : PALETTE.find(c => c.hex === state.wall)?.name || t("Ваш відтінок");
  $('door-color-name').textContent = c.name;
  $('door-finishes').innerHTML = p.colors.length > 1 ? p.colors.map(f => `<button class="swatch" data-finish="${f.id}" style="--swatch:${f.hex}" aria-label="${esc(f.name)}" title="${esc(f.name)}" aria-pressed="${f.id === c.id}"></button>`).join('') : `<span class="finish-single">${esc(t("Ця модель для примірки представлена в одному відтінку."))}</span>`;
  $('selected-name').textContent = p.name;
  $('selected-price').textContent = money(c.price);
  $('selected-details').textContent = c.name + (interior ? '' : ' · ' + t('штатна ручка'));
  $('selected-handle-row').hidden = !h;
  $('selected-handle-name').textContent = h ? `${h.name}` : '';
  $('selected-handle-price').textContent = h ? money(h.price) : '';
  $('selection-total').textContent = money(c.price + (h?.price || 0));
  const available = productsForType(p.category, state.doorType);
  $('door-counter').textContent = `${available.findIndex(item => item.id === p.id) + 1} / ${available.length}`;
  $('previous-door').disabled = $('next-door').disabled = available.length < 2;
  canvas.setAttribute('aria-label', `${r.name}. ${t("Двері")}: ${p.name}, ${c.name}. ${t("Колір стін")}: ${$('wall-name').textContent}. ${h ? h.name : t("Без додаткової ручки")}.`);
  $('handle-notice').textContent = interior ? t("Ці ручки доступні для візуальної примірки. У кошику це окремі товари, не готовий комплект. Сумісність, розміри та комплектацію уточніть із менеджером.") : t("У цих вхідних дверях уже є штатна ручка. Щоб приміряти змінні ручки, оберіть міжкімнатні двері.");
  remember();
}
function selectTab(tab, focus = false) {
  // Saved selections from the former four-tab mobile layout still open a valid panel.
  tab = tab === 'handles' ? 'handles' : 'doors';
  state.tab = tab;
  for (const id of ['doors', 'handles']) { const active = tab === id; $('tab-' + id).setAttribute('aria-selected', active); $('tab-' + id).tabIndex = active ? 0 : -1; }
  $('door-panel').hidden = tab !== 'doors'; $('handle-panel').hidden = tab !== 'handles';
  if (focus) $('tab-' + tab).focus();
}
function applyLayout() {
  if (mobileLayout.matches) {
    $('mobile-selection-footer').append($('intro-download'), $('scene-note'));
  } else {
    root.querySelector('.intro').append($('intro-download'));
    root.querySelector('.visual-column').append($('scene-note'));
  }
  selectTab(state.tab);
  sizeDesktopPanel();
}
function sizeDesktopPanel() {
  if (mobileLayout.matches) return;
  const panel = root.querySelector('.selection-panel');
  const top = panel.getBoundingClientRect().top + window.scrollY;
  root.querySelector('.workspace').style.setProperty('--panel-space', `${Math.max(540, window.innerHeight - top - 18)}px`);
}
const panelSizer = new ResizeObserver(sizeDesktopPanel);
panelSizer.observe(root.querySelector('.intro'));

window.addEventListener('resize', sizeDesktopPanel);
mobileLayout.addEventListener('change', applyLayout);
function chooseProduct(id) {
  if (id === state.product || !productsForType(product().category, state.doorType).some(p => p.id === id)) return;
  state.product = id; state[product().category] = id; state.color = product().colors[0].id;
  syncUI(); renderScene();
}
function cycleDoor(direction) {
  const available = productsForType(product().category, state.doorType);
  if (available.length < 2) return;
  const index = available.findIndex(p => p.id === state.product);
  chooseProduct(available[(index + direction + available.length) % available.length].id);
}
$('previous-door').addEventListener('click', () => cycleDoor(-1));
$('next-door').addEventListener('click', () => cycleDoor(1));
let touchStart;
canvas.addEventListener('touchstart', e => { touchStart = e.touches.length === 1 ? [e.touches[0].clientX, e.touches[0].clientY] : null; }, { passive: true });
canvas.addEventListener('touchend', e => {
  if (!touchStart || !e.changedTouches.length) return;
  const dx = e.changedTouches[0].clientX - touchStart[0], dy = e.changedTouches[0].clientY - touchStart[1]; touchStart = null;
  if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.5) cycleDoor(dx < 0 ? 1 : -1);
}, { passive: true });

// Masks belong to the generated room backgrounds only. Catalog source images are unchanged.
function wallMask(r, image) {
  const work = document.createElement('canvas'); work.width = 1536; work.height = 1024;
  const wc = work.getContext('2d', { willReadFrequently: true });
  wc.drawImage(image, 0, 0, 1536, 1024);
  const original = wc.getImageData(0, 0, 1536, 1024);
  const mask = document.createElement('canvas'); mask.width = 1536; mask.height = 1024;
  const mc = mask.getContext('2d', { willReadFrequently: true });
  mc.fillStyle = 'white';
  if (r.id === 'living') mc.fillRect(207, 74, 1212, r.wall - 74);
  else if (r.id === 'bedroom') mc.fillRect(80, 22, 1327, r.wall - 22);
  else if (r.id === 'hall') mc.fillRect(88, 0, 1238, r.wall);
  else mc.fillRect(0, 116, 1472, r.wall - 116);
  mc.globalCompositeOperation = 'destination-out';
  const polygon = points => { mc.beginPath(); points.forEach(([x, y], i) => i ? mc.lineTo(x, y) : mc.moveTo(x, y)); mc.closePath(); mc.fill(); };
  if (r.id === 'bedroom') {
    mc.fillRect(164,181,168,214); mc.fillRect(386,232,115,142);
    mc.fillRect(80,471,527,255); mc.fillRect(607,611,161,115);
    polygon([[608,471],[620,450],[649,444],[662,456],[659,481],[647,493],[622,489]]);
    polygon([[651,469],[698,493],[699,500],[687,499],[678,604],[696,614],[662,624],[635,621],[645,610],[667,607],[677,497],[650,478]]);
    mc.fillRect(624,570,47,27); mc.fillRect(716,570,32,44);
  } else if (r.id === 'hall') {
    mc.beginPath(); mc.ellipse(286,236,145,154,0,0,Math.PI*2); mc.fill();
    mc.fillRect(464,138,257,53); mc.fillRect(102,467,344,332);
    polygon([[520,178],[550,174],[576,203],[600,317],[620,466],[618,508],[583,520],[559,526],[533,513],[503,515],[486,480],[466,454],[474,379],[484,298],[496,239]]);
    polygon([[657,183],[667,180],[700,302],[741,308],[735,427],[726,445],[624,449],[619,436],[610,307],[628,303]]);
    mc.fillRect(450,637,266,45); mc.fillRect(451,679,22,120); mc.fillRect(704,679,12,120);
    mc.fillRect(148,417,54,51); mc.fillRect(371,436,29,32);
    polygon([[242,452],[344,451],[329,465],[259,469]]);
  } else if (r.id === 'entry') {
    mc.fillRect(40,194,409,370); mc.fillRect(1425,116,33,620);
    polygon([[505,710],[568,708],[568,736],[502,736]]);
  }
  const alpha = mc.getImageData(0, 0, 1536, 1024).data;
  for (let y = 0; y < r.wall; y++) for (let x = 0; x < 1536; x++) {
    const i = (y * 1536 + x) * 4, rr = original.data[i], gg = original.data[i + 1], bb = original.data[i + 2];
    const plantZone = (r.id === 'bedroom' && ((x > 700 && x < 780 && y > 500 && y < 612) || x > 1355)) || (r.id === 'entry' && x < 601 && y > 300) || (r.id === 'hall' && x > 115 && x < 248 && y > 300 && y < 424);
    if (plantZone && ((gg - rr > 2 && gg - bb > 10) || (gg - bb > (rr - gg) * 2.6 && rr - bb > 25) || Math.max(rr, gg, bb) < 73)) alpha[i + 3] = 0;
  }
  return { original, alpha };
}
function tintedRoom(r, image, hex) {
  if (wallTints.get(r.id)?.color === hex) return wallTints.get(r.id).data;
  if (!wallBases.has(r.id)) wallBases.set(r.id, wallMask(r, image));
  const { original, alpha } = wallBases.get(r.id);
  if (hex === 'original') return original;
  const output = new ImageData(new Uint8ClampedArray(original.data), 1536, 1024);
  const rgb = [1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16));
  for (let i = 0; i < output.data.length; i += 4) {
    const a = alpha[i + 3] / 255;
    if (!a) continue;
    for (let c = 0; c < 3; c++) {
      const shade = original.data[i + c] / r.base[c];
      output.data[i + c] = original.data[i + c] * (1 - a) + Math.min(255, rgb[c] * shade) * a;
    }
  }
  wallTints.set(r.id, { color: hex, data: output });
  return output;
}
function drawHandle(context, x, y, height, h, side) {
  const s = height / 837;
  context.save(); context.translate(x, y); context.scale(s, s);
  if (side === 'right') context.scale(-1, 1);
  context.shadowColor = '#00000050'; context.shadowBlur = 3; context.shadowOffsetY = 2;
  const metal = context.createLinearGradient(0, -11, 0, 13); metal.addColorStop(0, h.accent); metal.addColorStop(.3, h.color); metal.addColorStop(.7, h.color); metal.addColorStop(1, h.accent);
  context.fillStyle = metal; context.strokeStyle = '#00000040'; context.lineWidth = .7;
  context.beginPath();
  if (h.shape === 'round') context.arc(0, 0, 14, 0, Math.PI * 2); else context.roundRect(-13, -13, 26, 26, 2);
  context.fill(); context.stroke();
  context.beginPath(); context.roundRect(-2, -3, 59, 9, 3); context.fill(); context.stroke();
  context.restore();
}
function drawDoor(context, p, img, h, x, y, height) {
  const [sx, sy, sw, sh] = p.crop;
  const width = height * sw / sh;
  context.save();
  // The catalog photo supplies its own frame/reveal; do not add a dark outline or shadow plate.
  context.shadowColor = 'transparent';
  context.shadowBlur = 0; context.shadowOffsetX = 0; context.shadowOffsetY = 0;
  // Crop only the white studio margins; the door itself keeps its source proportions.
  if (p.face) {
    // Map the photographed quadrilateral into the preview opening without surrounding furniture.
    const [lx, lt, rx, rt, , rb, , lb] = p.face, steps = Math.ceil(width);
    for (let i = 0; i < steps; i++) {
      const t = i / steps, top = lt + (rt - lt) * t, bottom = lb + (rb - lb) * t;
      context.drawImage(img, (lx + (rx - lx) * t) / 800 * img.naturalWidth, top / 837 * img.naturalHeight, (rx - lx) / steps / 800 * img.naturalWidth, (bottom - top) / 837 * img.naturalHeight, x + width * t, y, Math.min(width / steps + .5, width * (1 - t)), height);
    }
  } else context.drawImage(img, sx / 800 * img.naturalWidth, sy / 837 * img.naturalHeight, sw / 800 * img.naturalWidth, sh / 837 * img.naturalHeight, x, y, width, height);
  if (p.category === 'interior' && h) drawHandle(context, x + p.handle[0] * width, y + p.handle[1] * height, height, h, p.handleSide);
  context.restore();
  return width;
}
async function renderScene() {
  const version = ++renderVersion;
  const r = room(), p = product(), f = finish(), h = handle(), wall = state.wall;
  rendered = false; $('save').disabled = true; $('zoom').disabled = true; $('scene-loading').hidden = false; $('scene-loading').textContent = t('Готуємо ваш простір…');
  try {
    const [background, doorImage] = await Promise.all([loadImage(r.image), loadImage(previewDoorImage(p, f))]);
    if (version !== renderVersion) return;
    ctx.putImageData(tintedRoom(r, background, wall), 0, 0);
    const width = r.door[2] * p.crop[2] / p.crop[3];
    const x = r.id === 'living' ? (1536 - width) / 2 : r.door[0];
    drawDoor(ctx, p, doorImage, h, x, r.door[1], r.door[2]);
    $('scene-loading').hidden = true;
    rendered = true; $('save').disabled = false; $('zoom').disabled = false;
    canvas.dataset.room = r.id; canvas.dataset.product = p.id; canvas.dataset.type = state.doorType; canvas.dataset.color = f.id; canvas.dataset.wall = wall; canvas.dataset.handle = h?.id || (p.category === 'interior' ? 'none' : 'integrated');
    if ($('detail-dialog').open) renderDetail(doorImage);
  } catch (error) {
    if (version !== renderVersion) return;
    $('scene-loading').hidden = false;
    $('scene-loading').textContent = t("Зображення недоступне. Спробуйте обрати іншу модель або оновити сторінку.");
    toast(error.message);
  }
}
async function renderDetail(loaded) {
  const p = product(), f = finish(), h = handle();
  const img = loaded || await loadImage(previewDoorImage(p, f));
  const dc = $('detail-canvas').getContext('2d');
  dc.fillStyle = state.wall === 'original' ? '#b0afaa' : state.wall; dc.fillRect(0, 0, 800, 1000);
  const height = 900, width = height * p.crop[2] / p.crop[3];
  drawDoor(dc, p, img, h, (800 - width) / 2, 60, height);
  $('detail-title').textContent = p.name;
  $('detail-caption').textContent = f.name + ' · ' + (h ? h.name + ', ' + h.finish + '. ' + t('Примірка ручки схематична.') : p.category === 'interior' ? t("Без ручки. Оберіть її у вкладці «Ручки».") : t("Штатна ручка моделі."));
}

$('rooms').addEventListener('click', event => {
  const b = event.target.closest('[data-room]'); if (!b || b.dataset.room === state.room) return;
  state.room = b.dataset.room;
  selectTab('doors'); renderProducts(); syncUI(); renderScene();
});
function chooseDoorType(type) {
  if (type === state.doorType) return;
  const next = withDoorType(state, type); if (next === state) return;
  state = next; renderProducts(); syncUI(); renderScene();
}
$('door-types').addEventListener('click', event => {
  const button = event.target.closest('[data-door-type]'); if (!button) return;
  chooseDoorType(button.dataset.doorType);
  $('door-types').querySelector(`[data-door-type="${state.doorType}"]`)?.focus({ preventScroll: true });
});
$('door-type-select').addEventListener('change', event => chooseDoorType(event.target.value));
$('door-grid').addEventListener('click', event => {
  const b = event.target.closest('[data-product]'); if (!b || b.dataset.product === state.product) return;
  chooseProduct(b.dataset.product);
});
$('door-finishes').addEventListener('click', event => {
  const b = event.target.closest('[data-finish]'); if (!b) return;
  state.color = b.dataset.finish; syncUI(); renderScene();
});
$('wall-palette').addEventListener('click', event => {
  const b = event.target.closest('[data-wall]'); if (!b) return;
  state.wall = b.dataset.wall; syncUI(); renderScene();
});
$('custom-color').addEventListener('input', event => {
  state.wall = event.target.value.toLowerCase(); syncUI();
  cancelAnimationFrame(colorFrame); colorFrame = requestAnimationFrame(renderScene);
});
$('handle-grid').addEventListener('click', event => {
  const b = event.target.closest('[data-handle]'); if (!b || product().category !== 'interior') return;
  state.handle = b.dataset.handle; syncUI(); renderScene();
});
$('no-handle').addEventListener('click', () => { state.handle = null; syncUI(); renderScene(); });
$('interior-handles').addEventListener('click', () => { chooseDoorType('interior'); selectTab('handles'); });
for (const name of ['doors', 'handles']) {
  $('tab-' + name).addEventListener('click', () => selectTab(name));
  $('tab-' + name).addEventListener('keydown', event => {
    if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
      event.preventDefault(); const tabs = ['doors', 'handles'];
      const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (tabs.indexOf(name) + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
      selectTab(tabs[next], true);
    }
  });
}
$('reset').addEventListener('click', () => { state = { ...DEFAULT }; selectTab('doors'); renderProducts(); syncUI(); renderScene(); toast("Повернули початковий образ"); });
$('zoom').addEventListener('click', async () => { if (!rendered) return; await renderDetail(); $('detail-dialog').showModal(); });
$('close-detail').addEventListener('click', () => $('detail-dialog').close());
$('detail-dialog').addEventListener('click', event => { if (event.target === $('detail-dialog')) { const bounds = $('detail-dialog').getBoundingClientRect(); if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) $('detail-dialog').close(); } });
$('save').addEventListener('click', async () => {
  if (!rendered) return;
  const p = product(), f = finish(), r = room(), h = handle(), wall = state.wall;
  const exportCanvas = document.createElement('canvas'); exportCanvas.width = 1536; exportCanvas.height = 1160;
  const ec = exportCanvas.getContext('2d'); ec.fillStyle = '#f9f8f4'; ec.fillRect(0, 0, 1536, 1160); ec.drawImage(canvas, 0, 0);
  await document.fonts.ready;
  ec.fillStyle = '#28241e'; ec.font = '36px Forum'; ec.fillText('Bona Doors / ' + p.name, 44, 1075);
  ec.font = '16px Manrope'; ec.fillStyle = '#7a776f'; ec.fillText(`${r.name} · ${f.name} · ${t("стіни")} ${wall === 'original' ? t("як на фото") : wall.toUpperCase()} · ${h ? h.name + ', ' + h.short : p.category === 'interior' ? t("без додаткової ручки") : t("штатна ручка")}`, 44, 1110);
  ec.font = '12px Manrope'; ec.fillText(t("Візуальна примірка, не точний 3D-проєкт. Відтінки, масштаб і вигляд ручки — орієнтовні."), 44, 1140);
  try {
    exportCanvas.toBlob(blob => {
      if (!blob) { toast("Не вдалося зберегти зображення"); return; }
      const url = URL.createObjectURL(blob), link = document.createElement('a'); link.href = url; link.download = `bona-${r.id}-${p.id}-${f.id}.png`; link.click();
      setTimeout(() => URL.revokeObjectURL(url), 60000); toast("Образ збережено як PNG");
    }, 'image/png');
  } catch (_) { toast("Не вдалося зберегти зображення"); }
});

let pendingSelection = null;
$('order-selection').addEventListener('click', async () => {
 const button = $('order-selection');
 if (button.disabled) return;
 const selection = { product: product().id, color: finish().id, handle: handle()?.id || null };
 const signature = JSON.stringify(selection);
 if (pendingSelection?.signature !== signature) pendingSelection = { signature, id: globalThis.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => { const n = Math.floor(Math.random()*16); return (c === 'x' ? n : (n&3)|8).toString(16); }) };
 button.disabled = true; button.setAttribute('aria-busy', 'true'); $('order-label').textContent = t('Додаємо…'); $('cart-error').hidden = true;
 const controller = new AbortController();
 const timeout = setTimeout(() => controller.abort(), 30000);
 try {
  const response = await fetch(config.cartUrl, { method:'POST', credentials:'same-origin', signal:controller.signal, headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':config.csrf}, body:JSON.stringify({...selection, request_id:pendingSelection.id, expected_total:finish().price+(handle()?.price||0)}) });
  const data = await response.json().catch(()=>({}));
  if (!response.ok) {
   if (response.status < 500) pendingSelection = null;
   throw new Error(response.status === 419 ? t('Сесія закінчилася. Оновіть сторінку та повторіть спробу.') : (Object.values(data.errors||{}).flat()[0] || data.message || t('Не вдалося додати товари. Перевірте з’єднання та повторіть спробу.')));
  }
  handleBasket(data); pendingSelection = null;
  toast(t('Позиції додано до кошика. Кількість можна змінити в кошику.'));
 } catch(error) {
  $('cart-error-message').textContent = error.name === 'AbortError' || error instanceof TypeError ? t('Не вдалося додати товари. Перевірте з’єднання та повторіть спробу.') : error.message || t('Не вдалося додати товари. Перевірте з’єднання та повторіть спробу.'); $('cart-error').hidden = false;
 } finally { clearTimeout(timeout); button.disabled = false; button.removeAttribute('aria-busy'); $('order-label').textContent = t('Замовити'); }
});
$('reload').addEventListener('click', () => window.location.reload());
initControls();
renderScene();
}
