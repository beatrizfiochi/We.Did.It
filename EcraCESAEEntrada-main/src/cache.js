import { log } from './logger.js';

export function makeCache({ key, ttlMs, staleAfterMs, fetcher }) {
  let state = {
    value: null,
    fetchedAt: null,
    lastError: null,
  };
  let inflight = null;

  async function refresh() {
    if (inflight) return inflight;
    const started = Date.now();
    inflight = (async () => {
      try {
        const value = await fetcher();
        state = { value, fetchedAt: Date.now(), lastError: null };
        log.info(`[cache:${key}] refreshed in ${Date.now() - started}ms`);
        return value;
      } catch (err) {
        state.lastError = err.message || String(err);
        log.warn(`[cache:${key}] refresh failed: ${state.lastError}`);
        throw err;
      } finally {
        inflight = null;
      }
    })();
    return inflight;
  }

  function age() {
    return state.fetchedAt ? Date.now() - state.fetchedAt : Infinity;
  }

  async function get() {
    const a = age();

    if (state.value === null) {
      try {
        await refresh();
      } catch {
        // surfaced below as null + stale + error
      }
      return envelope();
    }

    if (a > ttlMs) {
      refresh().catch(() => {});
    }
    return envelope();
  }

  function envelope() {
    const a = age();
    const isStale = state.value === null || a > (staleAfterMs ?? ttlMs);
    return {
      data: state.value,
      fetchedAt: state.fetchedAt ? new Date(state.fetchedAt).toISOString() : null,
      ageMs: state.fetchedAt ? a : null,
      stale: isStale,
      error: state.lastError,
      source: key,
    };
  }

  function status() {
    return {
      key,
      fetchedAt: state.fetchedAt ? new Date(state.fetchedAt).toISOString() : null,
      ageMs: state.fetchedAt ? age() : null,
      hasValue: state.value !== null,
      lastError: state.lastError,
    };
  }

  return { get, refresh, status, _key: key };
}
