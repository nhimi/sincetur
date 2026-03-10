#!/usr/bin/env bash
# =============================================================================
# deploy.sh — Atualização e redeploy do Sincetur
# Pode ser executado múltiplas vezes — idempotente e seguro
# Uso: bash deploy.sh  (ou sudo bash deploy.sh)
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Variáveis configuráveis
# ---------------------------------------------------------------------------
APP_DIR="/opt/sincetur"
BRANCH="main"
BUN_PATH="/root/.bun/bin"
SERVICE_NAME="sincetur"

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

log_info()    { echo -e "${BLUE}[INFO]${NC}  $*"; }
log_success() { echo -e "${GREEN}[OK]${NC}    $*"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $*" >&2; }
log_warning() { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_step()    { echo -e "\n${BOLD}${CYAN}==> $*${NC}"; }

trap 'log_error "Deploy falhou na linha $LINENO."; exit 1' ERR

# Garantir que bun está no PATH
export PATH="${BUN_PATH}:${PATH}"

echo -e "${BOLD}${CYAN}════════════════════════════════════════${NC}"
echo -e "${BOLD}${CYAN}   Sincetur — Deploy $(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo -e "${BOLD}${CYAN}════════════════════════════════════════${NC}"

# =============================================================================
# 1 — Ir para o diretório da aplicação
# =============================================================================
log_step "1. Verificando diretório da aplicação"

if [[ ! -d "${APP_DIR}" ]]; then
  log_error "Diretório ${APP_DIR} não encontrado. Execute install.sh primeiro."
  exit 1
fi

cd "${APP_DIR}"
log_success "Diretório: ${APP_DIR}"

# =============================================================================
# 2 — Atualizar código
# =============================================================================
log_step "2. Atualizando código (git pull)"

git fetch origin "${BRANCH}"
git reset --hard "origin/${BRANCH}"
log_success "Código atualizado para o commit: $(git rev-parse --short HEAD)"

# =============================================================================
# 3 — Instalar dependências
# =============================================================================
log_step "3. Instalando dependências"

bun install --frozen-lockfile
log_success "Dependências instaladas"

# =============================================================================
# 4 — Migrações Prisma
# =============================================================================
log_step "4. Executando migrações Prisma"

bunx prisma migrate deploy
log_success "Migrações aplicadas"

# =============================================================================
# 5 — Build de produção
# =============================================================================
log_step "5. Build de produção"

bun run build
log_success "Build concluído"

# =============================================================================
# 6 — Reiniciar serviço
# =============================================================================
log_step "6. Reiniciando serviço ${SERVICE_NAME}"

if [[ $EUID -ne 0 ]]; then
  sudo systemctl restart "${SERVICE_NAME}"
else
  systemctl restart "${SERVICE_NAME}"
fi

sleep 2

if systemctl is-active --quiet "${SERVICE_NAME}"; then
  log_success "Serviço ${SERVICE_NAME} está a correr"
else
  log_error "Serviço ${SERVICE_NAME} falhou ao iniciar"
  journalctl -u "${SERVICE_NAME}" -n 30 --no-pager
  exit 1
fi

# =============================================================================
# 7 — Status e logs finais
# =============================================================================
log_step "7. Status do serviço"

systemctl status "${SERVICE_NAME}" --no-pager -l | head -20

echo ""
log_step "Últimas 20 linhas do log"
journalctl -u "${SERVICE_NAME}" -n 20 --no-pager

echo ""
echo -e "${BOLD}${GREEN}✅ Deploy concluído com sucesso!${NC}"
echo -e "   Commit: $(git rev-parse --short HEAD) — $(git log -1 --pretty='%s')"
echo -e "   Logs:   ${YELLOW}journalctl -u ${SERVICE_NAME} -f${NC}"
