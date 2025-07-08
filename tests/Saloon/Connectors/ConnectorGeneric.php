<?php

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchConditionalIgnoreRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequestNoLog;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class ConnectorGeneric extends \Saloon\Http\Connector
{
    use LoggerPlugin;

    public function resolveBaseUrl(): string
    {
        return 'https://google.com';
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function search(string $search): Response
    {
        return $this->send(new GoogleSearchRequest($search));
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function searchWithoutLog(string $search): Response
    {
        return $this->send(new GoogleSearchRequestNoLog($search));
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function searchConditionalLog(string $search): Response
    {
        return $this->send(new GoogleSearchConditionalIgnoreRequest($search));
    }
}
