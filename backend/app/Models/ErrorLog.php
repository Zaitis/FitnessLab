<?php

namespace App\Models;

use Database\Factories\ErrorLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    /** @use HasFactory<ErrorLogFactory> */
    use HasFactory;

    const UPDATED_AT = null;

    /**
     * PSR-3 levels, in the order Monolog defines them. Used to validate the
     * admin viewer's ?level= filter.
     *
     * @var list<string>
     */
    const array LEVELS = [
        'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug',
    ];

    protected $fillable = [
        'level',
        'message',
        'context',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }
}
