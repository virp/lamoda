<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Container extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'container_product');
    }

    public function scopeWithAllProducts(Builder $query): Builder
    {
        $cp = DB::table('container_product')->get();

        $products = $cp->groupBy('product_id')->map->map(static function ($product) {
            return $product->container_id;
        })->toArray();

        $containers = $cp->groupBy('container_id')->map->map(static function ($container) {
            return $container->product_id;
        })->toArray();

        $selectedContainers = [];

        $remainingProducts = static function (array &$products) {
            while (count($products) !== 0) {
                $key = key($products);
                $containers = current($products);

                yield $key => $containers;
            }
        };

        foreach ($remainingProducts($products) as $product => $productContainers) {
            $selectedContainer = null;

            foreach ($productContainers as $productContainer) {
                if (isset($containers[$productContainer])) {
                    $selectedContainer = $productContainer;
                    break;
                }
            }

            if ($selectedContainer === null) {
                unset($products[$product]);
                continue;
            }

            $selectedContainers[] = $selectedContainer;

            $itProducts = $containers[$selectedContainer];

            foreach ($itProducts as $p) {
                foreach ($products[$p] as $ps) {
                    unset($containers[$ps]);
                }
                unset($products[$p]);
            }
        }



        $productsInContainers = DB::table('container_product')
            ->selectRaw('distinct product_id')
            ->whereIn('container_id', $selectedContainers)
            ->get()
            ->pluck('product_id');

        $additionalContainers = DB::table('container_product')
            ->selectRaw('min(container_id) as container_id')
            ->whereIntegerNotInRaw('product_id', $productsInContainers)
            ->groupBy('product_id')
            ->get()
            ->pluck('container_id')
            ->toArray();

        $selectedContainers = array_merge($selectedContainers, $additionalContainers);

        return $query->whereIn('id', $selectedContainers);
    }
}
