# Sincetur — Guia de Deploy

Este documento descreve como fazer o deploy do **Sincetur** (Next.js 15 + Bun + Prisma + Caddy + PostgreSQL) em diferentes ambientes.

---

## Índice

1. [Pré-requisitos](#pré-requisitos)
2. [Deploy manual num servidor VPS](#1-deploy-manual-num-servidor-vps)
3. [Deploy com Docker](#2-deploy-com-docker)
4. [Deploy automático via GitHub Actions](#3-deploy-automático-via-github-actions)
5. [Variáveis de ambiente](#4-variáveis-de-ambiente)
6. [Configurar secrets no GitHub](#5-configurar-secrets-no-github)
7. [Comandos úteis de manutenção](#6-comandos-úteis-de-manutenção)

---

## Pré-requisitos

- Ubuntu 22.04 LTS ou 24.04 LTS
- Acesso root ou sudo ao servidor
- Git instalado localmente

---

## 1. Deploy manual num servidor VPS

### 1.1 Instalação completa (primeira vez)

Execute o script de instalação como root no servidor:

```bash
# Descarregar e executar o script
curl -fsSL https://raw.githubusercontent.com/nhimi/sincetur/main/install.sh | sudo bash

# Ou clonar o repo e executar localmente
git clone https://github.com/nhimi/sincetur.git
cd sincetur
sudo bash install.sh
```

O script `install.sh` faz automaticamente:

- Instala Node.js 20, Bun, Caddy e PostgreSQL 16
- Cria o utilizador de sistema `sincetur`
- Clona o repositório para `/opt/sincetur`
- Gera e guarda credenciais seguras em `/opt/sincetur/.env`
- Instala dependências, executa migrações Prisma e faz o build
- Cria e inicia os serviços systemd (`sincetur` e `caddy`)

### 1.2 Atualização (redeploy)

Para atualizar o código após um `git push`:

```bash
cd /opt/sincetur
sudo bash deploy.sh
```

O script `deploy.sh` executa:
1. `git pull origin main`
2. `bun install --frozen-lockfile`
3. `bunx prisma migrate deploy`
4. `bun run build`
5. `systemctl restart sincetur`

---

## 2. Deploy com Docker

### 2.1 Preparar o ficheiro `.env`

```bash
cp .env.example .env
# Editar .env com os valores reais
nano .env
```

Valores mínimos obrigatórios:

```env
NODE_ENV=production
DATABASE_URL=postgresql://sincetur_user:SENHA@postgres:5432/sincetur
NEXTAUTH_SECRET=$(openssl rand -base64 32)
NEXTAUTH_URL=http://seudominio.com
DB_PASSWORD=senha_segura_aqui
```

> **Nota:** No `docker-compose.yml`, o host da base de dados é `postgres` (nome do serviço), não `localhost`.

### 2.2 Iniciar todos os serviços

```bash
docker compose up -d
```

### 2.3 Executar migrações

```bash
docker compose exec app bunx prisma migrate deploy
```

### 2.4 Verificar estado dos serviços

```bash
docker compose ps
docker compose logs app
```

### 2.5 Parar todos os serviços

```bash
docker compose down
```

### 2.6 Rebuild após atualização de código

```bash
git pull origin main
docker compose build --no-cache
docker compose up -d
```

---

## 3. Deploy automático via GitHub Actions

O ficheiro `.github/workflows/deploy.yml` automatiza o deploy ao servidor sempre que há um `push` para o branch `main`.

### Fluxo

```
git push origin main
    └─> GitHub Actions (CI)
            └─> SSH para o servidor
                    └─> bash deploy.sh
```

### Configurar

1. Gerar um par de chaves SSH dedicado ao deploy:

```bash
ssh-keygen -t ed25519 -C "deploy@sincetur" -f ~/.ssh/sincetur_deploy -N ""
# Copiar chave pública para o servidor
ssh-copy-id -i ~/.ssh/sincetur_deploy.pub root@SEU_SERVIDOR
```

2. Adicionar os secrets no GitHub (ver [secção 5](#5-configurar-secrets-no-github))

3. Fazer um `git push` para `main` — o deploy é automático.

---

## 4. Variáveis de ambiente

| Variável | Descrição | Exemplo |
|---|---|---|
| `NODE_ENV` | Ambiente de execução | `production` |
| `DATABASE_URL` | URL de ligação ao PostgreSQL | `postgresql://user:pass@host:5432/db` |
| `NEXTAUTH_SECRET` | Segredo para JWT (mínimo 32 chars) | `openssl rand -base64 32` |
| `NEXTAUTH_URL` | URL base da aplicação | `https://seudominio.com` |
| `DB_PASSWORD` | Password do PostgreSQL (Docker Compose) | `senha_forte` |

---

## 5. Configurar secrets no GitHub

1. Aceder ao repositório no GitHub
2. Navegar para **Settings → Secrets and variables → Actions**
3. Clicar em **New repository secret** e adicionar:

| Secret | Valor |
|---|---|
| `SERVER_HOST` | IP ou hostname do servidor |
| `SERVER_USER` | Utilizador SSH (ex: `root` ou `ubuntu`) |
| `SERVER_SSH_KEY` | Conteúdo completo da chave privada (`~/.ssh/sincetur_deploy`) |
| `SERVER_PORT` | Porta SSH (opcional, padrão: `22`) |

---

## 6. Comandos úteis de manutenção

### Logs da aplicação

```bash
# Seguir logs em tempo real
journalctl -u sincetur -f

# Últimas 100 linhas
journalctl -u sincetur -n 100 --no-pager

# Logs do Docker
docker compose logs -f app
```

### Reiniciar / parar / iniciar

```bash
systemctl restart sincetur
systemctl stop sincetur
systemctl start sincetur
systemctl status sincetur
```

### Atualizar manualmente

```bash
cd /opt/sincetur
sudo bash deploy.sh
```

### Base de dados

```bash
# Aceder ao psql
sudo -u postgres psql -d sincetur

# Executar migrações manualmente
cd /opt/sincetur
bunx prisma migrate deploy

# Ver estado das migrações
bunx prisma migrate status
```

### Caddy

```bash
systemctl status caddy
systemctl reload caddy
caddy validate --config /etc/caddy/Caddyfile
```

### Docker

```bash
# Reconstruir e reiniciar
docker compose build --no-cache && docker compose up -d

# Ver todos os serviços
docker compose ps

# Entrar no container da app
docker compose exec app sh
```
