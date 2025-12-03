#!/bin/bash

# Quick App Update Script
# Use this after making code changes

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

REGION="ap-southeast-1"
CLUSTER_NAME="registration-cluster1"
ECR_REPO="557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app"

# Generate version tag (timestamp format: YYYYMMDD-HHMMSS)
VERSION=$(date +%Y%m%d-%H%M%S)
echo -e "${GREEN}🔄 Updating Registration App${NC}"
echo "=============================="
echo -e "Version: ${YELLOW}${VERSION}${NC}\n"

# 1. Build new image
echo -e "${YELLOW}Building new image...${NC}"
docker build --platform linux/amd64 -t registration-app:${VERSION} .
echo -e "${GREEN}✓ Build complete${NC}"

# 2. Login to ECR
echo -e "\n${YELLOW}Authenticating with ECR...${NC}"
aws ecr get-login-password --region $REGION | \
    docker login --username AWS --password-stdin \
    557690595475.dkr.ecr.$REGION.amazonaws.com
echo -e "${GREEN}✓ Authenticated${NC}"

# 3. Tag and push with version AND latest
echo -e "\n${YELLOW}Pushing to ECR...${NC}"
docker tag registration-app:${VERSION} $ECR_REPO:${VERSION}
docker tag registration-app:${VERSION} $ECR_REPO:latest
docker push $ECR_REPO:${VERSION}
docker push $ECR_REPO:latest
echo -e "${GREEN}✓ Pushed version ${VERSION} to ECR${NC}"

# 4. Update Kubernetes with specific version
echo -e "\n${YELLOW}Updating Kubernetes deployment...${NC}"
kubectl set image deployment/registration-app registration-app=${ECR_REPO}:${VERSION}
echo -e "${GREEN}✓ Deployment updated to version ${VERSION}${NC}"

# 5. Wait for rollout
echo -e "\n${YELLOW}Waiting for rollout to complete...${NC}"
kubectl rollout status deployment/registration-app --timeout=5m

echo -e "\n${GREEN}=============================="
echo "✅ Update Complete!"
echo "==============================${NC}"

echo -e "\n${YELLOW}Useful commands:${NC}"
echo "  View logs:  kubectl logs -f deployment/registration-app"
echo "  Check pods: kubectl get pods"
echo "  Get URL:    kubectl get service registration-app-service"

