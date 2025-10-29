#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Days Battery Deployment Script${NC}"
echo "=================================="

# Detect Docker Compose command (support both old and new syntax)
if docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
    echo -e "${GREEN}✅ Detected: docker compose (Docker CLI plugin)${NC}"
elif command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
    echo -e "${GREEN}✅ Detected: docker-compose (standalone)${NC}"
else
    echo -e "${RED}❌ Error: Neither 'docker compose' nor 'docker-compose' found!${NC}"
    echo "Please install Docker and Docker Compose first."
    exit 1
fi
echo ""

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
$DOCKER_COMPOSE -f docker-compose.prod.yml down || true

# Pull latest changes (if running from server)
if [ -d .git ]; then
    echo -e "${YELLOW}📥 Pulling latest changes from git...${NC}"
    git pull
fi

# Build and start containers
echo -e "${YELLOW}🔨 Building Docker images...${NC}"
$DOCKER_COMPOSE -f docker-compose.prod.yml build --no-cache

echo -e "${YELLOW}🚀 Starting containers...${NC}"
$DOCKER_COMPOSE -f docker-compose.prod.yml up -d

# Wait for services to be ready
echo -e "${YELLOW}⏳ Waiting for services to start...${NC}"
sleep 10

# Check container status
echo -e "${YELLOW}📊 Container status:${NC}"
$DOCKER_COMPOSE -f docker-compose.prod.yml ps

# Show logs
echo ""
echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo -e "${GREEN}Your application should be available at: http://${DOMAIN}${NC}"
echo ""
echo "Useful commands:"
echo "  - View logs:    $DOCKER_COMPOSE -f docker-compose.prod.yml logs -f"
echo "  - Stop:         $DOCKER_COMPOSE -f docker-compose.prod.yml down"
echo "  - Restart:      $DOCKER_COMPOSE -f docker-compose.prod.yml restart"
echo "  - Shell:        $DOCKER_COMPOSE -f docker-compose.prod.yml exec php sh"
echo ""
echo -e "${YELLOW}Note: Application is running on HTTP (port 80) without SSL${NC}"
