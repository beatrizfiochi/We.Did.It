import { fetchJSON } from './lib/fetch-json.js';
import { $, $$, clear, el } from './lib/dom.js';

const POLL_MS = 30_000;
const TAB_ROTATE_MS = 12_000;

const METRO_COLORS = {
  A: 'var(--metro-A)', B: 'var(--metro-B)', C: 'var(--metro-C)',
  D: 'var(--metro-D)', E: 'var(--metro-E)', F: 'var(--metro-F)', G: 'var(--metro-G)',
};

let activeTab = 'metro';

function setTab(tab) {
  activeTab = tab;
  $$('.transit-tab').forEach((b) => b.setAttribute('aria-pressed', b.dataset.tab === tab ? 'true' : 'false'));
  $$('.transit-pane').forEach((p) => p.classList.toggle('is-active', p.dataset.pane === tab));
}

function renderMetro(data) {
  const stopEl = $('[data-metro-stop]');
  const modeEl = $('[data-metro-mode]');
  const linesEl = $('[data-metro-lines]');
  if (!data) return;
  if (stopEl) stopEl.textContent = data.stop || 'Casa da Música';
  if (modeEl) {
    modeEl.textContent = `${data.note || 'Horário previsto'} · ${data.distance || ''}`.trim();
    modeEl.dataset.mode = 'schedule';
  }
  if (!linesEl) return;
  clear(linesEl);
  (data.lines || []).slice(0, 8).forEach((row) => {
    linesEl.append(el('li', { class: 'transit-line' },
      el('span', { class: 'transit-line-badge', style: { background: METRO_COLORS[row.line] || 'var(--accent)' } }, row.line),
      el('span', { class: 'transit-line-dest' }, row.destination),
      el('span', { class: 'transit-line-eta' },
        String(row.etaMin ?? '—'),
        el('span', { class: 'transit-line-eta-unit' }, 'min'),
      ),
    ));
  });
}

function renderStcp(data) {
  const stopsEl = $('[data-stcp-stops]');
  const modeEl = $('[data-stcp-mode]');
  if (!data || !stopsEl) return;
  const isLive = data.sourceMode === 'live';
  if (modeEl) {
    modeEl.textContent = isLive ? 'Em tempo real' : 'Horário previsto';
    modeEl.dataset.mode = isLive ? 'live' : 'schedule';
  }
  clear(stopsEl);
  (data.stops || []).forEach((stop) => {
    const linesUl = el('ul', { class: 'transit-lines' });
    (stop.lines || []).slice(0, 4).forEach((row) => {
      linesUl.append(el('li', { class: 'transit-line' },
        el('span', { class: 'transit-line-badge bus' }, row.line),
        el('span', { class: 'transit-line-dest' }, row.destination),
        el('span', { class: 'transit-line-eta' },
          row.etaMin == null ? '—' : String(row.etaMin),
          el('span', { class: 'transit-line-eta-unit' }, 'min'),
        ),
      ));
    });
    stopsEl.append(el('li', { class: 'transit-stop' },
      el('div', { class: 'transit-stop-header' },
        el('span', { class: 'transit-stop-name' }, stop.name),
        el('span', { class: 'pill pill-muted' }, stop.code || ''),
      ),
      linesUl,
    ));
  });
}

export async function mount() {
  $$('.transit-tab').forEach((b) => b.addEventListener('click', () => setTab(b.dataset.tab)));
  setTab('metro');

  async function refresh() {
    const [m, s] = await Promise.all([
      fetchJSON('/api/transit/metro'),
      fetchJSON('/api/transit/stcp'),
    ]);
    if (m.data) renderMetro(m.data);
    if (s.data) renderStcp(s.data);
  }
  await refresh();
  setInterval(refresh, POLL_MS);

  // Auto-rotate tab
  setInterval(() => setTab(activeTab === 'metro' ? 'stcp' : 'metro'), TAB_ROTATE_MS);
}
