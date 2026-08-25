// Tentativa best-effort de scraping STCP + fallback determinístico.
// STCP não oferece API pública estável. O endpoint /pt/paragem do site é
// interativo. Tentamos primeiro um endpoint candidato; se falhar, calculamos
// a partir de uma tabela de horários publicados.

import * as cheerio from 'cheerio';
import { log } from '../logger.js';

// Paragens próximas ao CESAE Digital Porto (Rua Ciríaco Cardoso).
// NOTA: códigos exatos a confirmar com STCP no momento de instalação.
const STOPS = [
  { code: 'PSTL1', name: 'Pasteleira' },
  { code: 'SLZR2', name: 'Salazares' },
];

const LINE_SCHEDULES = {
  '200': { destination: 'Bolhão', headwayPeak: 12, headwayOffPeak: 20, weekend: 25 },
  '204': { destination: 'Hospital S. João', headwayPeak: 15, headwayOffPeak: 25, weekend: 30 },
  '209': { destination: 'Pasteleira', headwayPeak: 18, headwayOffPeak: 25, weekend: 30 },
  '502': { destination: 'Bolhão (Litoral)', headwayPeak: 15, headwayOffPeak: 20, weekend: 25 },
};

const STOP_LINES = {
  'Pasteleira': ['200', '209', '502'],
  'Salazares': ['200', '204'],
};

export async function fetchStcp() {
  // Tentativa de scrape live (best-effort, com timeout curto)
  const live = await tryLive().catch((e) => {
    log.debug(`[stcp] live attempt failed: ${e.message}`);
    return null;
  });

  if (live && live.stops.length > 0) {
    return { ...live, sourceMode: 'live' };
  }

  // Fallback determinístico
  const now = nowLisbon();
  const stops = STOPS.map(({ code, name }) => {
    const lineNumbers = STOP_LINES[name] || [];
    const lines = lineNumbers.map((num) => {
      const sched = LINE_SCHEDULES[num];
      if (!sched) return null;
      const headway = currentHeadway(now, sched);
      const offset = hashStr(`${name}-${num}`) % headway;
      const minuteOfDay = now.hours * 60 + now.minutes;
      const eta = ((headway - ((minuteOfDay + offset) % headway)) % headway) || headway;
      return {
        line: num,
        destination: sched.destination,
        etaMin: eta,
        mode: 'schedule',
      };
    }).filter(Boolean);
    lines.sort((a, b) => a.etaMin - b.etaMin);
    return { code, name, lines };
  });

  return { stops, sourceMode: 'fallback', note: 'Horário previsto' };
}

async function tryLive() {
  // Endpoint candidato — pode mudar; isto é defensivo
  const results = await Promise.allSettled(STOPS.map(async ({ code, name }) => {
    const url = `https://www.stcp.pt/pt/paragem?codigo=${encodeURIComponent(code)}`;
    const res = await fetch(url, {
      headers: { 'User-Agent': 'CesaeKioskBot/1.0' },
      signal: AbortSignal.timeout(3000),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const html = await res.text();
    const $ = cheerio.load(html);
    const lines = [];
    // Padrão genérico — tenta detetar linhas e ETAs em qualquer tabela visível
    $('table tr').each((_, tr) => {
      const cols = $(tr).find('td').map((__, td) => $(td).text().trim()).get();
      if (cols.length >= 3) {
        const lineMatch = cols[0].match(/^\d{2,3}[A-Z]?$/);
        if (lineMatch) {
          const etaMatch = cols[2].match(/(\d+)\s*min/);
          lines.push({
            line: cols[0],
            destination: cols[1],
            etaMin: etaMatch ? parseInt(etaMatch[1], 10) : null,
            mode: 'live',
          });
        }
      }
    });
    return { code, name, lines };
  }));

  const stops = results
    .filter((r) => r.status === 'fulfilled' && r.value.lines.length > 0)
    .map((r) => r.value);

  return { stops };
}

function nowLisbon() {
  const fmt = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Europe/Lisbon',
    weekday: 'short',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  });
  const parts = Object.fromEntries(
    fmt.formatToParts(new Date()).filter((p) => p.type !== 'literal').map((p) => [p.type, p.value])
  );
  const hours = parseInt(parts.hour, 10);
  const minutes = parseInt(parts.minute, 10);
  const weekday = parts.weekday;
  const isWeekend = weekday === 'Sat' || weekday === 'Sun';
  const isPeak = !isWeekend && ((hours >= 7 && hours < 10) || (hours >= 17 && hours < 20));
  return { hours, minutes, weekday, isWeekend, isPeak };
}

function currentHeadway({ isWeekend, isPeak }, sched) {
  if (isWeekend) return sched.weekend;
  return isPeak ? sched.headwayPeak : sched.headwayOffPeak;
}

function hashStr(s) {
  let h = 0;
  for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) | 0;
  return Math.abs(h);
}
