# Fintech Wallet Challenge

Uma aplicação de carteira digital construída com Laravel 13, Vue 3, Inertia, e Sanctum, com suporte completo a Docker e deploy automatizado no Render.

## 🚀 Acesso em Produção

**URL**: [https://fintech-wallet-challenge.onrender.com](https://fintech-wallet-challenge.onrender.com)

## 🏗️ Decisões Técnicas

### Service & Repository Pattern
A arquitetura utiliza o padrão **Service + Repository** para separação de responsabilidades:

- **Repository**: Encapsula toda a lógica de acesso aos dados, permitindo trocar a fonte de dados sem impactar a regra de negócio.
- **Service**: Contém a lógica de negócio complexa (transferências, validações de saldo, criação de extratos), mantendo os controllers enxutos.

Benefícios:
- Código mais testável e reutilizável
- Facilita manutenção e evolução
- Desacoplamento entre controllers e banco de dados

### Laravel Sanctum
Autenticação stateful para SPA (Single Page Application) com suporte a sessões e cookies:

- **Sessões de Arquivo**: Para evitar dependência de banco durante o bootstrap
- **CSRF Protection**: Integrado automaticamente para segurança contra ataques cross-site
- **First-party SPA**: O frontend e backend rodam no mesmo domínio, permitindo autenticação via sessão

Fluxo:
1. Frontend faz GET `/sanctum/csrf-cookie` antes do login
2. Login via POST `/login` com credenciais
3. Requisições subsequentes usam a sessão via cookies

### Inertia + Vue 3
Stack moderna que combina:
- **Inertia.js**: Elimina a necessidade de REST API explícita, compartilhando dados entre backend e frontend
- **Vue 3**: Reatividade, componentes, melhor experiência de desenvolvimento
- **Vite**: Build tool ultrarrápido para desenvolvimento

## 📋 Pré-requisitos

### Desenvolvimento Local

- **PHP 8.3+** com extensões: pdo_pgsql, mbstring, xml, curl, tokenizer, zip, bcmath, intl
- **Node 22+** para o Vite e npm
- **PostgreSQL 13+** para o banco de dados
- **Composer 2+** para gerenciar dependências PHP
- **Docker & Docker Compose** (opcional, para ambiente isolado)

### Mínimo Sistema Operacional
- **Windows, macOS ou Linux** com suporte a Docker

## 🛠️ Setup Local

### 1. Clonar o Repositório
```bash
git clone <seu-repositorio>
cd teck
```

### 2. Configurar Variáveis de Ambiente
```bash
cp .env.example .env
```

Edite o `.env` com suas credenciais de banco:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=teck
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
APP_ENV=local
APP_DEBUG=true
```

### 3. Instalar Dependências
```bash
composer install
npm install
```

### 4. Gerar Chave da Aplicação
```bash
php artisan key:generate
```

### 5. Executar Migrations
```bash
php artisan migrate
```

### 6. Executar Seeders (Opcional)
```bash
php artisan db:seed
```

Isso criará usuários de teste com a senha padrão: `password`

### 7. Iniciar o Servidor de Desenvolvimento

**Terminal 1 - Backend (Laravel)**
```bash
php artisan serve
```
O servidor estará disponível em `http://localhost:8000`

**Terminal 2 - Frontend (Vite)**
```bash
npm run dev
```
O Vite estará disponível em `http://localhost:5173` com HMR ativo.

Acesse `http://localhost:8000` no navegador.

## 🐳 Setup com Docker

### Build e Inicialização
```bash
docker compose build
docker compose up
```

A aplicação estará disponível em:
- **App**: `http://localhost:8000`
- **Adminer (BD)**: `http://localhost:8081`

O container executa automaticamente:
- Migrations (`php artisan migrate --force`)
- Seeders (`php artisan db:seed`)
- Builder do Vite (`npm run build`)
- Servidor PHP (`php -S 0.0.0.0:10000 -t public`)

## 📚 Fluxo de Migração & Seed

### Migrations
Atualizar esquema do banco:
```bash
php artisan migrate
```

Reverter última migration:
```bash
php artisan migrate:rollback
```

Resetar e recriar tudo:
```bash
php artisan migrate:reset
php artisan migrate
```

### Seeders
Executar seeders específicos:
```bash
php artisan db:seed --class=DatabaseSeeder
```

Executar um seeder durante migration fresh:
```bash
php artisan migrate:fresh --seed
```

## 👥 Usuários Padrão do Seed

Todos os usuários criados pela seed `DatabaseSeeder` têm a mesma senha:

**Senha**: `password`

Acesse normalmente com email e senha via `/login`.

## 🚀 Deploy no Render

### Configuração

1. Conectar repositório GitHub/GitLab ao Render
2. Criar um novo **Web Service**
3. Usar `Dockerfile` desta aplicação
4. Configurar variáveis de ambiente:
   ```
   APP_ENV=production
   APP_URL=https://fintech-wallet-challenge.onrender.com
   DB_HOST=<seu-postgres-render>
   DB_USERNAME=<usuario>
   DB_PASSWORD=<senha>
   SESSION_DOMAIN=
   ```

5. Start Command: (deixar vazio, o Dockerfile já tem CMD)
6. Health Check Path: `/up`
7. Port: `10000`

### Deploy Automático
Cada push para a branch principal dispara rebuild e deploy automaticamente.

O container executa na inicialização:
- Migrations com reset
- Seeders
- Build dos assets Vite
- Servidor PHP na porta 10000

## 🔑 Autenticação & Acesso à API

### Login
```bash
POST /login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "password"
}
```

### Endpoints Protegidos
Todos os endpoints `/api/*` exigem autenticação via `auth:sanctum`:

```bash
GET /api/user
GET /api/ultimas-transferencias
GET /api/extrato-completo?tipo=credito&data_inicio=2026-03-01&data_fim=2026-03-31
POST /api/transferencia
```

## 📁 Estrutura do Projeto

```
teck/
├── app/
│   ├── Http/Controllers/       # Controllers (thin)
│   ├── Services/               # Business logic
│   ├── Repositories/           # Data access
│   └── Models/                 # Eloquent models
├── database/
│   ├── migrations/             # Schema
│   └── seeders/                # Dados iniciais
├── resources/
│   ├── js/                     # Vue + Inertia
│   ├── css/                    # Tailwind
│   └── views/                  # Blade templates
├── docker/
│   └── start.sh                # Script de inicialização
├── Dockerfile                  # Imagem para produção
├── compose.yaml                # Local com Docker Compose
└── vite.config.js              # Config do Vite
```

## 🐛 Troubleshooting

### Mixed Content Error (HTTPS)
Se ver "Mixed Content" no navegador:
1. Confirme `APP_ENV=production`
2. Confirme `APP_URL=https://...`
3. Execute `php artisan config:clear`
4. Redeploy

### Porta 10000 em Desenvolvimento
Se quiser usar a mesma porta que o Render:
```bash
php artisan serve --port=10000
```

### Conexão com Banco Recusada
Verifique:
- PostgreSQL está rodando
- Credenciais no `.env` estão corretas
- Host/port apontam para o servidor certo

## 📝 Variáveis de Ambiente Importantes

| Variável         | Local                   | Produção                                        |
| ---------------- | ----------------------- | ----------------------------------------------- |
| `APP_ENV`        | `local`                 | `production`                                    |
| `APP_DEBUG`      | `true`                  | `false`                                         |
| `APP_URL`        | `http://localhost:8000` | `https://fintech-wallet-challenge.onrender.com` |
| `SESSION_DRIVER` | `file`                  | `file`                                          |
| `SESSION_DOMAIN` | `localhost`             | (vazio)                                         |

## 📞 Suporte

Para dúvidas ou issues, revisar logs:

Local:
```bash
tail -f storage/logs/laravel.log
```

Render:
```bash
# Acesse o dashboard do serviço
# Logs aba "Logs"
```

---

**Última atualização**: Março 2026

