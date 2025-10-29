#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Days Battery Deployment Script${NC}"
echo "=================================="

# Check if .env.prod exists
if [ ! -f .env.prod ]; then
    echo -e "${RED}❌ Error: .env.prod file not found!${NC}"
    echo "Please create .env.prod file with your production configuration."
    exit 1
fi

# Load environment variables
set -a
source .env.prod
set +a

# Validate required variables
if [ -z "$DOMAIN" ] || [ "$DOMAIN" = "YOUR_DOMAIN_HERE" ]; then
    echo -e "${RED}❌ Error: DOMAIN is not set in .env.prod${NC}"
    exit 1
fi

if [ -z "$LETSENCRYPT_EMAIL" ] || [ "$LETSENCRYPT_EMAIL" = "your-email@example.com" ]; then
    echo -e "${RED}❌ Error: LETSENCRYPT_EMAIL is not set in .env.prod${NC}"
    exit 1
fi

if [ -z "$APP_SECRET" ] || [ "$APP_SECRET" = "CHANGE_ME_TO_SECURE_RANDOM_STRING" ]; then
    echo -e "${RED}❌ Error: APP_SECRET is not set properly in .env.prod${NC}"
    echo "Generate a secure secret with: php -r \"echo bin2hex(random_bytes(32));\""
    exit 1
fi

echo -e "${GREEN}✅ Configuration validated${NC}"
echo -e "Domain: ${YELLOW}${DOMAIN}${NC}"
echo ""

# Stop existing containers if running
echo -e "${YELLOW}🛑 Stopping existing containers...${NC}"
docker compose -f docker-compose.prod.yml down || true

# Pull latest changes (if running from server)
if [ -d .git ]; then
    echo -e "${YELLOW}📥 Pulling latest changes from git...${NC}"
    git pull
fi

# Build and start containers
echo -e "${YELLOW}🔨 Building Docker images...${NC}"
docker compose -f docker-compose.prod.yml build --no-cache

echo -e "${YELLOW}🚀 Starting containers...${NC}"
docker compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
echo -e "${YELLOW}⏳ Waiting for services to start...${NC}"
sleep 10

# Check container status
echo -e "${YELLOW}📊 Container status:${NC}"
docker compose -f docker-compose.prod.yml ps

# Show logs
echo ""
echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo -e "${GREEN}Your application should be available at: https://${DOMAIN}${NC}"
echo ""
echo "Useful commands:"
echo "  - View logs:    docker compose -f docker-compose.prod.yml logs -f"
echo "  - Stop:         docker compose -f docker-compose.prod.yml down"
echo "  - Restart:      docker compose -f docker-compose.prod.yml restart"
echo "  - Shell:        docker compose -f docker-compose.prod.yml exec php sh"
echo ""
echo -e "${YELLOW}Note: SSL certificate may take 1-2 minutes to be issued by Let's Encrypt${NC}"
