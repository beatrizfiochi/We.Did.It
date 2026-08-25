const PLAYLIST_ID = 'PLXPamj4zjdgdpHTmRPeWy5F4sNcSKZX2j';

function buildUrl() {
  const params = new URLSearchParams({
    list: PLAYLIST_ID,
    autoplay: '1',
    mute: '1',
    loop: '1',
    playlist: PLAYLIST_ID,
    controls: '0',
    modestbranding: '1',
    rel: '0',
    playsinline: '1',
    iv_load_policy: '3',
    disablekb: '1',
  });
  return `https://www.youtube-nocookie.com/embed/videoseries?${params.toString()}`;
}

export function mount() {
  const iframe = document.getElementById('yt-iframe');
  if (!iframe) return;
  iframe.src = buildUrl();
  const mountedAt = Date.now();

  // Watchdog: reset src every 8h to dodge memory leaks
  const WATCHDOG_MS = 8 * 60 * 60 * 1000;
  setInterval(() => {
    if (Date.now() - mountedAt > WATCHDOG_MS) {
      iframe.src = buildUrl();
    }
  }, 5 * 60_000);
}
