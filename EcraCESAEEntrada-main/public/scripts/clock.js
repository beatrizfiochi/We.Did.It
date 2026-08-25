const TZ = 'Europe/Lisbon';

const timeFmt = new Intl.DateTimeFormat('pt-PT', {
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
  timeZone: TZ,
});

const dateFmt = new Intl.DateTimeFormat('pt-PT', {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
  timeZone: TZ,
});

function capitalizeFirst(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
}

export function mount() {
  const timeEl = document.querySelector('[data-clock-time]');
  const dateEl = document.querySelector('[data-clock-date]');

  function tick() {
    const now = new Date();
    if (timeEl) timeEl.textContent = timeFmt.format(now);
    if (dateEl) dateEl.textContent = capitalizeFirst(dateFmt.format(now));
  }

  tick();
  // Tick on the minute boundary then every minute
  const now = new Date();
  const msToNextMinute = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();
  setTimeout(() => {
    tick();
    setInterval(tick, 60_000);
  }, msToNextMinute);
}
