
[← Back to main documentation](../README.md)

# `FunctionalTestData` API for Request data and input

## Url

This is the main static constructor, it is **mandatory** to use it when creating an instance of `FunctionalTestData`: 

```php
$testData = FunctionalTestData::withUrl('/');
```

## Host

Will send this as the `HTTP_HOST` environment variable to the Symfony Kernel, allowing it to mimic the `Host:` HTTP Request header:

```php
$testData = FunctionalTestData::withUrl('/')
    ->withHost('example.com');
```

## Method (default `GET`)

Is used to provide another HTTP method than `GET`. Will automatically be transformed to uppercase.

```php
$testData = FunctionalTestData::withUrl('/')
    ->withMethod('POST');
```

## Payload

Is sent as HTTP Request body. Must be a `string`.

```php
$testData = FunctionalTestData::withUrl('/')
    ->withPayload(json_encode(['message'=>'Ok!']);
```

## User locale

Shorthand for setting the `HTTP_ACCEPT_LANGUAGE` HTTP Request header to the provided value.

```php
$testData = FunctionalTestData::withUrl('/')
    ->withUserLocale('en');
```

## Http header

Will send these as HTTP Request headers to the Symfony Kernel.

Since we are using Symfony's native KernelBrowser, requests are uppercased & normalized to `HTTP_*` server parameters in the process.

```php
$testData = FunctionalTestData::withUrl('/')
    ->withHttpHeader('Accept', 'text/plain');
```

## Server / Env parameters (experimental)

Will send these as Server parameters to the Symfony Kernel.

```php
$testData = FunctionalTestData::withUrl('/')
    ->withServerParameters('APP_ENV', 'test');
```

Note: this part is experimental, and env vars processing differs depending on how you set them up in your project.
