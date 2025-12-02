#!/bin/bash

# AWS Public ECR Push Script for Registration App

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
REPOSITORY_NAME="registration-app"
IMAGE_TAG="${IMAGE_TAG:-latest}"

echo -e "${GREEN}🚀 Starting AWS Public ECR Push Process${NC}"
echo "==========================================="

# Step 1: Check if AWS CLI is installed
echo -e "\n${YELLOW}Step 1: Checking AWS CLI...${NC}"
if ! command -v aws &> /dev/null; then
    echo -e "${RED}❌ AWS CLI is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ AWS CLI is installed${NC}"

# Step 2: Check if Docker is running
echo -e "\n${YELLOW}Step 2: Checking Docker...${NC}"
if ! docker info &> /dev/null; then
    echo -e "${RED}❌ Docker is not running${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker is running${NC}"

# Step 3: Get AWS Account ID
echo -e "\n${YELLOW}Step 3: Getting AWS Account ID...${NC}"
AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text 2>/dev/null)
if [ -z "$AWS_ACCOUNT_ID" ]; then
    echo -e "${RED}❌ Failed to get AWS Account ID${NC}"
    exit 1
fi
echo -e "${GREEN}✓ AWS Account ID: $AWS_ACCOUNT_ID${NC}"

# Step 4: Get or create public repository
echo -e "\n${YELLOW}Step 4: Checking/Creating Public ECR repository...${NC}"
echo -e "${YELLOW}Note: Public ECR only works in us-east-1 region${NC}"

# Check if repository exists
REPO_INFO=$(aws ecr-public describe-repositories \
    --repository-names $REPOSITORY_NAME \
    --region us-east-1 2>/dev/null || echo "")

if [ -z "$REPO_INFO" ]; then
    echo "Creating public ECR repository: $REPOSITORY_NAME"
    aws ecr-public create-repository \
        --repository-name $REPOSITORY_NAME \
        --region us-east-1
    echo -e "${GREEN}✓ Repository created${NC}"
else
    echo -e "${GREEN}✓ Repository already exists${NC}"
fi

# Get repository URI
REPO_URI=$(aws ecr-public describe-repositories \
    --repository-names $REPOSITORY_NAME \
    --region us-east-1 \
    --query 'repositories[0].repositoryUri' \
    --output text)

echo -e "${GREEN}✓ Repository URI: $REPO_URI${NC}"

# Step 5: Login to Public ECR
echo -e "\n${YELLOW}Step 5: Authenticating with Public ECR...${NC}"
aws ecr-public get-login-password --region us-east-1 | \
    docker login --username AWS --password-stdin public.ecr.aws
echo -e "${GREEN}✓ Successfully authenticated${NC}"

# Step 6: Build Docker image
echo -e "\n${YELLOW}Step 6: Building Docker image (AMD64 architecture)...${NC}"
docker build --platform linux/amd64 -t $REPOSITORY_NAME:$IMAGE_TAG .
echo -e "${GREEN}✓ Image built successfully${NC}"

# Step 7: Tag image for Public ECR
echo -e "\n${YELLOW}Step 7: Tagging image...${NC}"
docker tag $REPOSITORY_NAME:$IMAGE_TAG $REPO_URI:$IMAGE_TAG
echo -e "${GREEN}✓ Image tagged: $REPO_URI:$IMAGE_TAG${NC}"

# Step 8: Push to Public ECR
echo -e "\n${YELLOW}Step 8: Pushing image to Public ECR...${NC}"
docker push $REPO_URI:$IMAGE_TAG
echo -e "${GREEN}✓ Image pushed successfully${NC}"

# Summary
echo -e "\n${GREEN}==========================================="
echo "✅ SUCCESS! Image pushed to Public ECR"
echo "===========================================${NC}"
echo ""
echo "Repository URI: $REPO_URI:$IMAGE_TAG"
echo ""
echo -e "${GREEN}🌍 This is a PUBLIC image - anyone can pull it!${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Update your Kubernetes deployment to use: $REPO_URI:$IMAGE_TAG"
echo "2. No authentication needed - it's public!"
echo ""
echo -e "${YELLOW}To pull this image (no auth needed):${NC}"
echo "docker pull $REPO_URI:$IMAGE_TAG"
