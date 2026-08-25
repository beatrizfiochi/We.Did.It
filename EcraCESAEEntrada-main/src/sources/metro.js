// Cálculo determinístico de próximos comboios para Casa da Música.
// Metro do Porto não tem API pública de tempo real; usamos as frequências
// publicadas e calculamos ETA a partir da hora atual em Europe/Lisbon.

const LINES = [
  { line: 'A', color: '#0070c0', destinations: ['Sr. de Matosinhos', 'Estádio do Dragão'] },
  { line: 'B', color: '#e10000', destinations: ['Póvoa de Varzim', 'Estádio do Dragão'] },
  { line: 'C', color: '#1c8a3a', destinations: ['ISMAI', 'Campanhã'] },
  { line: 'E', color: '#7e3f9d', destinations: ['Aeroporto', 'Estádio do Dragão'] },
  { line: 'F', color: '#ef7d00', destinations: ['Fânzeres', 'Senhora da Hora'] },
];

// minutos entre passagens em cada sentido, por período
const HEADWAY = {
  weekdayPeak: 10,    // 07:00–09:30, 17:00–20:00
  weekdayOffPeak: 15,
  weekend: 15,
};

export async function fetchMetro() {
  const now = nowLisbon();
  const headway = currentHeadway(now);
  const minuteOfDay = now.hours * 60 + now.minutes;

  const lines = [];
  LINES.forEach(({ line, color, destinations }, idx) => {
    destinations.forEach((destination, dirIdx) => {
      // Offset arbitrário mas estável por linha+sentido, para não dar sempre o mesmo valor
      const offset = (idx * 3 + dirIdx * 5) % headway;
      const eta = ((headway - ((minuteOfDay + offset) % headway)) % headway) || headway;
      lines.push({ line, color, destination, etaMin: eta });
    });
  });

  lines.sort((a, b) => a.etaMin - b.etaMin);

  return {
    stop: 'Casa da Música',
    distance: '≈15 min a pé',
    lines,
    note: 'Horário previsto',
    period: now.period,
    headwayMin: headway,
  };
}

function nowLisbon() {
  // Intl.DateTimeFormat em pt-PT com timezone Lisbon, depois parseamos as partes
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
  const weekday = parts.weekday; // Mon, Tue, ..., Sun
  const isWeekend = weekday === 'Sat' || weekday === 'Sun';
  const isPeak = !isWeekend && (
    (hours >= 7 && hours < 10) ||
    (hours >= 17 && hours < 20)
  );
  const period = isWeekend ? 'fim-de-semana' : (isPeak ? 'hora de ponta' : 'horário normal');
  return { hours, minutes, weekday, isWeekend, isPeak, period };
}

function currentHeadway({ isWeekend, isPeak }) {
  if (isWeekend) return HEADWAY.weekend;
  return isPeak ? HEADWAY.weekdayPeak : HEADWAY.weekdayOffPeak;
}
