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
            $table->tinyInteger('batch_number')->unsigned()->default(1);
            $table->string('title');
            $table->string('description')->nullable();
            $table->decimal('price', 13, 2);
            $table->integer('quantity');
            $table->boolean('status')->default(false);
            $table->integer('customer_limit')->default(5);
            $table->enum('batch_type', ['date', 'tickets', 'normal'])->default('normal');
            $table->enum('gender', ['male', 'female', 'unisex'])->default('unisex');
            $table->datetime('limit_date')->nullable();
            $table->integer('limit_tickets')->nullable();
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
