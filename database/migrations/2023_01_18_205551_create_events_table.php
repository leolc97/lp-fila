<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('title');
            $table->string('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->dateTime('start_date_sale');
            $table->dateTime('end_date_sale')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->tinyInteger('batch_quantity')->default(1);
            $table->enum('batch_turn', ['batch', 'ticket'])->default('batch');
            $table->boolean('featured')->default(true);
            $table->string('banner_image')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->string('offline_payment_info')->nullable();
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
        Schema::dropIfExists('events');
    }
};
