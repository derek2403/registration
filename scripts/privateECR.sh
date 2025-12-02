#!/bin/bash

# AWS ECR Push Script for Registration App
# This script builds and pushes your Docker image to AWS ECR

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
REGION="${AWS_REGION:-ap-southeast-1}"
REPOSITORY_NAME="registration-app"
IMAGE_TAG="${IMAGE_TAG:-latest}"

echo -e "${GREEN}🚀 Starting AWS ECR Push Process${NC}"
echo "=================================="

# Step 1: Check if AWS CLI is installed
echo -e "\n${YELLOW}Step 1: Checking AWS CLI...${NC}"
if ! command -v aws &> /dev/null; then
    echo -e "${RED}❌ AWS CLI is not installed. Please install it first:${NC}"
    echo "   brew install awscli"
    exit 1
fi
echo -e "${GREEN}✓ AWS CLI is installed${NC}"

# Step 2: Check if Docker is running
echo -e "\n${YELLOW}Step 2: Checking Docker...${NC}"
if ! docker info &> /dev/null; then
    echo -e "${RED}❌ Docker is not running. Please start Docker first.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker is running${NC}"

# Step 3: Get AWS Account ID
echo -e "\n${YELLOW}Step 3: Getting AWS Account ID...${NC}"
AWS_ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text 2>/dev/null)
if [ -z "$AWS_ACCOUNT_ID" ]; then
    echo -e "${RED}❌ Failed to get AWS Account ID. Please run 'aws configure' first.${NC}"
    exit 1
fi
echo -e "${GREEN}✓ AWS Account ID: $AWS_ACCOUNT_ID${NC}"

# Step 4: Create ECR repository if it doesn't exist
echo -e "\n${YELLOW}Step 4: Checking/Creating ECR repository...${NC}"
if ! aws ecr describe-repositories --repository-names $REPOSITORY_NAME --region $REGION &> /dev/null; then
    echo "Creating ECR repository: $REPOSITORY_NAME"
    aws ecr create-repository \
        --repository-name $REPOSITORY_NAME \
        --region $REGION \
        --image-scanning-configuration scanOnPush=true \
        --encryption-configuration encryptionType=AES256
    echo -e "${GREEN}✓ Repository created${NC}"
else
    echo -e "${GREEN}✓ Repository already exists${NC}"
fi

# Step 5: Login to ECR
echo -e "\n${YELLOW}Step 5: Authenticating with ECR...${NC}"
aws ecr get-login-password --region $REGION | docker login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$REGION.amazonaws.com
echo -e "${GREEN}✓ Successfully authenticated${NC}"

# Step 6: Build Docker image
echo -e "\n${YELLOW}Step 6: Building Docker image (AMD64 architecture)...${NC}"
docker build --platform linux/amd64 -t $REPOSITORY_NAME:$IMAGE_TAG .
echo -e "${GREEN}✓ Image built successfully${NC}"

# Step 7: Tag image for ECR
echo -e "\n${YELLOW}Step 7: Tagging image...${NC}"
ECR_IMAGE="$AWS_ACCOUNT_ID.dkr.ecr.$REGION.amazonaws.com/$REPOSITORY_NAME:$IMAGE_TAG"
docker tag $REPOSITORY_NAME:$IMAGE_TAG $ECR_IMAGE
echo -e "${GREEN}✓ Image tagged: $ECR_IMAGE${NC}"

# Step 8: Push to ECR
echo -e "\n${YELLOW}Step 8: Pushing image to ECR...${NC}"
docker push $ECR_IMAGE
echo -e "${GREEN}✓ Image pushed successfully${NC}"

# Summary
echo -e "\n${GREEN}=================================="
echo "✅ SUCCESS! Image pushed to ECR"
echo "==================================${NC}"
echo ""
echo "Repository URI: $ECR_IMAGE"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Update your Kubernetes deployment to use the ECR image"
echo "2. Configure your EKS cluster to pull from ECR"
echo ""
echo -e "${YELLOW}To pull this image:${NC}"
echo "docker pull $ECR_IMAGE"
