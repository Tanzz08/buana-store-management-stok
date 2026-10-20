<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LowStockNotification extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'low_stock_notifications';

    /**
     * Disable automatic timestamp management — table has only `created_at`
     * plus nullable `read_at` and `resolved_at` (managed manually).
     */
    public $timestamps = false;

    /**
     * The name of the "created at" column.
     */
    const CREATED_AT = 'created_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'stock_at_creation',
        'status',
        'created_at',
        'read_at',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_at_creation' => 'integer',
        'created_at'        => 'datetime',
        'read_at'           => 'datetime',
        'resolved_at'       => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope to retrieve only active (unread or read) notifications.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['unread', 'read']);
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class, 'notification_id');
    }
}
