<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'prometheus_id',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y H:i',
    ];

    public function products() : BelongsToMany {
        return $this->belongsToMany(Product::class, 'dispatch_product', 'dispatch_id', 'product_id')->withPivot('cantidad');
    }

}
