import { fetchJSON } from './lib/fetch-json.js';
import { $, clear, el } from './lib/dom.js';

const POLL_MS = 10 * 60_000;

// WMO weather code → { icon (emoji-style), label-pt }
// Compact mapping covering the codes Open-Meteo emits.
const WMO = {
  0:  { icon: '☀',  pt: 'Céu limpo' },
  1:  { icon: '🌤', pt: 'Maioritariamente limpo' },
  2:  { icon: '⛅', pt: 'Parcialmente nublado' },
  3:  { icon: '☁',  pt: 'Nublado' },
  45: { icon: '🌫', pt: 'Nevoeiro' },
  48: { icon: '🌫', pt: 'Nevoeiro com geada' },
  51: { icon: '🌦', pt: 'Chuvisco fraco' },
  53: { icon: '🌦', pt: 'Chuvisco' },
  55: { icon: '🌧', pt: 'Chuvisco forte' },
  61: { icon: '🌦', pt: 'Chuva fraca' },
  63: { icon: '🌧', pt: 'Chuva' },
  65: { icon: '🌧', pt: 'Chuva forte' },
  66: { icon: '🌧', pt: 'Chuva gelada fraca' },
  67: { icon: '🌧', pt: 'Chuva gelada' },
  71: { icon: '🌨', pt: 'Neve fraca' },
  73: { icon: '🌨', pt: 'Neve' },
  75: { icon: '🌨', pt: 'Neve forte' },
  77: { icon: '🌨', pt: 'Granizo' },
  80: { icon: '🌦', pt: 'Aguaceiros fracos' },
  81: { icon: '🌧', pt: 'Aguaceiros' },
  82: { icon: '⛈', pt: 'Aguaceiros fortes' },
  85: { icon: '🌨', pt: 'Aguaceiros de neve' },
  86: { icon: '🌨', pt: 'Aguaceiros de neve fortes' },
  95: { icon: '⛈', pt: 'Trovoada' },
  96: { icon: '⛈', pt: 'Trovoada com granizo' },
  99: { icon: '⛈', pt: 'Trovoada com granizo forte' },
};

function describe(code) { return WMO[code] || { icon: '·', pt: '—' }; }

const dayNameFmt = new Intl.DateTimeFormat('pt-PT', { weekday: 'short', timeZone: 'Europe/Lisbon' });

function shortDay(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T12:00:00');
  return dayNameFmt.format(d).replace('.', '');
}

function render(data) {
  if (!data) return;
  const cur = data.current || {};
  const d = describe(cur.weatherCode);

  const iconEl = $('[data-weather-icon]');
  const tempEl = $('[data-weather-temp]');
  const condEl = $('[data-weather-condition]');
  const feelsEl = $('[data-weather-feels]');
  if (iconEl) iconEl.textContent = d.icon;
  if (tempEl) tempEl.textContent = cur.tempC != null ? `${cur.tempC}°` : '—°';
  if (condEl) condEl.textContent = d.pt;
  if (feelsEl) feelsEl.textContent = cur.feelsLikeC != null ? `Sensação ${cur.feelsLikeC}°` : '';

  const strip = $('[data-forecast-strip]');
  if (strip) {
    clear(strip);
    (data.daily || []).slice(0, 5).forEach((day) => {
      const dd = describe(day.code);
      strip.append(el('li', { class: 'forecast-day' },
        el('div', { class: 'forecast-day-name' }, shortDay(day.date)),
        el('div', { class: 'forecast-day-icon' }, dd.icon),
        el('div', { class: 'forecast-day-temps' },
          el('span', { class: 'max' }, day.maxC != null ? `${day.maxC}°` : '—'),
          el('span', { class: 'min' }, day.minC != null ? `${day.minC}°` : '—'),
        ),
      ));
    });
  }
}

export async function mount() {
  async function refresh() {
    const env = await fetchJSON('/api/weather');
    if (env.data) render(env.data);
  }
  await refresh();
  setInterval(refresh, POLL_MS);
}
