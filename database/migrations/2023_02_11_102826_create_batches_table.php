<?php

use App\Models\Ticket;
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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ticket::class);
            $table->tinyInteger('ticket_batch')->default(1);
            $table->decimal('price', 13, 2);
            $table->enum('batch_type', ['date', 'tickets', 'normal'])->default('normal');
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
        Schema::dropIfExists('batches');
    }
};
