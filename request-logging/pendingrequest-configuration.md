# PendingRequest configuration

If a request is being logged, it will get both its log data and logger configured on the pending request.

#### Prepared log

If a request is being logged you have access to the log data directly:

```php
use HappyDemon\SaloonUtils\Logger\Middleware\RegisterLoggerMiddleware;

/** @var Model|array|null $log */
$log = $pendingRequest->config()->get(RegisterLoggerMiddleware::LOGGER_DATA);

```

{% hint style="info" %}
If $log is null, that means no log was ever created for the request, response handlers will be ignored
{% endhint %}

In case of the database logger, this would be a `HappyDemon\SaloonUtils\Logger\SaloonRequest` model instance.

#### Logger

You also have access to the actual [logger](loggers.md);

```php
use HappyDemon\SaloonUtils\Logger\Middleware\RegisterLoggerMiddleware;
use HappyDemon\SaloonUtils\Logger\LoggerRepository;

/** @var LoggerRepository $logRepository */
$logRepository = $pendingRequest->config()->get(RegisterLoggerMiddleware::CONFIG_LOGGER_SERVICE);
```
