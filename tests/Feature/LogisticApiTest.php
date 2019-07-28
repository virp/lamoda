<?php

namespace Tests\Feature;

use App\Container;
use App\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogisticApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function all_returned_containers_must_contain_all_products(): void
    {
        /*
         * rates 1/5 from defaults
         */

        /** @var Product[]|Collection $products */
        $products = factory(Product::class, 20)->create();
        /** @var Container[]|Collection $containers */
        $containers = factory(Container::class, 200)->create();

        foreach ($containers as $container) {
            $container->products()
                ->attach($this->faker->randomElements($products->pluck('id')->toArray(), 2));
        }

        $response = $this->getJson(route('containers.logistic'));

        $data = collect($response->json('data'))->pluck('id');

        $containersForLogistic = Container::whereIn('id', $data)->with('products')->get();

        $productsCount = $containersForLogistic->pluck('products')->flatten()->pluck('id')->unique()->count();

        /*
         * Not all products can be in containers
         */
        $productsCountInContainersFromDB = DB::table('container_product')
            ->selectRaw('distinct product_id')
            ->get()
            ->pluck('product_id')->unique()->count();

        $this->assertEquals($productsCountInContainersFromDB, $productsCount);
    }
}
