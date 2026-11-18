# Deploy do PetMotion na VPS do ToLembrado

O PetMotion (API + site + monitor) roda isolado na mesma VPS do ToLembrado e entra pela **mesma** conexão da Cloudflare (túnel), sem abrir porta nenhuma.

| | ToLembrado (não mexer) | PetMotion |
|---|---|---|
| Projeto Docker | `tolembrado-api`, `tolembrado-app` | `petmotion` |
| Pasta na VPS | `/opt/api`, monitor | `/opt/petmotion` |
| Portas internas | 3000/3001, 5432, 6379, 8080, 9000/9001, 11434 | **8095** (nginx), **9095** (PHP-FPM), **8096** (Reverb) |
| Portas publicadas na VPS | nenhuma | **nenhuma** |
| Rede | `tolembrado` + interna própria | interna própria; só `petmotion-web` entra na `tolembrado` |
| Banco | Postgres | SQLite no volume `petmotion_storage` |

## Primeira vez (na VPS)

```bash
sudo git clone https://github.com/GabrielCordeiroBarrosoTeles/petmotion-api.git /opt/petmotion
cd /opt/petmotion/infra
cp .env.example .env
# gere a chave e cole em APP_KEY do .env:
docker compose run --rm --no-deps --entrypoint php petmotion-php artisan key:generate --show
nano .env            # APP_KEY e, se quiser, LEGAL_* (contato das páginas legais)
./deploy.sh --seed   # sobe e cria os dados de demonstração
```

### Ligar o subdomínio no túnel (painel da Cloudflare)

O `cloudflared` do ToLembrado usa token, então a rota é configurada no painel:
**Zero Trust → Networks → Tunnels → (túnel do ToLembrado) → Public Hostname → Add**

| Campo | Valor |
|---|---|
| Subdomain | `petmotion` |
| Domain | `tolembrado.com.br` |
| Service | `HTTP` → `petmotion-web:8095` |

A Cloudflare cria o DNS sozinha. Teste: `https://petmotion.tolembrado.com.br/api/health` deve responder `{"status":"ok",...}`.

### Apontar o app para o servidor

No `petmotion-app/.env`: `EXPO_PUBLIC_API_URL=https://petmotion.tolembrado.com.br/api` e reinicie o `npx expo start`. A partir daí o app funciona em qualquer rede (4G, Wi-Fi da faculdade).

## Passeio ao vivo (Reverb)

O Reverb é o servidor de WebSocket do Laravel: é ele que leva o passeio do passeador para a tela do pai/mãe de pet em tempo real. Roda no container `petmotion-reverb` (mesma imagem do PHP, porta interna **8096**, só na rede `interno`, nada publicado na VPS). O nginx do `petmotion-web` repassa `/app` e `/apps` para ele, então o túnel da Cloudflare continua apontando só para `petmotion-web:8095` (o túnel já aceita WebSocket).

Chaves no `infra/.env` (o `./deploy.sh` gera `REVERB_APP_ID`, `REVERB_APP_KEY` e `REVERB_APP_SECRET` se estiverem vazios e **nunca** troca um valor que já existe):

| Variável | Valor |
|---|---|
| `BROADCAST_CONNECTION` | `reverb` |
| `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` | gerados com `openssl rand -hex` (veja o `.env.example`) |
| `REVERB_HOST` / `REVERB_PORT` / `REVERB_SCHEME` | `petmotion.tolembrado.com.br` / `443` / `https` (o que o app usa) |
| `REVERB_SERVER_HOST` / `REVERB_SERVER_PORT` | `0.0.0.0` / `8096` (onde o Reverb escuta) |
| `REVERB_PUBLISH_HOST` / `_PORT` / `_SCHEME` | `petmotion-reverb` / `8096` / `http` (o Laravel publica direto no container, sem sair da rede) |

O app precisa da **chave pública** (`REVERB_APP_KEY`) no `EXPO_PUBLIC_REVERB_KEY`. O segredo nunca sai da VPS.

Conferir que o WebSocket responde (de qualquer máquina com `websocat`, ou `npx wscat`):

```bash
KEY=$(grep ^REVERB_APP_KEY= /opt/petmotion/infra/.env | cut -d= -f2)
websocat "wss://petmotion.tolembrado.com.br/app/$KEY"
# resposta esperada: {"event":"pusher:connection_established","data":"{\"socket_id\":\"...\",\"activity_timeout\":30}"}
```

## Atualizar

```bash
cd /opt/petmotion/infra && ./deploy.sh
```

## Tirar depois do trabalho

```bash
cd /opt/petmotion/infra && docker compose down -v   # containers + volume (apaga o banco)
docker image rm petmotion-petmotion-php petmotion-petmotion-web petmotion-petmotion-reverb
sudo rm -rf /opt/petmotion
```
E no painel da Cloudflare apague o Public Hostname `petmotion.tolembrado.com.br` (o DNS sai junto). O ToLembrado não percebe nada.

## Testado antes de subir

No Mac, numa rede local chamada `tolembrado` (igual à da VPS), acessando `http://petmotion-web:8095` com os cabeçalhos do túnel: health OK, site, monitor, páginas legais, login da API, upload e entrega da foto do pet, links `https://` corretos, nenhuma porta publicada e imagem sem `.env`, banco, testes ou pacotes de desenvolvimento.
