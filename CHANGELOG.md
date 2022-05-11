# v0.1.9 - 2022-05-11

* Add a health check for remote token validation via the introspection endpoint

# v0.1.8 - 2022-05-09

* Add a health check for fetching the OIDC config provided by the OIDC server
  (Keycloak for example)
* Add a health check which checks if the server time is in sync with the OIDC
  server time
* Stop using the abandoned web-token/jwt-easy and use to the underlying
  libraries directly instead, as recommended
