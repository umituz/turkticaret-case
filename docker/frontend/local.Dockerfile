# Use Node.js 20 Alpine for smaller image size
FROM node:20-alpine AS base

# Install dependencies only when needed
FROM base AS deps
# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat
WORKDIR /app

# Copy package files
COPY next/package*.json ./
RUN npm ci

# Development image, copy all the files and run dev server
FROM base AS runner
WORKDIR /app

ARG PROJECT_NAME
ENV PROJECT_NAME=${PROJECT_NAME}

# Set Node environment to development
ENV NODE_ENV development
ENV NEXT_TELEMETRY_DISABLED 1

# Create non-root user
ARG HOST_UID=1001
ARG HOST_GID=1001
RUN addgroup --system --gid $HOST_GID nodejs
RUN adduser --system --uid $HOST_UID nextjs

# Copy node modules from deps stage
COPY --from=deps /app/node_modules ./node_modules
# Copy source code
COPY --chown=nextjs:nodejs next/ .

# Set correct permissions
RUN chown -R nextjs:nodejs /app

USER nextjs

# Expose port
EXPOSE 3000

ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

# Start development server
CMD ["npm", "run", "dev"]