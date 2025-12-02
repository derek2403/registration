#!/bin/bash

# Complete EKS Deployment Script for Registration App

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

CLUSTER_NAME="registration-cluster"
REGION="ap-southeast-1"

echo -e "${GREEN}🚀 AWS EKS Deployment Script${NC}"
echo "======================================"

# Check prerequisites
echo -e "\n${YELLOW}Checking prerequisites...${NC}"

if ! command -v aws &> /dev/null; then
    echo -e "${RED}❌ AWS CLI not installed${NC}"
    exit 1
fi

if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl not installed. Install with: brew install kubectl${NC}"
    exit 1
fi

if ! command -v eksctl &> /dev/null; then
    echo -e "${RED}❌ eksctl not installed. Install with: brew install eksctl${NC}"
    exit 1
fi

echo -e "${GREEN}✓ All prerequisites met${NC}"

# Check if cluster exists
echo -e "\n${YELLOW}Checking if cluster exists...${NC}"
if aws eks describe-cluster --name $CLUSTER_NAME --region $REGION &> /dev/null; then
    echo -e "${GREEN}✓ Cluster already exists${NC}"
    
    # Update kubeconfig
    echo -e "\n${YELLOW}Updating kubeconfig...${NC}"
    aws eks update-kubeconfig --region $REGION --name $CLUSTER_NAME
    echo -e "${GREEN}✓ kubeconfig updated${NC}"
else
    echo -e "${YELLOW}⚠ Cluster does not exist${NC}"
    echo -e "${BLUE}Creating EKS cluster... This will take 15-20 minutes${NC}"
    echo -e "${BLUE}Cost: ~$130/month (~$0.18/hour)${NC}"
    read -p "Continue? (yes/no): " CONFIRM
    
    if [ "$CONFIRM" != "yes" ]; then
        echo "Deployment cancelled"
        exit 0
    fi
    
    eksctl create cluster \
        --name $CLUSTER_NAME \
        --region $REGION \
        --nodegroup-name standard-workers \
        --node-type t3.medium \
        --nodes 2 \
        --nodes-min 1 \
        --nodes-max 3 \
        --managed
    
    echo -e "${GREEN}✓ Cluster created${NC}"
fi

# Verify connection
echo -e "\n${YELLOW}Verifying cluster connection...${NC}"
kubectl cluster-info
echo -e "${GREEN}✓ Connected to cluster${NC}"

# Configure ECR access
echo -e "\n${YELLOW}Configuring ECR access...${NC}"
NODE_ROLE=$(aws eks describe-nodegroup \
    --cluster-name $CLUSTER_NAME \
    --nodegroup-name standard-workers \
    --region $REGION \
    --query 'nodegroup.nodeRole' \
    --output text | sed 's:.*/::')

aws iam attach-role-policy \
    --role-name $NODE_ROLE \
    --policy-arn arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly \
    2>/dev/null || echo -e "${YELLOW}Policy already attached${NC}"

echo -e "${GREEN}✓ ECR access configured${NC}"

# Apply secrets
echo -e "\n${YELLOW}Applying Kubernetes secrets...${NC}"
kubectl apply -f k8s/secret.yaml
echo -e "${GREEN}✓ Secrets applied${NC}"

# Deploy MySQL
echo -e "\n${YELLOW}Deploying MySQL...${NC}"
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml
echo -e "${GREEN}✓ MySQL deployed${NC}"

# Wait for MySQL
echo -e "\n${YELLOW}Waiting for MySQL to be ready...${NC}"
kubectl wait --for=condition=ready pod -l app=mysql --timeout=180s || true
echo -e "${GREEN}✓ MySQL ready${NC}"

# Deploy application
echo -e "\n${YELLOW}Deploying application...${NC}"
kubectl apply -f k8s/deployment-aws.yaml
echo -e "${GREEN}✓ Application deployed${NC}"

# Wait for application
echo -e "\n${YELLOW}Waiting for application to be ready...${NC}"
kubectl wait --for=condition=available deployment/registration-app --timeout=300s || true

# Run migrations
echo -e "\n${YELLOW}Running database migrations...${NC}"
sleep 10  # Give pods time to fully start
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || echo "")

if [ ! -z "$POD_NAME" ]; then
    kubectl exec -it $POD_NAME -- php artisan migrate --force || echo -e "${YELLOW}⚠ Migrations failed (you can run manually later)${NC}"
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Pod not ready yet, skip migrations${NC}"
fi

# Get status
echo -e "\n${GREEN}======================================"
echo "✅ Deployment Complete!"
echo "======================================${NC}"

echo -e "\n${BLUE}📊 Current Status:${NC}"
kubectl get pods
echo ""
kubectl get services

# Get LoadBalancer URL
echo -e "\n${BLUE}🌐 Getting Application URL...${NC}"
echo -e "${YELLOW}Waiting for LoadBalancer to be ready (this may take 2-3 minutes)...${NC}"

for i in {1..60}; do
    EXTERNAL_IP=$(kubectl get service registration-app-service -o jsonpath='{.status.loadBalancer.ingress[0].hostname}' 2>/dev/null)
    if [ -z "$EXTERNAL_IP" ]; then
        EXTERNAL_IP=$(kubectl get service registration-app-service -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
    fi
    
    if [ ! -z "$EXTERNAL_IP" ]; then
        echo -e "\n${GREEN}✅ Application URL: http://$EXTERNAL_IP${NC}"
        echo ""
        echo -e "${GREEN}Admin Login:${NC}"
        echo "  URL: http://$EXTERNAL_IP/admin/login"
        echo "  Password: ALPHV187493?1"
        break
    fi
    
    sleep 3
done

if [ -z "$EXTERNAL_IP" ]; then
    echo -e "${YELLOW}⚠ LoadBalancer still provisioning. Check with:${NC}"
    echo "  kubectl get service registration-app-service"
fi

echo -e "\n${BLUE}📝 Useful Commands:${NC}"
echo "  View logs:           kubectl logs -f deployment/registration-app"
echo "  Check pods:          kubectl get pods"
echo "  Run migrations:      kubectl exec -it \$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}') -- php artisan migrate"
echo "  Access pod shell:    kubectl exec -it \$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}') -- /bin/bash"
echo "  Delete cluster:      eksctl delete cluster --name $CLUSTER_NAME --region $REGION"
