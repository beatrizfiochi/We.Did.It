import { fetchJSON } from './lib/fetch-json.js';
import { $, clear, el } from './lib/dom.js';

const POLL_MS = 30 * 60_000;
const ADVANCE_MS = 8_000;

let items = [];
let index = 0;
let timer = null;

function renderCard(c) {
  const card = el('li', { class: 'course-card', 'data-fade-in': '' },
    el('div', {
      class: 'course-card-img',
      style: c.imageUrl ? { backgroundImage: `url("${c.imageUrl}")` } : {},
    }),
    el('div', { class: 'course-card-body' },
      el('div', { class: 'course-card-meta' },
        c.location && el('span', {}, c.location),
        c.schedule && el('span', { class: 'pill pill-muted' }, c.schedule),
      ),
      el('div', { class: 'course-card-title' }, c.title),
      el('div', { class: 'course-card-foot' },
        c.startDate && el('div', { class: 'course-card-date' }, c.startDate),
        c.price && el('span', { class: 'pill pill-accent' }, c.price),
      ),
    ),
  );
  return card;
}

function paint() {
  const strip = $('[data-courses-strip]');
  if (!strip) return;
  clear(strip);
  if (items.length === 0) return;
  // Render in a rotated order so the visible 3 are always: index, index+1, index+2 (then rest)
  const ordered = [];
  for (let i = 0; i < items.length; i++) ordered.push(items[(index + i) % items.length]);
  ordered.forEach((c) => strip.append(renderCard(c)));
}

function advance() {
  if (items.length <= 3) return;
  index = (index + 1) % items.length;
  paint();
}

export async function mount() {
  async function refresh() {
    const env = await fetchJSON('/api/courses');
    if (env.data && Array.isArray(env.data.items) && env.data.items.length > 0) {
      items = env.data.items;
      index = 0;
      paint();
    }
  }
  await refresh();
  if (timer) clearInterval(timer);
  timer = setInterval(advance, ADVANCE_MS);
  setInterval(refresh, POLL_MS);
}
