#!/bin/bash

# Kubernetes Deployment Script for Registration App
# This script deploys your application to Kubernetes

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Deploying Registration App to Kubernetes${NC}"
echo "=============================================="

# Check if kubectl is available
echo -e "\n${YELLOW}Checking kubectl...${NC}"
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ kubectl is available${NC}"

# Check cluster connection
echo -e "\n${YELLOW}Checking cluster connection...${NC}"
if ! kubectl cluster-info &> /dev/null; then
    echo -e "${RED}❌ Cannot connect to Kubernetes cluster${NC}"
    echo "Please configure kubectl first:"
    echo "  For EKS: aws eks update-kubeconfig --region ap-southeast-1 --name your-cluster-name"
    echo "  For Docker Desktop: Enable Kubernetes in Docker Desktop settings"
    exit 1
fi
echo -e "${GREEN}✓ Connected to cluster${NC}"
kubectl cluster-info | head -n 1

# Apply secrets from secret.yaml
echo -e "\n${YELLOW}Applying secrets...${NC}"
if [ -f "k8s/secret.yaml" ]; then
    kubectl apply -f k8s/secret.yaml
    echo -e "${GREEN}✓ Secrets applied from k8s/secret.yaml${NC}"
else
    echo -e "${RED}❌ k8s/secret.yaml not found${NC}"
    exit 1
fi

# Deploy MySQL
echo -e "\n${YELLOW}Deploying MySQL...${NC}"
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml
echo -e "${GREEN}✓ MySQL deployment applied${NC}"

# Wait for MySQL to be ready
echo -e "\n${YELLOW}Waiting for MySQL to be ready...${NC}"
kubectl wait --for=condition=ready pod -l app=registration-mysql --timeout=120s || true
echo -e "${GREEN}✓ MySQL is ready${NC}"

# Deploy Application
echo -e "\n${YELLOW}Deploying Registration App...${NC}"
kubectl apply -f k8s/deployment-aws.yaml
echo -e "${GREEN}✓ Application deployment applied${NC}"

# Wait for deployment to be ready
echo -e "\n${YELLOW}Waiting for application to be ready...${NC}"
kubectl wait --for=condition=available deployment/registration-app --timeout=180s || true

# Show status
echo -e "\n${GREEN}=============================================="
echo "✅ Deployment Complete!"
echo "==============================================${NC}"

echo -e "\n${BLUE}📊 Current Status:${NC}"
kubectl get pods
echo ""
kubectl get services

# Get service URL
echo -e "\n${BLUE}🌐 Access Information:${NC}"
SERVICE_TYPE=$(kubectl get service registration-app-service -o jsonpath='{.spec.type}')

if [ "$SERVICE_TYPE" == "LoadBalancer" ]; then
    echo "Service Type: LoadBalancer"
    echo -e "${YELLOW}Getting external URL (this may take a few minutes)...${NC}"
    EXTERNAL_IP=""
    for i in {1..30}; do
        EXTERNAL_IP=$(kubectl get service registration-app-service -o jsonpath='{.status.loadBalancer.ingress[0].hostname}' 2>/dev/null)
        if [ -z "$EXTERNAL_IP" ]; then
            EXTERNAL_IP=$(kubectl get service registration-app-service -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null)
        fi
        if [ ! -z "$EXTERNAL_IP" ]; then
            break
        fi
        sleep 2
    done
    
    if [ ! -z "$EXTERNAL_IP" ]; then
        echo -e "${GREEN}✓ Application URL: http://$EXTERNAL_IP${NC}"
    else
        echo -e "${YELLOW}⚠ LoadBalancer is still provisioning. Check status with:${NC}"
        echo "  kubectl get service registration-app-service"
    fi
else
    echo "Service Type: NodePort/ClusterIP"
    echo -e "${YELLOW}To access locally, run:${NC}"
    echo "  kubectl port-forward service/registration-app-service 8080:80"
    echo "  Then open: http://localhost:8080"
fi

echo -e "\n${BLUE}📝 Useful Commands:${NC}"
echo "  View logs:       kubectl logs -f deployment/registration-app"
echo "  Check pods:      kubectl get pods"
echo "  Describe pod:    kubectl describe pod <pod-name>"
echo "  Delete all:      kubectl delete -f k8s/"
echo "  Restart:         kubectl rollout restart deployment/registration-app"

