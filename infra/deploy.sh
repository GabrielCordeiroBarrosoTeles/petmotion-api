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
