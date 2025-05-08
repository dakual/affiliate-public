k8s_yaml('./.k8s/configmap.yaml')
k8s_resource(new_name='configmap', objects=['affiliate-envars'], labels=['Utils'])

k8s_yaml('./.k8s/rabbitmq.yaml')
k8s_resource('rabbitmq', port_forwards=['5672:5672', '15672:15672'], labels=['Queues'])

k8s_yaml('./.k8s/database.yaml')
k8s_resource('database', port_forwards=['3306:3306'], labels=['Databases'])
k8s_resource(new_name='volumes', objects=['mysql-pv','mysql-pvc'], labels=['Databases'])


docker_build(
  'backend',
  context='./',
  dockerfile='./backend/Dockerfile',
  live_update=[
    sync('./backend/src', '/app/'),
    sync('./backend/public', '/app/'),
  ]
)
k8s_yaml('./.k8s/backend.yaml')
k8s_resource('backend', port_forwards="5001:5000", labels=['Application'])


docker_build(
  'fe-admin',
  context='./',
  dockerfile='./fe-admin/Dockerfile',
  live_update=[
    sync('./fe-admin/src', '/app/'),
    sync('./fe-admin/public', '/app/'),
    sync('./fe-admin/templates', '/app/'),
  ]
)
k8s_yaml('./.k8s/fe-admin.yaml')
k8s_resource('fe-admin', port_forwards="5002:5000", labels=['Application'])


docker_build(
  'fe-user',
  context='./',
  dockerfile='./fe-user/Dockerfile',
  live_update=[
    sync('./fe-user/src', '/app/'),
    sync('./fe-user/public', '/app/'),
    sync('./fe-user/templates', '/app/'),
  ]
)
k8s_yaml('./.k8s/fe-user.yaml')
k8s_resource('fe-user', port_forwards="5003:5000", labels=['Application'])

docker_build(
  'consumer-email',
  context='./',
  dockerfile='./consumer-email/Dockerfile',
  live_update=[
    sync('./consumer-email/src', '/app/'),
    sync('./consumer-email/public', '/app/'),
    sync('./consumer-email/templates', '/app/'),
  ]
)
k8s_yaml('./.k8s/consumer-email.yaml')
k8s_resource('consumer-email', labels=['Application'])

# include('./gateway/Tiltfile')


# Enables istio sidecar injection
load('ext://namespace', 'namespace_create')
namespace_create(name=k8s_namespace(), labels=['istio-injection: enabled'])
k8s_resource(
  new_name='default-namespace',
  objects=['default:Namespace'],
  labels=['gateway'],
  pod_readiness='ignore',
)

k8s_yaml('./.k8s/istio.yaml')

k8s_resource(
  objects=['gateway:Gateway', 'gateway:VirtualService'],
  new_name='gateway-istio',
  labels=['gateway'],
  resource_deps=['istio-ingress'],
  pod_readiness='ignore',
)

k8s_resource(
  new_name='coredns-config',
  objects=['coredns:ConfigMap'],
  labels=['gateway'],
  pod_readiness='ignore',
)


load('ext://helm_resource', 'helm_resource', 'helm_repo')
helm_repo('istio', 'https://istio-release.storage.googleapis.com/charts', labels=['gateway'])

# Istio deployment
helm_resource('istio-base', 'istio/base', 
  namespace='istio-system',
  labels=['gateway'],
  pod_readiness='ignore',
  resource_deps=['istio'],
  flags=['--create-namespace'],
)

helm_resource('istiod', 'istio/istiod', 
  labels=['gateway'],
  namespace='istio-system',
  resource_deps=['istio-base'],
)

helm_resource('istio-ingress', 'istio/gateway', 
  labels=['gateway'],
  namespace='istio-ingress',
  resource_deps=['istiod'],
  flags=['--create-namespace'],
)

