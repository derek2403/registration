# Registration Application - Deployment Guide

This guide will help you deploy the Registration Application from AWS ECR to your own Kubernetes cluster.

---

## 📋 **Prerequisites**

Before starting, ensure you have:

- ✅ Kubernetes cluster (EKS, GKE, AKS, or self-hosted)
- ✅ `kubectl` installed and configured
- ✅ AWS CLI installed (for ECR access)
- ✅ Access credentials provided by the development team

---

## 🔐 **Step 1: Get Access Credentials**

You will receive these from the development team:

1. **AWS Account ID**: `557690595475`
2. **AWS Region**: `ap-southeast-1` (Singapore)
3. **ECR Repository**: `registration-app`
4. **ECR Image URL**: `557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app:latest`
5. **AWS Access Key ID** (for ECR pull)
6. **AWS Secret Access Key** (for ECR pull)

---

## 🚀 **Step 2: Configure AWS CLI**

Configure AWS credentials to access the container registry:

```bash
aws configure
```

Enter when prompted:
- **AWS Access Key ID**: [provided key]
- **AWS Secret Access Key**: [provided secret]
- **Default region**: `ap-southeast-1`
- **Default output format**: `json`

---

## 🔑 **Step 3: Configure Application Secrets**

### **3.1: Copy the Secret Template**

The development team will provide `k8s/exampleSecret.yaml`. Copy this file:

```bash
cp k8s/exampleSecret.yaml k8s/secret.yaml
```

### **3.2: Edit Secret Values**

Open `k8s/secret.yaml` and update the following values:

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: registration-secrets
type: Opaque
stringData:
  # Application Settings
  APP_NAME: "YourCompanyName Registration"
  APP_ENV: "production"
  APP_KEY: "base64:YOUR_GENERATED_APP_KEY_HERE"  # Generate new key!
  APP_DEBUG: "false"
  APP_URL: "http://your-domain.com"
  
  # Admin Dashboard Password
  ADMIN_PASSWORD: "YOUR_SECURE_PASSWORD_HERE"  # Change this!
  
  # Database Configuration
  DB_CONNECTION: "mysql"
  DB_HOST: "mysql"  # Use your database host if external
  DB_PORT: "3306"
  DB_DATABASE: "registration"
  DB_USERNAME: "root"
  DB_PASSWORD: "YOUR_SECURE_DB_PASSWORD"  # Change this!
  
  # Email Configuration (Gmail SMTP example)
  MAIL_MAILER: "smtp"
  MAIL_HOST: "smtp.gmail.com"
  MAIL_PORT: "587"
  MAIL_USERNAME: "your-email@gmail.com"
  MAIL_PASSWORD: "your-app-password"
  MAIL_ENCRYPTION: "tls"
  MAIL_FROM_ADDRESS: "noreply@yourcompany.com"
  MAIL_FROM_NAME: "Your Company Name"
```

### **3.3: Generate Laravel APP_KEY**

To generate a secure `APP_KEY`:

```bash
# Using Docker
docker run --rm 557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app:latest php artisan key:generate --show

# Or using PHP locally
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

Copy the output and paste it as `APP_KEY` in your `secret.yaml`.

---

## 🗄️ **Step 4: Deploy MySQL Database**

### **4.1: Review MySQL Configuration**

Check `k8s/mysql-deployment.yaml` - it's pre-configured but you can adjust:
- Storage size (default: 1GB)
- MySQL version (default: 8.0)

### **4.2: Deploy MySQL**

```bash
# Deploy MySQL and its service
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml

# Wait for MySQL to be ready (takes 1-2 minutes)
kubectl get pods -w
# Press Ctrl+C when you see mysql pod is "Running"
```

---

## 🐳 **Step 5: Configure ECR Access**

Your Kubernetes cluster needs permission to pull images from AWS ECR.

### **Option A: For AWS EKS Clusters**

```bash
# Get your node IAM role name
NODE_ROLE=$(aws eks describe-nodegroup \
    --cluster-name YOUR_CLUSTER_NAME \
    --nodegroup-name YOUR_NODEGROUP_NAME \
    --region YOUR_REGION \
    --query 'nodegroup.nodeRole' \
    --output text | sed 's:.*/::')

# Attach ECR read policy
aws iam attach-role-policy \
    --role-name $NODE_ROLE \
    --policy-arn arn:aws:iam::aws:policy/AmazonEC2ContainerRegistryReadOnly
```

### **Option B: For Non-AWS Kubernetes Clusters**

Create a Docker registry secret:

```bash
# Login to ECR
aws ecr get-login-password --region ap-southeast-1 | \
    docker login --username AWS --password-stdin \
    557690595475.dkr.ecr.ap-southeast-1.amazonaws.com

# Create Kubernetes secret
kubectl create secret docker-registry ecr-secret \
    --docker-server=557690595475.dkr.ecr.ap-southeast-1.amazonaws.com \
    --docker-username=AWS \
    --docker-password=$(aws ecr get-login-password --region ap-southeast-1)

# Update k8s/deployment-aws.yaml to use this secret
# Uncomment these lines in the deployment file:
# imagePullSecrets:
# - name: ecr-secret
```

---

## 📦 **Step 6: Deploy Application Secrets**

Apply the secrets you configured in Step 3:

```bash
kubectl apply -f k8s/secret.yaml
```

Verify secrets were created:

```bash
kubectl get secrets
# You should see "registration-secrets" in the list
```

---

## 🚀 **Step 7: Deploy the Application**

### **7.1: Review Deployment Configuration**

Check `k8s/deployment-aws.yaml` and adjust if needed:
- Number of replicas (default: 2)
- Resource limits (CPU, Memory)
- Image tag (if not using `latest`)

### **7.2: Deploy Application**

```bash
kubectl apply -f k8s/deployment-aws.yaml
```

### **7.3: Monitor Deployment**

```bash
# Watch pods starting
kubectl get pods -w
# Press Ctrl+C when pods are "Running"

# Check deployment status
kubectl rollout status deployment/registration-app

# View application logs
kubectl logs -f deployment/registration-app
```

---

## 🗄️ **Step 8: Run Database Migrations**

Once the application pods are running:

```bash
# Get the first pod name
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}')

# Run migrations
kubectl exec -it $POD_NAME -- php artisan migrate --force

# Verify migrations completed successfully
# You should see output like:
#   INFO  Running migrations.
#   create_users_table ...................... DONE
#   create_teams_table ...................... DONE
#   (etc.)
```

---

## 🌐 **Step 9: Access Your Application**

### **9.1: Get the LoadBalancer URL**

```bash
kubectl get service registration-app-service
```

Look for the `EXTERNAL-IP` column. This is your application URL.

**Note:** LoadBalancer provisioning may take 2-5 minutes. If you see `<pending>`, wait and check again.

### **9.2: Test Access**

Open in your browser:
```
http://YOUR-EXTERNAL-IP
```

Admin panel:
```
http://YOUR-EXTERNAL-IP/admin/login
```

Use the `ADMIN_PASSWORD` you set in `secret.yaml`.

---

## ✅ **Step 10: Verify Deployment**

### **Check All Components**

```bash
# Check all pods are running
kubectl get pods

# Check services
kubectl get services

# Check persistent volumes
kubectl get pvc

# View application logs
kubectl logs -f deployment/registration-app
```

### **Health Checks**

All should show healthy status:
- ✅ 2 registration-app pods: `Running` (2/2)
- ✅ 1 mysql pod: `Running` (1/1)
- ✅ registration-app-service: Has `EXTERNAL-IP`
- ✅ mysql-pv-claim: Status `Bound`

---

## 🔄 **Updating the Application**

When you receive a new version:

### **Option 1: Using Image Tags**

```bash
# Pull new image
kubectl set image deployment/registration-app \
    registration-app=557690595475.dkr.ecr.ap-southeast-1.amazonaws.com/registration-app:v1.1.0

# Monitor rollout
kubectl rollout status deployment/registration-app
```

### **Option 2: Using Latest Tag**

```bash
# Force pull latest image
kubectl rollout restart deployment/registration-app

# Monitor rollout
kubectl rollout status deployment/registration-app
```

### **After Update**

```bash
# Run any new migrations
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}')
kubectl exec -it $POD_NAME -- php artisan migrate --force
```

---

## 📊 **Monitoring & Maintenance**

### **Useful Commands**

```bash
# View application logs
kubectl logs -f deployment/registration-app

# View logs from all replicas
kubectl logs -f -l app=registration-app --all-containers

# View MySQL logs
kubectl logs -f -l app=mysql

# Get pod details
kubectl describe pod <pod-name>

# Access pod shell
kubectl exec -it <pod-name> -- /bin/bash

# Restart deployment
kubectl rollout restart deployment/registration-app

# Scale replicas
kubectl scale deployment/registration-app --replicas=3

# Check resource usage
kubectl top pods
kubectl top nodes
```

### **Database Backup**

```bash
# Backup MySQL database
POD_NAME=$(kubectl get pods -l app=mysql -o jsonpath='{.items[0].metadata.name}')
kubectl exec $POD_NAME -- mysqldump -u root -p"$DB_PASSWORD" registration > backup.sql

# Restore database
kubectl exec -i $POD_NAME -- mysql -u root -p"$DB_PASSWORD" registration < backup.sql
```

---

## 🐛 **Troubleshooting**

### **Pods Not Starting**

```bash
# Check pod events
kubectl describe pod <pod-name>

# Check logs
kubectl logs <pod-name>

# Common issues:
# - Image pull errors: Check ECR credentials
# - CrashLoopBackOff: Check application logs
# - Pending: Check resource availability
```

### **Cannot Connect to Database**

```bash
# Test MySQL connectivity from app pod
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}')
kubectl exec -it $POD_NAME -- php artisan tinker

# In tinker, test:
DB::connection()->getPdo();
```

### **Application Errors**

```bash
# View Laravel logs
kubectl logs -f deployment/registration-app

# Check environment variables
kubectl exec <pod-name> -- env | grep -E "APP_|DB_|MAIL_"

# Clear application cache
kubectl exec <pod-name> -- php artisan cache:clear
kubectl exec <pod-name> -- php artisan config:clear
```

### **LoadBalancer Not Getting IP**

```bash
# Check service
kubectl describe service registration-app-service

# For cloud providers, ensure:
# - Load balancer quota not exceeded
# - Correct security group/firewall rules
# - Service type is LoadBalancer
```

---

## 🔒 **Security Recommendations**

### **Before Production**

1. **Change All Passwords**
   - `ADMIN_PASSWORD`
   - `DB_PASSWORD`
   - `APP_KEY`

2. **Configure TLS/SSL**
   - Use Ingress with cert-manager
   - Or configure AWS Load Balancer with ACM certificate

3. **Restrict Access**
   - Configure network policies
   - Use private subnets for database
   - Enable pod security policies

4. **Enable Monitoring**
   - Set up Prometheus/Grafana
   - Configure CloudWatch (for AWS)
   - Set up alerting

5. **Regular Backups**
   - Schedule database backups
   - Test restore procedures
   - Store backups securely

---

## 📞 **Support**

If you encounter issues:

1. Check the troubleshooting section above
2. Review application logs: `kubectl logs -f deployment/registration-app`
3. Contact development team with:
   - Error messages
   - Output of `kubectl get pods`
   - Output of `kubectl describe pod <pod-name>`
   - Application logs

---

## 📝 **Appendix: File Structure**

```
registration/
├── k8s/
│   ├── secret.yaml              # Your configured secrets
│   ├── exampleSecret.yaml       # Template provided by dev team
│   ├── deployment-aws.yaml      # Application deployment
│   ├── mysql-deployment.yaml    # MySQL database
│   └── mysql-service.yaml       # MySQL service
└── DEPLOYMENT_GUIDE.md          # This file
```

---

## ✅ **Deployment Checklist**

- [ ] AWS CLI configured with provided credentials
- [ ] Kubernetes cluster accessible via kubectl
- [ ] ECR access configured (IAM role or docker-registry secret)
- [ ] `secret.yaml` created and configured with your values
- [ ] APP_KEY generated and added to secrets
- [ ] All passwords changed from defaults
- [ ] MySQL deployed and running
- [ ] Application secrets applied
- [ ] Application deployed
- [ ] Database migrations completed successfully
- [ ] LoadBalancer URL accessible
- [ ] Admin panel login working
- [ ] Email sending tested (if configured)
- [ ] Backup strategy in place

---

**Version:** 1.0  
**Last Updated:** December 2025  
**Application:** Registration System  
**Laravel Version:** 11.x  
**PHP Version:** 8.4

