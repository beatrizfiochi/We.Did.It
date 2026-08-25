import Parser from 'rss-parser';
import { log } from '../logger.js';

const parser = new Parser({
  timeout: 5000,
  headers: { 'User-Agent': 'CesaeKioskBot/1.0' },
});

const FEEDS = [
  { name: 'RTP', url: 'https://www.rtp.pt/noticias/rss' },
  { name: 'Público', url: 'https://feeds.feedburner.com/publicoRSS' },
];

const MAX_ITEMS = 30;

export async function fetchNews() {
  const results = await Promise.allSettled(FEEDS.map(async (f) => {
    const feed = await parser.parseURL(f.url);
    return (feed.items || []).map((it) => ({
      title: cleanText(it.title || ''),
      source: f.name,
      publishedAt: it.isoDate || it.pubDate || null,
      url: it.link || '',
    })).filter((it) => it.title);
  }));

  const items = [];
  results.forEach((r, i) => {
    if (r.status === 'fulfilled') {
      items.push(...r.value);
    } else {
      log.warn(`[news:${FEEDS[i].name}] fetch failed: ${r.reason?.message}`);
    }
  });

  if (items.length === 0) {
    throw new Error('all news feeds failed');
  }

  items.sort((a, b) => {
    const ta = a.publishedAt ? Date.parse(a.publishedAt) : 0;
    const tb = b.publishedAt ? Date.parse(b.publishedAt) : 0;
    return tb - ta;
  });

  return { items: items.slice(0, MAX_ITEMS) };
}

function cleanText(s) {
  return s.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
}
