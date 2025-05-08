```sh
docker run --rm -it -v "$(pwd):/app" composer init
docker run --rm -it -v "$(pwd):/app" composer require slim/slim
docker run --rm -it -v "$(pwd):/app" composer require slim/psr7
docker run --rm -it -v "$(pwd):/app" composer require firebase/php-jwt
docker run --rm -it -v "$(pwd):/app" composer require selective/basepath
docker run --rm -it -v "$(pwd):/app" composer require php-di/php-di
docker run --rm -it -v "$(pwd):/app" composer install  --ignore-platform-req=ext-sockets
docker run --rm -it -v "$(pwd):/app" composer dump-autoload
docker run --rm -it -v "$(pwd):/app" -p 8080:8080 composer start
docker run --rm -it -v "$(pwd):/app" -p 8080:8080 php:8.1-cli -S 0.0.0.0:8080 -t /app/public
```

### for tilt 
```sh
tilt up
```

### for docker compose
```sh
docker compose up --build
```

### intall self-hosted github action runner on minikube
```sh
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.17.2/cert-manager.yaml
kubectl get all --namespace cert-manager

kubectl create ns actions-runner-system
kubectl create secret generic controller-manager -n actions-runner-system --from-literal=github_token="<GITHUB_TOKEN>"

helm repo add actions-runner-controller https://actions-runner-controller.github.io/actions-runner-controller
helm repo update
helm upgrade --install --namespace actions-runner-system --create-namespace --wait actions-runner-controller actions-runner-controller/actions-runner-controller --set syncPeriod=1m
kubectl get all --namespace actions-runner-system
```

```yaml
apiVersion: v1
kind: ServiceAccount
metadata:
  namespace: actions-runner-system
  name: github-runner-sa
automountServiceAccountToken: true

---

apiVersion: rbac.authorization.k8s.io/v1
kind: ClusterRoleBinding
metadata:
  name: github-runner-api-cluster-role-binding
  namespace: actions-runner-system
subjects:
  - kind: ServiceAccount
    name: github-runner-sa
    namespace: actions-runner-system
    apiGroup: ""
roleRef:
  kind: ClusterRole
  name: admin
  apiGroup: rbac.authorization.k8s.io

---

apiVersion: actions.summerwind.dev/v1alpha1
kind: RunnerDeployment
metadata:
 name: k8s-action-runner
 namespace: actions-runner-system
spec:
 replicas: 1
 template:
   spec:
     serviceAccountName: github-runner-sa
     repository: dakual/affiliate
```

```sh
kubectl get pod -n actions-runner-system | grep -i "k8s-action-runner"
```

```yaml
runs-on: self-hosted
```

