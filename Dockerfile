# =============================================================================
# Dockerfile — Build multi-stage de produção para o Sincetur
# Stage 1 (deps):    Instala dependências
# Stage 2 (builder): Gera o cliente Prisma e cria o build Next.js standalone
# Stage 3 (runner):  Imagem mínima de execução
# =============================================================================

# ---------------------------------------------------------------------------
# Stage 1: deps — instalar dependências
# ---------------------------------------------------------------------------
FROM oven/bun:1 AS deps
WORKDIR /app

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

# ---------------------------------------------------------------------------
# Stage 2: builder — build Next.js standalone
# ---------------------------------------------------------------------------
FROM oven/bun:1 AS builder
WORKDIR /app

COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Gerar cliente Prisma antes do build
RUN bunx prisma generate

# Build de produção (output: standalone)
RUN bun run build

# ---------------------------------------------------------------------------
# Stage 3: runner — imagem mínima para produção
# ---------------------------------------------------------------------------
FROM oven/bun:1-slim AS runner
WORKDIR /app

ENV NODE_ENV=production

# Criar grupo e utilizador sem privilégios
RUN addgroup --system --gid 1001 nodejs \
    && adduser --system --uid 1001 nextjs

# Copiar artefactos do build standalone
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static
COPY --from=builder --chown=nextjs:nodejs /app/public ./public

USER nextjs

EXPOSE 3000

CMD ["bun", "server.js"]
