<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainerProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('container_product', function (Blueprint $table) {
            $table->unsignedBigInteger('container_id');
            $table->unsignedBigInteger('product_id');

            $table->primary(['container_id', 'product_id']);

            $table->foreign('container_id')
                ->references('id')
                ->on('containers')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('container_product');
    }
}
