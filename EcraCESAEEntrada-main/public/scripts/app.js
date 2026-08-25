import { mount as mountClock }   from './clock.js';
import { mount as mountWeather } from './weather.js';
import { mount as mountYouTube } from './youtube.js';
import { mount as mountCourses } from './courses.js';
import { mount as mountNews }    from './news.js';
import { mount as mountTransit } from './transit.js';

function scheduleDailyReload(hour = 4, minute = 0) {
  const now = new Date();
  const next = new Date(now);
  next.setHours(hour, minute, 0, 0);
  if (next <= now) next.setDate(next.getDate() + 1);
  const ms = next.getTime() - now.getTime();
  setTimeout(() => location.reload(), ms);
}

function installErrorHandlers() {
  window.addEventListener('error', (e) => console.error('window error', e.message));
  window.addEventListener('unhandledrejection', (e) => console.error('unhandled rejection', e.reason));
}

async function start() {
  installErrorHandlers();
  scheduleDailyReload(4, 0);

  // Mount each module independently. A failure in one doesn't stop the others.
  const modules = [
    ['clock',   mountClock],
    ['weather', mountWeather],
    ['youtube', mountYouTube],
    ['courses', mountCourses],
    ['news',    mountNews],
    ['transit', mountTransit],
  ];

  await Promise.allSettled(modules.map(async ([name, fn]) => {
    try { await fn(); }
    catch (e) { console.error(`[mount:${name}] failed`, e); }
  }));
}

start();
