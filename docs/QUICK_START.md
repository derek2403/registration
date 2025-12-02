# Registration App - Quick Start Guide

**⚡ Fast deployment in 10 steps**

---

## 📦 **What You Need**

From development team:
- AWS Access Key & Secret
- ECR Image URL: `557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app:latest`
- `k8s/` folder with deployment files

---

## 🚀 **10-Step Deployment**

### **1. Configure AWS**
```bash
aws configure
# Enter: Access Key, Secret Key, Region: ap-southeast-1
```

### **2. Create Secrets File**
```bash
cp k8s/exampleSecret.yaml k8s/secret.yaml
# Edit k8s/secret.yaml with your values
```

### **3. Generate APP_KEY**
```bash
docker run --rm 557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app:latest \
  php artisan key:generate --show
# Copy output to secret.yaml
```

### **4. Configure ECR Access (AWS EKS)**
```bash
NODE_ROLE=$(aws eks describe-nodegroup --cluster-name YOUR_CLUSTER --nodegroup-name YOUR_NODEGROUP --region YOUR_REGION --query 'nodegroup.nodeRole' --output text | sed 's:.*/::')
aws iam attach-role-policy --role-name $NODE_ROLE --policy-arn arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly
```

**OR for non-AWS:**
```bash
kubectl create secret docker-registry ecr-secret \
  --docker-server=557690595475.dkr.ecr.ap-southeast-1.amazonaws.com \
  --docker-username=AWS \
  --docker-password=$(aws ecr get-login-password --region ap-southeast-1)
# Then uncomment imagePullSecrets in deployment-aws.yaml
```

### **5. Deploy MySQL**
```bash
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml
```

### **6. Apply Secrets**
```bash
kubectl apply -f k8s/secret.yaml
```

### **7. Deploy Application**
```bash
kubectl apply -f k8s/deployment-aws.yaml
```

### **8. Wait for Pods**
```bash
kubectl get pods -w
# Wait until all pods show "Running"
```

### **9. Run Migrations**
```bash
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}')
kubectl exec -it $POD_NAME -- php artisan migrate --force
```

### **10. Get URL**
```bash
kubectl get service registration-app-service
# Use EXTERNAL-IP to access your app
```

---

## 🌐 **Access**

- **Application:** `http://YOUR-EXTERNAL-IP`
- **Admin Panel:** `http://YOUR-EXTERNAL-IP/admin/login`
- **Password:** Use `ADMIN_PASSWORD` from your `secret.yaml`

---

## 🔄 **Update Application**

```bash
kubectl rollout restart deployment/registration-app
kubectl rollout status deployment/registration-app
```

---

## 🐛 **Quick Troubleshooting**

```bash
# Check pods
kubectl get pods

# View logs
kubectl logs -f deployment/registration-app

# Describe pod
kubectl describe pod <pod-name>

# Test database
kubectl exec -it <app-pod> -- php artisan tinker
```

---

## 📞 **Need Help?**

See full **DEPLOYMENT_GUIDE.md** for:
- Detailed explanations
- Troubleshooting guide
- Security recommendations
- Monitoring setup

---

**🎯 Time to Deploy:** ~15-20 minutes  
**💰 AWS EKS Cost:** ~$120/month  
**🔧 Support:** Contact development team with pod logs

