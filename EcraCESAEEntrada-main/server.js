import express from 'express';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import apiRouter, { warmUp } from './src/routes/api.js';
import { log } from './src/logger.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const PORT = parseInt(process.env.PORT || '3000', 10);
const HOST = process.env.HOST || '127.0.0.1';

const app = express();
app.disable('x-powered-by');

app.use(express.static(path.join(__dirname, 'public'), {
  maxAge: '1h',
  etag: true,
  index: 'index.html',
}));

app.use('/api', apiRouter);

app.use((err, _req, res, _next) => {
  log.error('unhandled error', err);
  res.status(500).json({ error: 'internal' });
});

const server = app.listen(PORT, HOST, async () => {
  log.info(`server listening on http://${HOST}:${PORT}`);
  log.info('warming caches…');
  const results = await warmUp();
  results.forEach((r) => {
    if (r.status === 'fulfilled') {
      const v = r.value;
      log.info(`[warm] ${v.k}: ${v.ok ? 'ok' : 'FAIL'} (${v.ms}ms)${v.err ? ' — ' + v.err : ''}`);
    }
  });
  log.info('warm-up complete');
});

function shutdown(sig) {
  log.info(`received ${sig}, shutting down`);
  server.close(() => process.exit(0));
  setTimeout(() => process.exit(1), 5000).unref();
}
process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));
process.on('unhandledRejection', (e) => log.error('unhandledRejection', e));
