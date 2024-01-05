<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'prometheus_id',
        'nombre',
        'precio_menor',
    ];

    public $timestamps = false;

    public function despachos() : BelongsToMany {
        return $this->belongsToMany(Dispatch::class, 'dispatch_product', 'product_id', 'dispatch_id')->withPivot('cantidad');
    }
}
