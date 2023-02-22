<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class);
            $table->tinyInteger('batch')->default(1);
            $table->string('title');
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->integer('customer_limit')->default(5);
            $table->enum('gender', ['male', 'female', 'unisex'])->default('unisex');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
