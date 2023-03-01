# Custom Roles Mapping

The auth bundle provides a default Symfony service implementing the
`UserRolesInterface` interface which translates OAuth2 token scopes into Symfony
roles. By default, it creates roles with the prefix `ROLE_SCOPE_` and appends the scopes in uppercase.

Examples:

* `my-scope` → `ROLE_SCOPE_MY-SCOPE`
* `MyScope`  → `ROLE_SCOPE_MYSCOPE`

You can override this by registering your own symfony service which implements
`UserRolesInterface` and implementing the `getRoles()` method. There you can
change the mapping and also inject roles from other sources, like LDAP for
example.