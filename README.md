# User Api Bundle

## Configuration

### Routing

```
user_api_bundle:
    resource: '@UserApiBundle/Resources/config/routing.yaml'
    prefix: /api/{v}

```

### Security

Exemple avec l'entité App\Entity\User.

```
security:
    encoders:
        App\Entity\User:
            algorithm: bcrypt
    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers
    providers:
        in_memory: { memory: ~ }
        database:
            entity:
                class: App:User
                property: username
    
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
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

### Commandes

On dispose de deux commandes : 

* `users:create` : création d'utilisateur
* `users:profile:change-password` : changement de mot de passe

Lors de la création d'untilisateur, si une propriété email est détecté, l'email est demandé.

### Token

Pour le JWT Token, les instructions sont sur le [bundle de Lewik](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#installation).
