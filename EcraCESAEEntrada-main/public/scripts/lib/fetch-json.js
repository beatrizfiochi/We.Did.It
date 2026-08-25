// fetch wrapper with timeout, localStorage second-tier cache,
// and graceful stale fallback. Returns the server envelope:
// { data, fetchedAt, ageMs, stale, error, source }
// Never throws — on full failure returns { data: null, stale: true, error }.

const LS_PREFIX = 'ecra:cache:';

export async function fetchJSON(url, { timeoutMs = 6000 } = {}) {
  try {
    const res = await fetch(url, { signal: AbortSignal.timeout(timeoutMs) });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const envelope = await res.json();
    if (envelope && envelope.data !== null && envelope.data !== undefined) {
      try { localStorage.setItem(LS_PREFIX + url, JSON.stringify({ envelope, at: Date.now() })); } catch {}
    }
    return envelope;
  } catch (err) {
    const cached = readLocal(url);
    if (cached) return { ...cached.envelope, stale: true, ageMs: Date.now() - cached.at, error: err.message };
    return { data: null, stale: true, error: err.message, source: url };
  }
}

function readLocal(url) {
  try {
    const raw = localStorage.getItem(LS_PREFIX + url);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch { return null; }
}
