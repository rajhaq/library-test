<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('category');
            $table->string('author')->unique();
            $table->string('publish_date')->nullable();
            $table->integer('stock')->default(1);
            $table->tinyInteger('status')->default(1);

            $table->timestamps();
            $table->foreign('user_id')
            ->references('id')->on('users');
        });
        \DB::statement('ALTER TABLE table_name AUTO_INCREMENT = 1000000000;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
