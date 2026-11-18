#!/usr/bin/env bash
# Atualiza o PetMotion na VPS: código novo, containers, migrations.
# Uso (na VPS, em /opt/petmotion/infra):
#   ./deploy.sh           atualiza
#   ./deploy.sh --seed    primeira vez: apaga e recria o banco com os dados de demonstração
set -euo pipefail
cd "$(dirname "$0")"

[ -f .env ] || { echo "Falta infra/.env (copie de .env.example e preencha)." >&2; exit 1; }
grep -q '^APP_KEY=base64:' .env || { echo "Defina APP_KEY no infra/.env (veja o comentário no .env.example)." >&2; exit 1; }
docker network inspect tolembrado >/dev/null 2>&1 || { echo "Rede 'tolembrado' não existe: o ToLembrado está rodando?" >&2; exit 1; }

# Passeio ao vivo: preenche o que faltar do Reverb. Nunca troca um valor que já existe.
ensure_env() {  # ensure_env CHAVE VALOR
  grep -qE "^$1=.+" .env && return 0
  if grep -qE "^$1=" .env; then sed -i.bak "s|^$1=.*|$1=$2|" .env && rm -f .env.bak; else printf '%s=%s\n' "$1" "$2" >> .env; fi
}
ensure_env BROADCAST_CONNECTION reverb
ensure_env REVERB_APP_ID "$(openssl rand -hex 8)"
ensure_env REVERB_APP_KEY "$(openssl rand -hex 16)"
ensure_env REVERB_APP_SECRET "$(openssl rand -hex 32)"
ensure_env REVERB_HOST petmotion.tolembrado.com.br
ensure_env REVERB_PORT 443
ensure_env REVERB_SCHEME https
ensure_env REVERB_SERVER_HOST 0.0.0.0
ensure_env REVERB_SERVER_PORT 8096
ensure_env REVERB_PUBLISH_HOST petmotion-reverb
ensure_env REVERB_PUBLISH_PORT 8096
ensure_env REVERB_PUBLISH_SCHEME http

echo "==> Código"
git -C .. pull --ff-only

echo "==> Containers (as migrations rodam ao subir o PHP)"
docker compose up -d --build --remove-orphans

if [ "${1:-}" = "--seed" ]; then
  grep -qE '^SEED_ADMIN_PASSWORD=.{10,}' .env || { echo "Defina SEED_ADMIN_PASSWORD (10+ caracteres) no infra/.env antes do --seed." >&2; exit 1; }
  echo "==> Dados de demonstração"
  docker compose exec -T petmotion-php php artisan migrate:fresh --seed --force
  docker compose exec -T petmotion-php php artisan storage:link >/dev/null 2>&1 || true
fi

echo "==> Saúde"
docker compose exec -T petmotion-web wget -qO- http://127.0.0.1:8095/api/health || { echo "Health check falhou" >&2; exit 1; }
echo

docker image prune -f >/dev/null
docker compose ps --format 'table {{.Service}}\t{{.Status}}'
