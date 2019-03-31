# User Api Bundle

## Configuration

### Routing

```
user_api_bundle:
    resource: '@UserApiBundle/Resources/config/routing.yaml'
    prefix: /api/{v}

```

### Security

```
security:

    ...
    
    firewalls:
        secured_api_tokens:
            pattern: ^/api/v([1-9]{1})/tokens
            anonymous: true
            stateless: true
        secured_api:
            pattern: ^/api/
            anonymous: true
            stateless: true
            guard:
                provider: database
                authenticators:
                    - App\Security\TokenAuthenticator
                    
    ...
```
