import * as cheerio from 'cheerio';

const URL = 'https://www.cesaedigital.pt/fldrSite/pages/coursesList.aspx';
const BASE = 'https://www.cesaedigital.pt';

export async function fetchCourses() {
  const res = await fetch(URL, {
    headers: { 'User-Agent': 'CesaeKioskBot/1.0', 'Accept-Language': 'pt-PT,pt;q=0.9' },
    signal: AbortSignal.timeout(8000),
  });
  if (!res.ok) throw new Error(`cesae HTTP ${res.status}`);
  const html = await res.text();
  const $ = cheerio.load(html);

  const items = [];
  $('article a[href^="/curso/lista/"]').each((_, el) => {
    try {
      const $a = $(el);
      const href = $a.attr('href') || '';
      const url = href.startsWith('http') ? href : BASE + href;
      const imageSrc = $a.find('img').attr('src') || '';
      const imageUrl = imageSrc.startsWith('http') ? imageSrc : (imageSrc ? BASE + imageSrc : null);
      const labelText = $a.find('.label').text().trim();
      const title = $a.find('h1').text().trim();
      const startDate = $a.find('.date').text().trim();
      const price = $a.find('.price').text().trim();
      const status = $a.find('.text-holder > div').last().text().trim();

      const [location, schedule] = parseLabel(labelText);

      if (title) {
        items.push({ title, imageUrl, location, schedule, startDate, price, status, url });
      }
    } catch {
      // skip broken card
    }
  });

  if (items.length === 0) throw new Error('cesae: no courses parsed');
  return { items };
}

function parseLabel(s) {
  if (!s) return [null, null];
  const parts = s.split(',').map((p) => p.trim()).filter(Boolean);
  if (parts.length === 0) return [null, null];
  if (parts.length === 1) return [parts[0], null];
  return [parts[0], parts.slice(1).join(', ')];
}
