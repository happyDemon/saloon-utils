<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * This model manages request logs
 *
 * @property int $id
 * @property string $connector The fully qualified class name of the connector
 * @property string $request The fully qualified class name of the request
 * @property string $method The HTTP method used
 * @property string $endpoint The endpoint that was called
 * @property array $request_headers The headers sent with the request
 * @property array $request_query The query parameters sent with the request
 * @property array $request_body The body sent with the request
 * @property array $response_headers The headers received in the response
 * @property array $response_body The body received in the response
 * @property int $status_code The HTTP status code received in the response
 * @property Carbon $completed_at When the request was completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class SaloonRequest extends Model
{
    use MassPrunable;

    protected $table = 'saloon_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'connector',
        'request',
        'method',
        'endpoint',
        'request_headers',
        'request_query',
        'request_body',
        'response_headers',
        'response_body',
        'status_code',
        'completed_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('saloon-utils.logs.database_connection', config('database.default')));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'request_headers' => 'array',
            'request_query' => 'array',
            'request_body' => 'array',
            'response_headers' => 'array',
            'response_body' => 'array',
        ];
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return $this->newQuery()->where(
            'created_at',
            '<=',
            now()->startOfDay()->subDays(config('saloon-utils.logs.keep_for_days', 14))
        );
    }
}
