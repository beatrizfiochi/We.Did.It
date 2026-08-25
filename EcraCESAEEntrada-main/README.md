# Ecrã CESAE Digital — entrada

Display informativo para a entrada do CESAE Digital Porto. Pensado para correr 24/7 num Mac Mini ligado a uma TV 58", em modo Chrome kiosk.

## O que mostra

- **Logo + relógio + data** em português
- **Meteorologia atual** + previsão a 5 dias (Open-Meteo)
- **Playlist YouTube** em autoplay sem som
- **Próximos cursos** em destaque scrapados de cesaedigital.pt (carrossel)
- **Transportes**: tempos previstos de Metro (Casa da Música) e STCP (Pasteleira/Salazares)
- **Notícias** em ticker (RTP + Público)

## Arrancar em dev

```bash
npm install
npm start
# abrir http://localhost:3000
```

`npm run dev` faz watch do `server.js`.

## Stack

- Node 18+ (fetch nativo, AbortSignal.timeout)
- Backend: `express`, `cheerio`, `rss-parser` (3 deps apenas)
- Frontend: HTML/CSS/JS vanilla, ES modules nativos — sem bundler
- Cache stale-while-revalidate no backend + segunda camada em localStorage no frontend

## Estrutura

```
server.js              # Express + warm-up
src/
  cache.js             # núcleo do stale-while-revalidate
  routes/api.js
  sources/             # weather, news, courses, metro, stcp
public/
  index.html
  styles/              # reset, tokens, layout, components, fonts
  scripts/             # módulos por concern + lib/
  assets/              # logo, fonts (Inter), ícones meteo
deploy/                # plists launchd, script kiosk, INSTALL.md
```

## Deploy

Ver [`deploy/INSTALL.md`](deploy/INSTALL.md).

## Endpoints

| Endpoint | Refresh | Stale ceiling |
|---|---|---|
| `GET /api/weather`      | 10 min | 6 h |
| `GET /api/news`         | 5 min  | 1 h |
| `GET /api/courses`      | 30 min | 24 h |
| `GET /api/transit/metro`| 60 s   | n/a |
| `GET /api/transit/stcp` | 30 s   | 10 min |
| `GET /api/health`       | —      | — |

Cada resposta tem o envelope:

```json
{
  "data": { ... },
  "fetchedAt": "2026-05-13T08:44:00.000Z",
  "ageMs": 1234,
  "stale": false,
  "error": null,
  "source": "weather"
}
```

## Resiliência

- Routes nunca esperam pela rede (sempre devolvem cache, refresh em background)
- Se uma fonte morre, mantém-se o último valor com `stale: true`
- Frontend tem 2ª camada de cache em `localStorage` — sobrevive a restart do servidor
- Recarrega o browser todos os dias às 04:00 para limpar memória
- Refresh do iframe YouTube a cada 8 h
- Erros nunca aparecem na UI — só no console
