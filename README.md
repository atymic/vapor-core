# Laravel Vapor Core / Runtime with Extended Secret Support

## Changes from upstream

By default, vapor passes your `.env` variables directly to the lambda function. Unfortunately this has a 2kb limit - very easy to exceed, especially with apps that interact with lots of external services. Currently, the Laravel team suggests creating a secret for every single extra environment variable, which is unfeasible.

This fork of the official package adds extended ENV support. This works by pushing your additional `.env` files to a secret which is loaded just like a normal dotenv in Laravel.
Ideally this will be integrated into the core, but the Laravel team does not currently seem to be prioritising this issue.

The change is very small and single, you can view it by diffing this fork against the official repo.

## Using this package

- Update your `composer.json` to point your installation to the fork, using the `repositories` option.
```json
"repositories": [
    {
        "type": "github",
        "url": "https://github.com/atymic/vapor-core"
    }
],
```
- Update your version to the latest tag (first release being 2.21.3), which will have the secrets code in it. Make sure to lock it to the specific version (otherwise, when laravel updates the mainline package you'll be upgraded and won't have the secret code). Subscribe to [release notifications on the repo](https://github.com/atymic/vapor-core) so you can update once we have released the new version.
- Create an additional `.env` file, for example, `.env.extended` (make sure to gitignore). This file can be up to 10kb
- Update your production deployment script to push the extended env file to a secret (i.e. `vapor secret --name DOT_ENV_EXTENDED  --file .env.extended production`)
  - Any secret with a name prefixed with `DOT_ENV` will be loaded by laravel as a `env` file
- Your app will load the additional secret file at runtime!


## Upstream Readme

<p>
<a href="https://github.com/laravel/vapor-core/actions"><img src="https://github.com/laravel/vapor-core/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/vapor-core"><img src="https://img.shields.io/packagist/dt/laravel/vapor-core" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/vapor-core"><img src="https://img.shields.io/packagist/v/laravel/vapor-core" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/vapor-core"><img src="https://img.shields.io/packagist/l/laravel/vapor-core" alt="License"></a>
</p>

[Laravel Vapor](https://vapor.laravel.com) is an auto-scaling, serverless deployment platform for Laravel, powered by AWS Lambda. Manage your Laravel infrastructure on Vapor and fall in love with the scalability and simplicity of serverless.

Vapor abstracts the complexity of managing Laravel applications on AWS Lambda, as well as interfacing those applications with SQS queues, databases, Redis clusters, networks, CloudFront CDN, and more.

This repository contains the core service providers and runtime client used to make Laravel applications run smoothly in a serverless environment. To learn more about Vapor and how to use this repository, please consult the [official documentation](https://docs.vapor.build).
