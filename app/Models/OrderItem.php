<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'app_id', 'app_edition_id', 'app_name_snapshot', 'edition_name_snapshot',
    'included_platforms_snapshot', 'license_label_snapshot', 'unit_price_cents', 'quantity', 'line_subtotal_cents',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'included_platforms_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(AppEdition::class, 'app_edition_id');
    }
}
