## Bundle Configuration

created via `./bin/console config:dump-reference DbpRelayAuthBundle | sed '/^$/d'`

```yaml
# Default configuration for "DbpRelayAuthBundle"
dbp_relay_auth:
    # The base URL for the OIDC server (in case of Keycloak fort the specific realm)
    server_url:           ~ # Example: 'https://keycloak.example.com/auth/realms/my-realm'
    # If set only tokens which contain this audience are accepted (optional)
    required_audience:    ~ # Example: my-api
    # How much the system time of the API server and the Keycloak server
    # can be out of sync (in seconds). Used for local token validation.
    local_validation_leeway: 120
    # If remote validation should be used. If set to false the token signature will
    # be only checked locally and not send to the keycloak server
    remote_validation:    false
    # The ID of the client (client credentials flow) used for remote token validation
    # (optional)
    remote_validation_id: ~ # Example: client-token-check
    # The client secret for the client referenced by client_id (optional)
    remote_validation_secret: ~ # Example: mysecret
    # The Keycloak server base URL
    frontend_keycloak_server: ~ # Example: 'https://keycloak.example.com/auth'
    # The keycloak realm
    frontend_keycloak_realm: ~ # Example: client-docs
    # The ID for the keycloak client (authorization code flow) used for API docs or similar
    frontend_keycloak_client_id: ~ # Example: client-docs
```