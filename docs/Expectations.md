
[← Back to main documentation](../README.md)

# `FunctionalTestData` API for output expectations

## Route name

You can assert that the HTTP Response's attribute key `_route` matches the expected value.

```
$testData = ...
    ->expectRouteName('home');
```

## HTTP Status code

You can assert that the HTTP Response's status code matches the expected value.

```
$testData = ...
    ->expectStatusCode();
```

## Response redirection

You can assert that the HTTP Response Status code is in the valid redirection codes (one in 201, 301, 302, 303, 307 and 308) and that the HTTP Request header `Location` matches the expected value.

```
$testData = ...
    ->expectRedirectUrl('/login');
```

## Content contains string or text

You can assert that the HTTP Response content contains the provided string ortext.

```
$testData = ...
    ->expectTextToBePresent('Hello world!');
```

## Content contains CSS selector

You can assert that the HTTP Response content contains properly encoded HTML, that the provided CSS selector exists at least once in the document.

```
$testData = ...
    ->expectCssSelectorToBePresent('h1');
```

## Content contains CSS selector AND text

You can assert that the HTTP Response content contains properly encoded HTML, that the provided CSS selector exists, and that its `->text()` (with Symfony's Crawler) contains the expected value.

```
$testData = ...
    ->expectCssSelectorToContainText('h1', 'Hello world!');
```

## Custom expectation callbacks

You can provide a callable to add to a list of callbacks that will be executed one by one **after** all other assertions are executed.

When executed, the callback receives two arguments: the HTTP Client as an instance of `KernelBrowser`, and the `Crawler` object.<br>
The value of `$this` inside the callback is the current test class, meaning you can use `$this->assert...` functions right inside the callback.

```
$testData = ...
    ->appendCallableExpectation(
        function (KernelBrowser $client, Crawler $crawler) {
            $this->assertSame('Ok!', $client->getResponse()->getContent());
        }
    );
```

## JSON response

You can assert that the HTTP Response contains the `Content-Type` header set to `application/json` or `application/ld+json`, and that the HTTP Response content is a valid JSON string (meaning it will run `json_decode()` on it).

```
$testData = ...
    ->expectIsJsonResponse();
```

## JSON parts

You can assert that the provided PHP array is contained inside the JSON from the HTTP Response content.

Any omitted field in the expectation is not asserted so you can avoid having to match data that are too random (like dates or UUIDs).

Only the provided keys and values are checked.

```
$testData = ...
    ->expectJsonParts([
        'message' => 'Hello!',
        'code' => 200,
    ]);
```

> Note: this function implicitly calls `$testData->expectIsJsonResponse()`.
