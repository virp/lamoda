<?php

namespace Tests\Feature;

use App\Container;
use App\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_return_products_list(): void
    {
        factory(Product::class, (int) env('SEED_PRODUCTS_COUNT', 100))->create();

        $this->getJson(route('products.index'))
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [['id', 'title']],
                    'links',
                    'meta',
                ]
            );
    }

    /** @test */
    public function should_return_paginated_products_list(): void
    {
        factory(Product::class, 15)->create();

        $this->getJson(route('products.index'))
            ->assertJsonCount(10, 'data');

        $this->getJson(route('products.index', ['page' => 2]))
            ->assertJsonCount(5, 'data');

        $this->getJson(route('products.index', ['per_page' => 15]))
            ->assertJsonCount(15, 'data');
    }

    /** @test */
    public function can_return_product(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->getJson(route('products.show', $product))
            ->assertJson(
                [
                    'data' => [
                        'id' => $product->id,
                        'title' => $product->title,
                    ],
                ],
                true
            );
    }

    /** @test */
    public function can_create_product(): void
    {
        $data = factory(Product::class)->raw();

        $this->postJson(route('products.store'), $data)
            ->assertStatus(201)
            ->assertJson(
                [
                    'data' => [
                        'id' => 1,
                        'title' => $data['title'],
                    ],
                ],
                true
            );

        $this->assertDatabaseHas('products', $data);
    }

    /** @test */
    public function product_title_should_be_required_for_creating(): void
    {
        $this->postJson(route('products.store'), ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertCount(0, Product::all());
    }

    /** @test */
    public function product_title_should_be_unique_for_creating(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->postJson(route('products.store'), ['title' => $product->title])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertCount(1, Product::all());
    }

    /** @test */
    public function can_update_product(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->putJson(route('products.update', $product), $product->toArray())
            ->assertStatus(200);

        $newTitle = 'New Title';

        $product->title = $newTitle;

        $this->putJson(route('products.update', $product), $product->toArray())
            ->assertStatus(200)
            ->assertJson(
                [
                    'data' => [
                        'id' => $product->id,
                        'title' => $newTitle,
                    ],
                ],
                true
            );

        $this->assertEquals($newTitle, $product->fresh()->title);
    }

    /** @test */
    public function product_title_should_be_required_for_updating(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->putJson(route('products.update', $product), ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertNotEquals('', $product->fresh()->title);
    }

    /** @test */
    public function product_title_should_be_unique_for_updating(): void
    {
        $title = 'Old Title';
        factory(Product::class)->create(['title' => $title]);

        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->putJson(route('products.update', $product), ['title' => $title])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertNotEquals($title, $product->fresh()->title);
    }

    /** @test */
    public function can_destroy_product(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create();

        $this->assertDatabaseHas('products', $product->toArray());

        $this->deleteJson(route('products.destroy', $product))
            ->assertStatus(200);

        $this->assertDatabaseMissing('products', $product->toArray());
    }

    /** @test */
    public function cannot_destroy_product_what_attached_to_container(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        /** @var Product $product */
        $product = factory(Product::class)->create();

        $container->products()->attach($product->id);

        $this->deleteJson(route('products.destroy', $product))
            ->assertStatus(409);

        $this->assertDatabaseHas('products', $product->toArray());
    }
}
