const fmt = () => new Date().toISOString();

export const log = {
  info: (...args) => console.log(`[${fmt()}] [info]`, ...args),
  warn: (...args) => console.warn(`[${fmt()}] [warn]`, ...args),
  error: (...args) => console.error(`[${fmt()}] [error]`, ...args),
  debug: (...args) => {
    if (process.env.DEBUG) console.log(`[${fmt()}] [debug]`, ...args);
  },
};
