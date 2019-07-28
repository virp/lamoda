<?php

namespace Tests\Feature;

use App\Container;
use App\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ContainersApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('container.capacity', 10);
    }

    /** @test */
    public function can_return_containers_list(): void
    {
        factory(Container::class)->create();

        $this->getJson(route('containers.index'))
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
    public function should_return_paginated_containers_list(): void
    {
        factory(Container::class, 15)->create();

        $this->getJson(route('containers.index'))
            ->assertJsonCount(10, 'data');

        $this->getJson(route('containers.index', ['page' => 2]))
            ->assertJsonCount(5, 'data');

        $this->getJson(route('containers.index', ['per_page' => 15]))
            ->assertJsonCount(15, 'data');
    }

    /** @test */
    public function can_return_container(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        /** @var Product $product */
        $product = factory(Product::class)->create();

        $container->products()->attach($product->id);

        $this->getJson(route('containers.show', $container))
            ->assertJson(
                [
                    'data' => [
                        'id' => $container->id,
                        'title' => $container->title,
                        'products' => [
                            [
                                'id' => $product->id,
                                'title' => $product->title,
                            ]
                        ],
                    ]
                ]
            );
    }

    /** @test */
    public function can_create_container(): void
    {
        $data = factory(Container::class)->raw();

        $this->postJson(route('containers.store'), $data)
            ->assertStatus(201)
            ->assertJson(
                [
                    'data' => [
                        'id' => 1,
                        'title' => $data['title'],
                        'products' => [],
                    ]
                ],
                true
            );

        $this->assertDatabaseHas('containers', $data);
    }

    /** @test */
    public function can_create_container_with_attached_products(): void
    {
        /** @var Product[]|Collection $products */
        $products = factory(Product::class, 2)->create();

        $data = factory(Container::class)->raw(['products' => $products->pluck('id')->toArray()]);

        $this->postJson(route('containers.store'), $data)
            ->assertStatus(201)
            ->assertJson(
                [
                    'data' => [
                        'id' => 1,
                        'title' => $data['title'],
                        'products' => [
                            [
                                'id' => $products[0]->id,
                                'title' => $products[0]->title,
                            ],
                            [
                                'id' => $products[1]->id,
                                'title' => $products[1]->title,
                            ]
                        ],
                    ]
                ],
                true
            );
    }

    /** @test */
    public function cannot_create_container_with_products_if_their_count_is_greater_than_container_capacity(): void
    {
        Config::set('container.capacity', 2);

        factory(Product::class, 3)->create();

        $data = factory(Container::class)->raw(['products' => [1, 2, 3]]);

        $this->postJson(route('containers.store'), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['products']);

        $this->assertDatabaseMissing('containers', [$data['title']]);
    }

    /** @test */
    public function container_title_should_be_required_for_creating(): void
    {
        $this->postJson(route('containers.store'), ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertCount(0, Container::all());
    }

    /** @test */
    public function container_title_should_be_unique_for_creating(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->postJson(route('containers.store'), ['title' => $container->title])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertCount(1, Container::all());
    }

    /** @test */
    public function can_update_container(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->putJson(route('containers.update', $container), $container->toArray())
            ->assertStatus(200);

        $newTitle = 'New Title';

        $container->title = $newTitle;

        $this->putJson(route('containers.update', $container), $container->toArray())
            ->assertStatus(200)
            ->assertJson(
                [
                    'data' => [
                        'id' => $container->id,
                        'title' => $newTitle,
                        'products' => [],
                    ],
                ],
                true
            );

        $this->assertEquals($newTitle, $container->fresh()->title);
    }

    /** @test */
    public function can_update_container_with_attached_products(): void
    {
        /** @var Product[]|Collection $products */
        $products = factory(Product::class, 2)->create();

        /** @var Container $container */
        $container = factory(Container::class)->create();

        $container->products()->sync($products->pluck('id')->toArray());

        $newTitle = 'New Title';

        $container->title = $newTitle;

        $data = $container->toArray();
        $data['products'] = [$products[0]->id];

        $this->putJson(route('containers.update', $container), $data)
            ->assertStatus(200)
            ->assertJson(
                [
                    'data' => [
                        'id' => $container->id,
                        'title' => $newTitle,
                        'products' => [
                            [
                                'id' => $products[0]->id,
                                'title' => $products[0]->title,
                            ],
                        ],
                    ],
                ],
                true
            );

        $this->assertEquals($newTitle, $container->title);
        $this->assertCount(1, $container->fresh()->products);
    }

    /** @test */
    public function cannot_update_container_with_products_if_their_count_is_greater_than_container_capacity(): void
    {
        Config::set('container.capacity', 2);

        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->putJson(route('containers.update', $container), ['products' => [1, 2, 3]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }

    /** @test */
    public function container_title_should_be_required_for_updating(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->putJson(route('containers.update', $container), ['title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertEquals($container->title, $container->fresh()->title);
    }

    /** @test */
    public function container_title_should_be_unique_for_updating(): void
    {
        $oldTitle = 'Old Title';

        factory(Container::class)->create(['title' => $oldTitle]);

        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->putJson(route('containers.update', $container), ['title' => $oldTitle])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        $this->assertEquals($container->title, $container->fresh()->title);
    }

    /** @test */
    public function can_destroy_container(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->assertDatabaseHas('containers', $container->toArray());

        $this->deleteJson(route('containers.destroy', $container))
            ->assertStatus(200);

        $this->assertDatabaseMissing('containers', $container->toArray());
    }
}
