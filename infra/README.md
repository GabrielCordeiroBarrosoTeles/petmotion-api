# Deploy do PetMotion na VPS do ToLembrado

O PetMotion (API + site + monitor) roda isolado na mesma VPS do ToLembrado e entra pela **mesma** conexão da Cloudflare (túnel), sem abrir porta nenhuma.

| | ToLembrado (não mexer) | PetMotion |
|---|---|---|
| Projeto Docker | `tolembrado-api`, `tolembrado-app` | `petmotion` |
| Pasta na VPS | `/opt/api`, monitor | `/opt/petmotion` |
| Portas internas | 3000/3001, 5432, 6379, 8080, 9000/9001, 11434 | **8095** (nginx), **9095** (PHP-FPM) |
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

## Atualizar

```bash
cd /opt/petmotion/infra && ./deploy.sh
```

## Tirar depois do trabalho

```bash
cd /opt/petmotion/infra && docker compose down -v   # containers + volume (apaga o banco)
docker image rm petmotion-petmotion-php petmotion-petmotion-web
sudo rm -rf /opt/petmotion
```
E no painel da Cloudflare apague o Public Hostname `petmotion.tolembrado.com.br` (o DNS sai junto). O ToLembrado não percebe nada.

## Testado antes de subir

No Mac, numa rede local chamada `tolembrado` (igual à da VPS), acessando `http://petmotion-web:8095` com os cabeçalhos do túnel: health OK, site, monitor, páginas legais, login da API, upload e entrega da foto do pet, links `https://` corretos, nenhuma porta publicada e imagem sem `.env`, banco, testes ou pacotes de desenvolvimento.
