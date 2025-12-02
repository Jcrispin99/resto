<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMenuAvailability extends Model
{
    use HasFactory;

    protected $table = 'branch_menu_availability';

    protected $fillable = [
        'branch_id',
        'menu_item_id',
        'available_from',
        'available_to',
        'is_available_on',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'is_available_on' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'menu_item_id');
    }
}
