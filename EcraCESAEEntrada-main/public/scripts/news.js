import { fetchJSON } from './lib/fetch-json.js';
import { $, clear, el } from './lib/dom.js';

const POLL_MS = 5 * 60_000;

function renderItems(items) {
  const track = $('[data-news-track]');
  if (!track) return;
  clear(track);

  // Duplicate the list once so the CSS marquee can scroll from 0 to -50% seamlessly
  const both = [...items, ...items];

  both.forEach((it, i) => {
    track.append(el('span', { class: 'ticker-item' },
      el('span', { class: 'ticker-source' }, it.source || ''),
      el('span', { class: 'ticker-text' }, it.title || ''),
    ));
    if (i < both.length - 1) {
      track.append(el('span', { class: 'ticker-sep' }, '•'));
    }
  });

  // Adapt duration to content length so reading speed stays comfortable
  const approxChars = items.reduce((acc, it) => acc + (it.title?.length || 0) + (it.source?.length || 0) + 4, 0);
  const seconds = Math.max(60, Math.round(approxChars / 6));
  track.style.animationDuration = `${seconds}s`;
}

export async function mount() {
  async function refresh() {
    const env = await fetchJSON('/api/news');
    if (env.data && Array.isArray(env.data.items) && env.data.items.length > 0) {
      renderItems(env.data.items);
    }
  }
  await refresh();
  setInterval(refresh, POLL_MS);
}
