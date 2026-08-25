# Instalação no Mac Mini

Passo-a-passo para correr o ecrã no Mac Mini ligado à TV de 58".

## 1. Pré-requisitos

- macOS 12+ (idealmente Sonoma+)
- Google Chrome instalado em `/Applications`
- Node.js LTS (≥ 18). Instalar via [nodejs.org](https://nodejs.org) ou `brew install node`.
- Conta de utilizador dedicada (recomendado): **`ecra`** com auto-login ativo.

## 2. Clonar / copiar o projeto

Copiar a pasta `EcraCESAEEntrada/` para `/Users/ecra/EcraCESAEEntrada/`. Ou:

```bash
cd /Users/ecra
git clone <url-do-repo> EcraCESAEEntrada
cd EcraCESAEEntrada
npm ci --omit=dev
```

## 3. Auto-login + energia

System Settings:

- **Users & Groups** → Auto-login: `ecra`
- **Lock Screen** → "Start Screen Saver when inactive": Never
- **Lock Screen** → "Turn display off when inactive": Never
- **Displays** → Brightness fixed, Night Shift off
- **Notifications** → Silenciar todas para o utilizador `ecra`
- **Software Update** → Adiar updates (idealmente automatic install às 04:30 Domingos)

Terminal (uma vez):

```bash
sudo pmset -a displaysleep 0 sleep 0 disksleep 0
sudo pmset -a autorestart 1     # reinicia automaticamente após falha de corrente
```

## 4. Confirmar caminho do `node`

```bash
which node
# /usr/local/bin/node  (Intel + Homebrew) ou /opt/homebrew/bin/node (Apple Silicon)
```

Editar **`com.cesae.ecra.server.plist`** e substituir `/usr/local/bin/node` se necessário.
Substituir também os caminhos do `WorkingDirectory` e `ProgramArguments` se o projeto não estiver em `/Users/ecra/EcraCESAEEntrada`.

## 5. Instalar os LaunchAgents

```bash
mkdir -p ~/Library/LaunchAgents ~/Library/Logs/cesae-ecra
cp deploy/com.cesae.ecra.server.plist  ~/Library/LaunchAgents/
cp deploy/com.cesae.ecra.kiosk.plist   ~/Library/LaunchAgents/

launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/com.cesae.ecra.server.plist
launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/com.cesae.ecra.kiosk.plist
```

Para parar / recarregar mais tarde:

```bash
launchctl bootout  gui/$(id -u) ~/Library/LaunchAgents/com.cesae.ecra.kiosk.plist
launchctl bootout  gui/$(id -u) ~/Library/LaunchAgents/com.cesae.ecra.server.plist
launchctl kickstart -k gui/$(id -u)/com.cesae.ecra.server
```

## 6. Validar

```bash
tail -f ~/Library/Logs/cesae-ecra/server.out.log
curl -s http://localhost:3000/api/health | python3 -m json.tool
```

Espera-se ver as caches `weather`, `news`, `courses`, `metro`, `stcp` com `hasValue: true`.

## 7. Reboot

```bash
sudo reboot
```

Após o boot: auto-login → launchd arranca `server.js` → `start-kiosk.sh` espera pelo health → abre Chrome em fullscreen → caffeinate mantém o ecrã aceso.

## 8. Códigos de paragens STCP

Os códigos por defeito (`PSTL1`, `SLZR2`) são placeholders. Confirmar no site oficial:

1. Abrir [https://www.stcp.pt/pt/paragem](https://www.stcp.pt/pt/paragem)
2. Procurar "Pasteleira" ou "Salazares" perto da Rua Ciríaco Cardoso
3. Anotar o código exibido na URL ou junto à paragem
4. Editar `src/sources/stcp.js`, array `STOPS` no topo do ficheiro
5. Reiniciar o serviço: `launchctl kickstart -k gui/$(id -u)/com.cesae.ecra.server`

## 9. Atualizações futuras

```bash
cd /Users/ecra/EcraCESAEEntrada
git pull
npm ci --omit=dev
launchctl kickstart -k gui/$(id -u)/com.cesae.ecra.server
launchctl kickstart -k gui/$(id -u)/com.cesae.ecra.kiosk
```

O frontend recarrega automaticamente todos os dias às 04:00.

## Resolução de problemas

| Sintoma | Verificar |
|---|---|
| Página em branco | `tail ~/Library/Logs/cesae-ecra/server.err.log` |
| YouTube não toca | Confirmar `--autoplay-policy=no-user-gesture-required` no Chrome (script `start-kiosk.sh`) |
| Tempos de transportes estranhos | Provavelmente fallback de horário a funcionar. Ver `curl localhost:3000/api/transit/stcp` |
| Cache stale | `curl localhost:3000/api/health` mostra `ageMs` de cada fonte |
| Chrome não arranca | Confirmar caminho em `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome` |
