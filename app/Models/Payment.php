<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'template_id', 'coupon_id',
        'type', 'plan',
        'amount', 'amount_original',
        'status', 'gateway', 'gateway_ref', 'gateway_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gateway_payload' => 'array',
            'paid_at'         => 'datetime',
        ];
    }

    public function user():     BelongsTo { return $this->belongsTo(User::class); }
    public function event():    BelongsTo { return $this->belongsTo(Event::class); }
    public function coupon():   BelongsTo { return $this->belongsTo(Coupon::class); }
    public function template(): BelongsTo { return $this->belongsTo(Template::class); }
}
