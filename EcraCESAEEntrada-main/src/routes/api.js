import express from 'express';
import { makeCache } from '../cache.js';
import { fetchWeather } from '../sources/weather.js';
import { fetchNews } from '../sources/news.js';
import { fetchCourses } from '../sources/courses.js';
import { fetchMetro } from '../sources/metro.js';
import { fetchStcp } from '../sources/stcp.js';

const caches = {
  weather: makeCache({ key: 'weather', ttlMs: 10 * 60_000, staleAfterMs: 6 * 60 * 60_000, fetcher: fetchWeather }),
  news:    makeCache({ key: 'news',    ttlMs: 5 * 60_000,  staleAfterMs: 60 * 60_000,      fetcher: fetchNews }),
  courses: makeCache({ key: 'courses', ttlMs: 30 * 60_000, staleAfterMs: 24 * 60 * 60_000, fetcher: fetchCourses }),
  metro:   makeCache({ key: 'metro',   ttlMs: 60_000,      staleAfterMs: 5 * 60_000,       fetcher: fetchMetro }),
  stcp:    makeCache({ key: 'stcp',    ttlMs: 30_000,      staleAfterMs: 10 * 60_000,      fetcher: fetchStcp }),
};

const router = express.Router();

function noCache(_req, res, next) {
  res.set('Cache-Control', 'no-store');
  next();
}

router.use(noCache);

router.get('/weather', async (_req, res) => res.json(await caches.weather.get()));
router.get('/news',    async (_req, res) => res.json(await caches.news.get()));
router.get('/courses', async (_req, res) => res.json(await caches.courses.get()));
router.get('/transit/metro', async (_req, res) => res.json(await caches.metro.get()));
router.get('/transit/stcp',  async (_req, res) => res.json(await caches.stcp.get()));

router.get('/health', (_req, res) => {
  res.json({
    status: 'ok',
    uptimeSec: Math.round(process.uptime()),
    memoryMB: Math.round(process.memoryUsage().rss / 1024 / 1024),
    caches: Object.fromEntries(Object.entries(caches).map(([k, c]) => [k, c.status()])),
  });
});

export async function warmUp() {
  const tasks = Object.entries(caches).map(async ([k, c]) => {
    const t = Date.now();
    try {
      await c.refresh();
      return { k, ok: true, ms: Date.now() - t };
    } catch (e) {
      return { k, ok: false, ms: Date.now() - t, err: e.message };
    }
  });
  return Promise.allSettled(tasks);
}

export default router;
