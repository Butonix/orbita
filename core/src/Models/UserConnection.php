<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property string $service
 * @property string $remote_id
 * @property ?array $data
 * @property Carbon $timestamp
 *
 * @property-read User $user
 */
class UserConnection extends Model
{
    use Traits\CompositeKey;

    protected $primaryKey = ['user_id', 'service'];
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}