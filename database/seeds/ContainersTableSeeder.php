<?php

use App\Container;
use App\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ContainersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param  \Faker\Generator  $faker
     * @return void
     */
    public function run(Faker\Generator $faker)
    {
        /** @var Container[]|Collection $containers */
        $containers = factory(Container::class, (int) env('SEED_CONTAINERS_COUNT', 1000))->create();

        if (Product::count() === 0) {
            $this->call(ProductsTableSeeder::class);
        }

        /** @var Product[]|Collection $products */
        $products = Product::all();

        foreach ($containers as $container) {
            $container
                ->products()
                ->attach($faker->randomElements(
                    $products->pluck('id')->toArray(),
                    config('container.capacity')
                ));
        }
    }
}
