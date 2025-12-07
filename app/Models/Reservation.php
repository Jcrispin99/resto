<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'branch_id',
        'partner_id',
        'table_id',
        'reservation_date',
        'reservation_time',
        'guests_count',
        'status',
        'special_requests',
        'confirmed_at',
        'seated_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'datetime',
        'guests_count' => 'integer',
        'confirmed_at' => 'datetime',
        'seated_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'branch_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', today())
                     ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_NO_SHOW, self::STATUS_COMPLETED]);
    }

    // Status constants
    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_SEATED = 'seated';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_NO_SHOW = 'no_show';
}
