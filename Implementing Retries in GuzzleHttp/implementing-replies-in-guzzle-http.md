---
title: "Implementing Retries in Guzzle Http"
description: "How to implement retries in Guzzle Http"
category: "Tips & Tricks"
tags: ["php", "guzzle", "http"]
keywords: "php, guzzle, http, retry, retrying, retries, retrying http requests, retrying guzzle requests"
---

In dealing with external APIs, network hiccups or server issues can really be a pain in the ass. It's just not enough to send a request once and hope for the best. You need a more reliable way to handle those non-ideal situations gracefully. In this short article, I'll be sharing a simple yet robust way to implement retries in Guzzle HTTP.

## Guzzle and Its Retry Middleware

Guzzle is a versatile HTTP client for PHP that provides a flexible and extensible middleware system. Regrettably, the official documentation leaves a bit to be desired when it comes to retry implementation. But no need to worry; I'll fill you in ASAP.

Guzzle has an incredibly flexible middleware system and you just need to tap into that system and implement your retry logic. Here's how:


### Step1: Creating a Custom Handler Stack

To kick things off, you'll want to create a new handler and push the retry middleware onto it. While Guzzle provides a RetryMiddleware class, I find it more elegant to set it up using the Middleware base class and its `retry` static method.
This method accepts two callable functions:

- The first callable accepts four arguments: the number of retries, the request, the answer (if any), and the exception (if any), and decides whether or not to retry a request.
- The second callable returns the delay to be observed between retries. It takes one argument: the current number of retries.

```php
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$maxRetries = 3;
$handler = HandlerStack::create();
$retryMiddleware = Middleware::retry(
    function (int $retries, RequestInterface $request, ?ResponseInterface $response, ?\RuntimeException $e) use ($maxRetries) {
        // Limit the number of retries to maxRetries
        if ($retries >= $maxRetries) {
            return false;
        }

        // Retry connection exceptions
        if ($e instanceof ConnectException) {
            echo "Unable to connect to " . $request->getUri() . ". Retrying (" . ($retries + 1) . "/" . $maxRetries . ")...\n";
            return true;
        }

        if ($response && in_array($response->getStatusCode(), [249, 429, 500, 502, 503, 504], true)) {
            echo "Something went wrong on the server. Retrying (" . ($retries + 1) . "/" . $maxRetries . ")...\n";
            return true;
        }

        return false;
    }, function (int $retries) {
        return 1000 * $retries; // Exponential Backoff: Wait longer with each retry
    }
);
$handler->push($retryMiddleware);

```

In the first callable, we check if the number of retries has exceeded the maximum (to prevent endless retries). We also retry on connection exceptions (e.g., `ConnectException`) because they can be transient. Lastly, we retry if the response status code matches specific values, like 429, 500, 502, 503, or 504. These codes often indicate temporary server issues. For example, 429 typically indicates that the server has received too many requests from the client in a short time and in this case, it's best to wait a bit before trying again.

In the second callable, we implement a retry strategy called "exponential backoff." This strategy gradually increases the delay before each retry. In our example case, the first retry will hold off for one second, the second for two, and so on. We're doing this so we can give the server some breathing room, something very important to avoid overloading the server. Of course, you are free to adjust the delay to suit your needs. You could explore other retry strategies such as constant delay retry or just adjust the calculation used in the exponential backoff.

### Step 2: Using Your Custom Handler Stack

With your custom handler stack in place, you can now create a Guzzle client and pass it your handler.

```php
$client = new \GuzzleHttp\Client([
    'handler' => $handler,
    // other options
]);
```
Your Guzzle client is now equipped to handle retries gracefully.

### Step 3: Testing the Retry Strategy

Now let's test our new client by sending a simple GET request to Google's homepage.

```php
$response = $client->get('https://google.com');
echo $response->getBody()->getContents();
```

This is supposed to work flawlessly under typical circumstances.

Let's now try a different situation to mix things up a bit. Rerun the script after disconnecting from the internet or turning off your data. What you'll see will resemble this:

```bash
Unable to connect to https://google.com. Retrying (1/3)...
Unable to connect to https://google.com. Retrying (2/3)...
Unable to connect to https://google.com. Retrying (3/3)...

Fatal error: Uncaught GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: https://google.com........
```

And there you have it – a robust retry mechanism for your Guzzle HTTP requests. Now you're ready to handle temporary network glitches or server issues like a pro.

Feel free to explore additional retry tactics and customize them to fit your needs. Guzzle has your back, and you now know how to take advantage of it to your advantage. Happy coding!