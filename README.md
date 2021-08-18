# DBP API-Keycloak-Bundle

[GitLab](https://packagist.org/packages/dbp/api-keycloak-bundle) | [Packagist](https://packagist.org/packages/dbp/api-keycloak-bundle)

## Bundle Configuration

```yaml
# Default configuration for "DbpRelayKeycloakBundle"
dbp_relay_keycloak:

    # The Keycloak server URL
    server_url:           ~ # Example: 'https://keycloak.example.com/auth'

    # The Keycloak Realm
    realm:                ~ # Example: myrealm

    # The ID for the keycloak client (authorization code flow) used for API docs or similar
    frontend_client_id:   ~ # Example: client-docs

    # If remote validation should be used. If set to false the token signature will
    # be only checked locally and not send to the keycloak server
    remote_validation:    false

    # The ID of the client (client credentials flow) used for remote token validation
    # (optional)
    remote_validation_client_id: ~ # Example: client-token-check

    # The client secret for the client referenced by client_id (optional)
    remote_validation_client_secret: ~ # Example: mysecret

    # If set only tokens which contain this audience are accepted (optional)
    required_audience:    ~ # Example: my-api

    # How much the system time of the API server and the Keycloak server
    # can be out of sync (in seconds). Used for local token validation.
    local_validation_leeway: 120
```