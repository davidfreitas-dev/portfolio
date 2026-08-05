# =============================================================
# Stage 1: Dependências (cache separado)
# =============================================================
FROM node:lts-alpine AS deps
WORKDIR /app
COPY package*.json ./
RUN npm ci

# =============================================================
# Stage 2: Desenvolvimento (Vite dev server)
# =============================================================
FROM node:lts-alpine AS development
WORKDIR /app

# Instala curl para healthcheck e libc6-compat para compatibilidade
RUN apk add --no-cache curl libc6-compat

COPY --from=deps /app/node_modules ./node_modules
COPY . .
EXPOSE 5173
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
