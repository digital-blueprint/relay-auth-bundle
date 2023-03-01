# Overview

Source: https://github.com/digital-blueprint/relay-auth-bundle

The auth bundle connects the core bundle with an OIDC server. For each request
it validates the passed access token, creates a Symfony user and assigns Symfony
roles to that user.

```mermaid
graph LR
    style auth_bundle fill:#606096,color:#fff

    oidc_server("OIDC Server")

    subgraph API Gateway
        api(("API"))
        core_bundle("Core Bundle")
        auth_bundle("Auth Bundle")
    end

    api --> core_bundle
    core_bundle --> auth_bundle
    auth_bundle --> core_bundle
    auth_bundle --> oidc_server
```