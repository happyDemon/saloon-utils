<?php

namespace HappyDemon\SaloonUtils\Logger;

use Illuminate\Database\Eloquent\Model;

/**
 * This model manages request logs
 */
class SaloonRequest extends Model
{
    protected $table = 'saloon_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'connector',
        'endpoint',
        'request_headers',
        'request_query',
        'request_body',
        'response_headers',
        'response_body',
        'status_code',
        'completed_at',
    ];

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
}
