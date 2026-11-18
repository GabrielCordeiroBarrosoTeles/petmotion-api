# PetMotion — API, site e monitor

Back-end do [PetMotion](https://github.com/GabrielCordeiroBarrosoTeles/petmotion-app), o app que transforma cada passeio do pet num mapa. Um projeto Laravel com três partes:

- **API REST** (`/api`) usada pelo app;
- **Site de apresentação** (`/`), com Política de Privacidade (`/privacidade`) e Termos de Uso (`/termos`);
- **Monitor** (`/monitor`): painel da equipe com o mapa de todos os trajetos, gráfico de atividade, ranking e vacinas vencendo.

> Trabalho da cadeira de Desenvolvimento Mobile.

## Stack

Laravel 13 · PHP 8.5 · Sanctum (token Bearer) · SQLite · Blade · Leaflet + Chart.js (monitor) · GSAP (site) · PHPUnit.

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

- Site: http://localhost:8000
- Monitor: http://localhost:8000/monitor (`admin@petmotion.app` / `petmotion`)
- Contas demo do app: `gabriel@petmotion.app`, `ana@petmotion.app` e `lucas@petmotion.app`, senha `petmotion`

## Endpoints

| Método | Rota | Uso |
|---|---|---|
| GET | `/api/health` | Health check: banco, cache e storage (200 ou 503) |
| POST | `/api/register`, `/api/login` | Cria conta / entra; devolve `{ token, user }` |
| GET · POST · DELETE | `/api/me`, `/api/logout`, `DELETE /api/me` | Sessão e exclusão de conta (com senha) |
| CRUD | `/api/pets` | Pets (foto via multipart em `POST /api/pets/{id}`) |
| GET | `/api/pets/{id}/stats` | Hoje, semana, sequência, totais, conquistas e próximos cuidados |
| GET · POST · DELETE | `/api/walks`, `/api/walks/{id}` | Passeios com trajeto e marcações |
| GET · POST · DELETE | `/api/pets/{id}/health-records`, `/api/health-records/{id}` | Carteirinha de saúde |
| GET | `/api/weather?lat=&lng=` | Clima (Open-Meteo) e estimativa da temperatura do asfalto |

Recursos de outro usuário respondem **404**. Login e cadastro têm limite de tentativas.

## Páginas legais

Os dados do responsável vêm do `.env`: `LEGAL_NAME`, `LEGAL_EMAIL`, `LEGAL_DPO_NAME`, `LEGAL_DOCUMENT` e `LEGAL_CITY`. Campo vazio aparece destacado como `[preencher …]` para não ir ao ar com contato inventado.

## SEO e GEO

Título, descrição, canonical, Open Graph, `hreflang` pt-BR, dados estruturados (MobileApplication + FAQPage), `/sitemap.xml`, `/robots.txt` e `/llms.txt` (resumo para assistentes de IA).

## Testes

```bash
php artisan test              # 63 testes
composer test:coverage        # cobertura com mínimo de 100% (precisa da extensão pcov)
```
