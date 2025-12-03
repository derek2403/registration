#!/bin/bash

# Push existing image to Public ECR
# Usage: ./pushToPublicECR.sh [version]
# Example: ./pushToPublicECR.sh 20251203-094515
# Or just: ./pushToPublicECR.sh (uses 'latest')

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Get version from argument or use 'latest'
VERSION=${1:-latest}

ECR_PUBLIC="public.ecr.aws/r4c7u3w1/registration-app"

echo -e "${GREEN}📤 Pushing to Public ECR${NC}"
echo "=============================="
echo -e "Version: ${YELLOW}${VERSION}${NC}\n"

# 1. Login to Public ECR (always uses us-east-1)
echo -e "${YELLOW}Authenticating with Public ECR...${NC}"
aws ecr-public get-login-password --region us-east-1 | \
    docker login --username AWS --password-stdin \
    public.ecr.aws
echo -e "${GREEN}✓ Authenticated${NC}"

# 2. Tag the local image for public ECR
echo -e "\n${YELLOW}Tagging image...${NC}"
if [ "$VERSION" = "latest" ]; then
    docker tag registration-app:latest $ECR_PUBLIC:latest
else
    docker tag registration-app:${VERSION} $ECR_PUBLIC:${VERSION}
    docker tag registration-app:${VERSION} $ECR_PUBLIC:latest
fi
echo -e "${GREEN}✓ Tagged${NC}"

# 3. Push to Public ECR
echo -e "\n${YELLOW}Pushing to Public ECR...${NC}"
if [ "$VERSION" = "latest" ]; then
    docker push $ECR_PUBLIC:latest
else
    docker push $ECR_PUBLIC:${VERSION}
    docker push $ECR_PUBLIC:latest
fi
echo -e "${GREEN}✓ Pushed${NC}"

echo -e "\n${GREEN}=============================="
echo "✅ Successfully pushed to Public ECR!"
echo "==============================${NC}"
echo -e "\nPublic URL: ${YELLOW}public.ecr.aws/r4c7u3w1/registration-app:${VERSION}${NC}"
