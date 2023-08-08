# Configuration

## Recipe

The default [Symfony recipe](https://github.com/digital-blueprint/symfony-recipes/tree/main/dbp/relay-auth-bundle)
creates a minimal configuration using two environment variables, which you have to fill out:

* `AUTH_SERVER_URL`: The URL to the OIDC server (or in case of Keycloak to the realm on the server)
* `AUTH_FRONTEND_CLIENT_ID`: The client ID for the API documentation page

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
  # The client ID for the OIDC client (authorization code flow) used for API docs and other frontends provided by the API itself
  frontend_client_id:   ~ # Example: client-docs
  # The authorization attributes that are available for users and derived from OIDC token scopes
  authorization_attributes:
    # Prototype
    -
      name:                 ~
      scope:                ~

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


## Remote Validation Client with Keycloak

To create a client which can validate/introspect tokens in Keycloak create a
new client with an ID of your choosing:

* Switch the "Access Type" to confidential
* Enable "Service Accounts Enabled"

* `remote_validation_id` is the "Client ID" of the client visible on the "Settings" page
* `remote_validation_secret` is the "Secret" of the client visible on the "Credentials" page
