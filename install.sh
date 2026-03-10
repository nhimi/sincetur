#!/usr/bin/env bash
# =============================================================================
# install.sh — Instalação automática do Sincetur (Next.js + Bun + Caddy + PostgreSQL)
# Compatível com Ubuntu 22.04 LTS e Ubuntu 24.04 LTS
# Uso: sudo bash install.sh
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Variáveis configuráveis
# ---------------------------------------------------------------------------
APP_DIR="/opt/sincetur"
APP_USER="sincetur"
REPO_URL="https://github.com/nhimi/sincetur.git"
BRANCH="main"
DB_NAME="sincetur"
DB_USER="sincetur_user"
CADDY_PORT="81"
APP_PORT="3000"
BUN_PATH="/root/.bun/bin"

# ---------------------------------------------------------------------------
# Cores ANSI
# ---------------------------------------------------------------------------
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# ---------------------------------------------------------------------------
# Funções de log
# ---------------------------------------------------------------------------
log_info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
log_success() { echo -e "${GREEN}[OK]${NC}    $*"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $*" >&2; }
log_warning() { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_step()    { echo -e "\n${BOLD}${CYAN}==> $*${NC}"; }

# ---------------------------------------------------------------------------
# Trap de erro
# ---------------------------------------------------------------------------
trap 'log_error "Instalação falhou na linha $LINENO. Verifique os logs acima."; exit 1' ERR

# ---------------------------------------------------------------------------
# Verificar root
# ---------------------------------------------------------------------------
if [[ $EUID -ne 0 ]]; then
  log_error "Este script deve ser executado como root (sudo bash install.sh)"
  exit 1
fi

# ---------------------------------------------------------------------------
# Resumo de passos concluídos
# ---------------------------------------------------------------------------
STEPS_DONE=()
mark_done() { STEPS_DONE+=("$1"); }

# =============================================================================
# PASSO 1 — Pré-requisitos do sistema
# =============================================================================
log_step "Passo 1: Instalando pré-requisitos do sistema"

apt-get update -qq
apt-get install -y -qq curl git unzip ca-certificates gnupg lsb-release
log_success "Pré-requisitos instalados"
mark_done "✅ Pré-requisitos do sistema"

# =============================================================================
# PASSO 2 — Node.js 20 LTS
# =============================================================================
log_step "Passo 2: Instalando Node.js 20 LTS"

if command -v node &>/dev/null && [[ "$(node --version | cut -d. -f1 | tr -d 'v')" -ge 20 ]]; then
  log_warning "Node.js $(node --version) já está instalado — ignorando"
else
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
  log_success "Node.js $(node --version) instalado"
fi
mark_done "✅ Node.js 20 LTS"

# =============================================================================
# PASSO 3 — Bun
# =============================================================================
log_step "Passo 3: Instalando Bun"

if command -v bun &>/dev/null || [[ -f "${BUN_PATH}/bun" ]]; then
  log_warning "Bun já está instalado — ignorando"
else
  curl -fsSL https://bun.sh/install | bash
  log_success "Bun instalado"
fi

# Garantir que bun está no PATH da sessão atual
export PATH="${BUN_PATH}:${PATH}"

if ! command -v bun &>/dev/null; then
  log_error "Bun não encontrado em ${BUN_PATH}. Verifique a instalação."
  exit 1
fi
log_success "Bun $(bun --version) disponível"
mark_done "✅ Bun"

# =============================================================================
# PASSO 4 — Caddy
# =============================================================================
log_step "Passo 4: Instalando Caddy"

if command -v caddy &>/dev/null; then
  log_warning "Caddy já está instalado — ignorando"
else
  apt-get install -y debian-keyring debian-archive-keyring apt-transport-https
  curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
    | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
  curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
    | tee /etc/apt/sources.list.d/caddy-stable.list
  apt-get update -qq
  apt-get install -y caddy
  log_success "Caddy $(caddy version) instalado"
fi
mark_done "✅ Caddy"

# =============================================================================
# PASSO 5 — PostgreSQL 16
# =============================================================================
log_step "Passo 5: Instalando e configurando PostgreSQL 16"

if ! command -v psql &>/dev/null || ! psql --version | grep -q "16"; then
  # Adicionar repositório oficial do PostgreSQL
  install -d /usr/share/postgresql-common/pgdg
  curl -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc --fail \
    https://www.postgresql.org/media/keys/ACCC4CF8.asc
  sh -c 'echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] \
    https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" \
    > /etc/apt/sources.list.d/pgdg.list'
  apt-get update -qq
  apt-get install -y postgresql-16
  log_success "PostgreSQL 16 instalado"
else
  log_warning "PostgreSQL já está instalado — verificando configuração"
fi

# Iniciar PostgreSQL
systemctl start postgresql
systemctl enable postgresql

# Gerar password segura
DB_PASSWORD="$(openssl rand -base64 24 | tr -dc 'A-Za-z0-9' | head -c 24)"

# Criar utilizador e base de dados (idempotente)
if sudo -u postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname='${DB_USER}'" | grep -q 1; then
  log_warning "Utilizador '${DB_USER}' já existe — atualizando password"
  sudo -u postgres psql -c "ALTER USER ${DB_USER} WITH PASSWORD '${DB_PASSWORD}';"
else
  sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH PASSWORD '${DB_PASSWORD}';"
  log_success "Utilizador '${DB_USER}' criado"
fi

if sudo -u postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname='${DB_NAME}'" | grep -q 1; then
  log_warning "Base de dados '${DB_NAME}' já existe — ignorando"
else
  sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};"
  log_success "Base de dados '${DB_NAME}' criada"
fi

sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};"

mark_done "✅ PostgreSQL 16"

# =============================================================================
# PASSO 6 — Utilizador do sistema
# =============================================================================
log_step "Passo 6: Criando utilizador do sistema '${APP_USER}'"

if id "${APP_USER}" &>/dev/null; then
  log_warning "Utilizador '${APP_USER}' já existe — ignorando"
else
  useradd --system --no-create-home --shell /usr/sbin/nologin "${APP_USER}"
  log_success "Utilizador '${APP_USER}' criado"
fi
mark_done "✅ Utilizador do sistema"

# =============================================================================
# PASSO 7 — Clonar repositório
# =============================================================================
log_step "Passo 7: Clonando repositório para ${APP_DIR}"

if [[ -d "${APP_DIR}/.git" ]]; then
  log_warning "Repositório já existe em ${APP_DIR} — fazendo git pull"
  git -C "${APP_DIR}" fetch origin "${BRANCH}"
  git -C "${APP_DIR}" reset --hard "origin/${BRANCH}"
else
  git clone --branch "${BRANCH}" "${REPO_URL}" "${APP_DIR}"
  log_success "Repositório clonado para ${APP_DIR}"
fi
mark_done "✅ Repositório clonado"

# =============================================================================
# PASSO 8 — Ficheiro .env
# =============================================================================
log_step "Passo 8: Criando ficheiro .env"

NEXTAUTH_SECRET="$(openssl rand -base64 32)"
SERVER_IP="$(hostname -I | awk '{print $1}')"

cat > "${APP_DIR}/.env" <<EOF
NODE_ENV=production
DATABASE_URL=postgresql://${DB_USER}:${DB_PASSWORD}@localhost:5432/${DB_NAME}
NEXTAUTH_SECRET=${NEXTAUTH_SECRET}
NEXTAUTH_URL=http://${SERVER_IP}:${CADDY_PORT}
DB_PASSWORD=${DB_PASSWORD}
EOF

chmod 600 "${APP_DIR}/.env"
log_success "Ficheiro .env criado em ${APP_DIR}/.env"
mark_done "✅ Ficheiro .env"

# =============================================================================
# PASSO 9 — Instalar dependências
# =============================================================================
log_step "Passo 9: Instalando dependências com Bun"

cd "${APP_DIR}"
export PATH="${BUN_PATH}:${PATH}"
bun install --frozen-lockfile
log_success "Dependências instaladas"
mark_done "✅ Dependências"

# =============================================================================
# PASSO 10 — Migrações Prisma
# =============================================================================
log_step "Passo 10: Executando migrações Prisma"

cd "${APP_DIR}"
bunx prisma migrate deploy
log_success "Migrações executadas"
mark_done "✅ Migrações Prisma"

# =============================================================================
# PASSO 11 — Build de produção
# =============================================================================
log_step "Passo 11: Build de produção"

cd "${APP_DIR}"
bun run build
log_success "Build concluído"
mark_done "✅ Build de produção"

# =============================================================================
# PASSO 12 — Ownership
# =============================================================================
log_step "Passo 12: Configurando permissões"

chown -R "${APP_USER}:${APP_USER}" "${APP_DIR}"
# Bun precisa ser acessível pelo utilizador do sistema
chmod -R o+rX "${BUN_PATH}"

log_success "Permissões configuradas"
mark_done "✅ Permissões"

# =============================================================================
# PASSO 13 — Serviço systemd
# =============================================================================
log_step "Passo 13: Criando serviço systemd"

cat > /etc/systemd/system/sincetur.service <<EOF
[Unit]
Description=Sincetur Next.js App
After=network.target postgresql.service

[Service]
Type=simple
User=${APP_USER}
WorkingDirectory=${APP_DIR}
ExecStart=${BUN_PATH}/bun ${APP_DIR}/.next/standalone/server.js
Restart=always
RestartSec=10
Environment=NODE_ENV=production
EnvironmentFile=${APP_DIR}/.env

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable sincetur
log_success "Serviço systemd criado e habilitado"
mark_done "✅ Serviço systemd"

# =============================================================================
# PASSO 14 — Configurar Caddy
# =============================================================================
log_step "Passo 14: Configurando Caddy"

if [[ -f "${APP_DIR}/Caddyfile" ]]; then
  cp "${APP_DIR}/Caddyfile" /etc/caddy/Caddyfile
  log_info "Caddyfile copiado do repositório"
else
  cat > /etc/caddy/Caddyfile <<EOF
:${CADDY_PORT} {
    reverse_proxy localhost:${APP_PORT} {
        header_up Host {host}
        header_up X-Forwarded-For {remote_host}
        header_up X-Forwarded-Proto {scheme}
        header_up X-Real-IP {remote_host}
    }
}
EOF
  log_info "Caddyfile padrão criado"
fi

systemctl enable caddy
log_success "Caddy configurado e habilitado"
mark_done "✅ Caddy"

# =============================================================================
# PASSO 15 — Iniciar serviços
# =============================================================================
log_step "Passo 15: Iniciando serviços"

systemctl restart sincetur
systemctl restart caddy

# Aguardar arranque
sleep 3

if systemctl is-active --quiet sincetur; then
  log_success "Serviço sincetur está a correr"
else
  log_error "Serviço sincetur falhou ao iniciar"
  journalctl -u sincetur -n 30 --no-pager
  exit 1
fi

if systemctl is-active --quiet caddy; then
  log_success "Serviço caddy está a correr"
else
  log_warning "Caddy não está a correr — verifique a configuração"
fi

mark_done "✅ Serviços iniciados"

# =============================================================================
# RESUMO FINAL
# =============================================================================
echo ""
echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║           INSTALAÇÃO CONCLUÍDA COM SUCESSO! 🎉               ║${NC}"
echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BOLD}Passos concluídos:${NC}"
for step in "${STEPS_DONE[@]}"; do
  echo -e "  ${step}"
done
echo ""
echo -e "${BOLD}Acesso:${NC}"
echo -e "  🌐 URL: ${CYAN}http://${SERVER_IP}:${CADDY_PORT}${NC}"
echo ""
echo -e "${BOLD}Ficheiros importantes:${NC}"
echo -e "  📄 Env:  ${APP_DIR}/.env"
echo -e "  📄 Logs: journalctl -u sincetur -f"
echo -e "  📄 App:  ${APP_DIR}"
echo ""
echo -e "${BOLD}Comandos úteis:${NC}"
echo -e "  Ver logs:      ${YELLOW}journalctl -u sincetur -f${NC}"
echo -e "  Reiniciar:     ${YELLOW}systemctl restart sincetur${NC}"
echo -e "  Status:        ${YELLOW}systemctl status sincetur${NC}"
echo -e "  Atualizar app: ${YELLOW}bash ${APP_DIR}/deploy.sh${NC}"
echo ""
