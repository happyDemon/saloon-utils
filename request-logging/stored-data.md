# How requests are stored

Before a log is saved, the request and response data is normalised. This logic lives in the `HappyDemon\SaloonUtils\Logger\Stores\ParsesRequestData` trait, so it applies to both bundled loggers (database and memory) and to any custom logger that uses the trait.

## Request & response bodies

Bodies aren't always stored verbatim:

* **Large responses** — when a response body reaches `saloon-utils.logs.response_max_length` bytes or more, it's stored as the string `too large` instead of the full body. The bundled migration uses `longtext`, so the default limit (`4294967295`) is effectively unlimited; lower it to cap what you keep.
* **Unsupported content types** — only responses with one of these `Content-Type`s have their body stored: `application/json`, `application/xml`, `application/soap+xml`, `text/xml`, `text/html`, `text/plain`, or no content type at all. Anything else is stored as `unsupported body: <content-type>`.
* **Stream & multipart request bodies** — a streamed request body is stored as `Streamed Body`, and a multipart body as `Multipart Body`, rather than their raw contents.

{% hint style="info" %}
These conversions live in `ParsesRequestData`. A custom logger that doesn't use the trait is free to store data however it likes.
{% endhint %}

## Failed requests

When Saloon can't complete a request — a `FatalRequestException`, e.g. the host can't be reached — the attempt is still logged:

* `status_code` is set to `418`.
* `response_body` holds the error details:

```json
{
    "internal_error": {
        "code": 0,
        "message": "cURL error 6: Could not resolve host"
    },
    "trace": "..."
}
```

This makes connection-level failures easy to spot. HTTP error responses (4xx/5xx) that actually came back keep their real status code and body.
