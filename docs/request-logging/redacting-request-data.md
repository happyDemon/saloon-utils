# Redacting request data

There are times you don't want sensitive data logged.

Ensure either your `Request` or `Connector` implements the `RedactsRequests` contract and defines what you want to redact:

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\RedactsRequests;
use HappyDemon\SaloonUtils\Logger\Enums\Redactor;
use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use Saloon\Http\Connector;

class ForgeConnector extends Connector implements RedactsRequests
{
    use LoggerPlugin;
    
    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
    
    public function shouldRedact(): array
    {
        return [
            Redactor::HEADERS->value => [
                // redact all
                '*',
            ],
            Redactor::BODY->value => [
                // dot path syntax supported
                'data.password',
            ],
            Redactor::QUERY->value => [
                'api_token',
            ],
        ];
    }
}
```
