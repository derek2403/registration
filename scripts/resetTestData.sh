# Get the app pod name
POD_NAME=$(kubectl get pods -l app=registration-app -o jsonpath='{.items[0].metadata.name}')

# Drop all tables and recreate them
kubectl exec -it $POD_NAME -- php artisan migrate:fresh --force

# Or drop and reseed with sample data
kubectl exec -it $POD_NAME -- php artisan migrate:fresh --seed --force