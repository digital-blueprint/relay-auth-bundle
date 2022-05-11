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

## Configuration Discovery

The auth bundle requires for the OIDC server to implement [OpenID Connect
Discovery](https://openid.net/specs/openid-connect-discovery-1_0.html) as well
as the metadata defined in the [OAuth 2.0 Authorization Server
Metadata](https://datatracker.ietf.org/doc/html/rfc8414).

Example: https://auth-demo.tugraz.at/auth/realms/tugraz-vpu/.well-known/openid-configuration


## Token Validation Modes

There are two modes of operation:

* **Local validation** (default): The bundle fetches (and caches) the public
  singing key from the OIDC server and verifies the access token signature (and
  all other properties like expiration dates) in process. The has the upside of
  being a fast, but has the downside of not taking token revocation into
  account, so should only be used if the access tokens lifetime isn't too long.
  Another extra requirement is that the system clock of the gateway and the OIDC
  server shouldn't deviate too much to avoid valid tokens being marked as
  expired or not valid yet.

* **Remote validation**: The bundle passes the access token to the OIDC server
  introspection endpoint for each request. This adds overhead to each request but
  everything is handled by the OIDC server.


## Frontend Keycloak Config (FIXME)

At this time the bundle is still depending on Keycloak as a specific OIDC server
for some optional functionality. The auth bundle handles the OIDC login
component of the OpenAPI docs provided by the core bundle (the login button at
the top left).

We are looking into providing a frontend web component that works with all OIDC
serves to remove this dependency.


## Remote Validation Client with Keycloak

To create a client which can validate/introspect tokens in Keycloak create a
new client with an ID of your choosing:

* Switch the "Access Type" to confidential
* Enable "Service Accounts Enabled"

* `remote_validation_id` is the "Client ID" of the client visible on the "Settings" page
* `remote_validation_secret` is the "Secret" of the client visible on the "Credentials" page
