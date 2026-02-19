
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

## Request parameters

Will send these as Request parameters to the Symfony Client.

Symfony adds them to `$request->request` when using HttpFoundation (which is the default).

```php
$testData = FunctionalTestData::withUrl('/')
    ->withRequestParameter('some_data', 'value');
```

## Files

Will send these as Files to the Symfony Client.

Remember that you muse an instance of the `Symfony\Component\HttpFoundation\File\File` class for this when using the default test client with Symfony.

Symfony adds them to `$request->files` when using HttpFoundation (which is the default).

```php
$testData = FunctionalTestData::withUrl('/')
    ->withFile('file_name', new UploadedFile('/tmp/filename.png', 'original_filename.png'));
```

## Execute an action before making the actual HTTP request

Will execute a callback with the `KernelBrowser` instance as first argument just before making the HTTP request.

Very useful to perform logins, setting cookies, or populate a database with test data. 

```php
$testData = FunctionalTestData::withUrl('/')
    ->withCallbackBeforeRequest(function (KernelBrowser $browser): void {
        $browser->getCookieJar()->set(new Cookie('test_cookie', 'test value'));
    });
```
