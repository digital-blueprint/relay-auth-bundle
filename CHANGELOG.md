# v0.1.27

* Support symfony/cache-contracts v3

# v0.1.26

* Add support for Symfony 6

# v0.1.25

* dev: replace abandoned composer-git-hooks with captainhook.
  Run `vendor/bin/captainhook install -f` to replace the old hooks with the new ones
  on an existing checkout.

# v0.1.24

* Port from web-token/jwt-core 2.0 to web-token/jwt-library 3.3

# v0.1.23

* Drop support for PHP 7.4/8.0

# v0.1.22

* Drop support for PHP 7.3

# v0.1.20

* Add some more unit tests
* Removal of some deprecated API usages

# v0.1.19

* Add support for kevinrob/guzzle-cache-middleware v5

# v0.1.18

* Add caching for roles fetched via UserRolesInterface

# v0.1.17

* Use the global "cache.app" adapter for caching instead of always using the filesystem adapter

# v0.1.16

* Move to GitHub

# v0.1.15

* Fix tests with newer core bundle versions

# v0.1.12 - 2022-11-15

* Added new `frontend_client_id` config entry as a replacement for `frontend_keycloak_client_id`
* Deprecated config entries: `frontend_keycloak_server`, `frontend_keycloak_realm`, `frontend_keycloak_client_id`

# v0.1.9 - 2022-05-11

* Add a health check for remote token validation via the introspection endpoint

# v0.1.8 - 2022-05-09

* Add a health check for fetching the OIDC config provided by the OIDC server
  (Keycloak for example)
* Add a health check which checks if the server time is in sync with the OIDC
  server time
* Stop using the abandoned web-token/jwt-easy and use to the underlying
  libraries directly instead, as recommended
