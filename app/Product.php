<?php

namespace App;

use App\Exceptions\DeletingUsedInContainersProductException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Product $product) {
            if ($product->containers()->exists()) {
                throw new DeletingUsedInContainersProductException();
            }
        });
    }

    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'container_product');
    }
}
