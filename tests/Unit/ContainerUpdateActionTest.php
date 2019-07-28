<?php

namespace Tests\Unit;

use App\Actions\ContainerUpdateAction;
use App\Container;
use App\Exceptions\ContainerCapacityException;
use App\Product;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ContainerUpdateActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_container(): void
    {
        /** @var Container $container */
        $container = factory(Container::class)->create();

        $newTitle = 'New Title';

        (new ContainerUpdateAction())->handle($container, ['title' => $newTitle]);

        $this->assertEquals($newTitle, $container->fresh()->title);
    }

    /** @test */
    public function should_update_attached_products_if_they_present(): void
    {
        factory(Product::class, 2)->create();

        /** @var Container $container */
        $container = factory(Container::class)->create();
        $container->products()->attach([1, 2]);

        $this->assertCount(2, $container->fresh()->products);

        (new ContainerUpdateAction())->handle($container, ['products' => [2]]);

        $this->assertCount(1, $container->fresh()->products);
    }

    /** @test */
    public function cannot_attach_more_products_then_container_capacity(): void
    {
        Config::set('container.capacity', 2);

        factory(Product::class, 3)->create();

        /** @var Container $container */
        $container = factory(Container::class)->create();

        $this->expectException(ContainerCapacityException::class);

        (new ContainerUpdateAction())->handle($container, ['products' => [1, 2, 3]]);
    }

    /** @test */
    public function should_rollback_container_update_if_cannot_attach_products(): void
    {
        Config::set('container.capacity', 2);

        factory(Product::class, 3)->create();

        /** @var Container $container */
        $container = factory(Container::class)->create();
        $container->products()->sync([1, 2]);

        $newTitle = 'New Title';

        try {
            (new ContainerUpdateAction())->handle($container, ['title' => $newTitle, 'products' => [1, 2, 3]]);
        } catch (Exception $exception) {
        }

        $this->assertNotEquals($newTitle, $container->fresh()->title);
    }
}
