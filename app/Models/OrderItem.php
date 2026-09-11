<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'app_id', 'app_name_snapshot', 'unit_price_cents', 'quantity', 'line_subtotal_cents'])]
class OrderItem extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
