<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('orders', function (Blueprint $table) {
    $table->id();

    $table->foreignId('restaurant_id')
        ->constrained('restaurants')
        ->cascadeOnDelete();

    $table->foreignId('table_id')
        ->nullable()
        ->constrained('tables')
        ->nullOnDelete();

    $table->string('order_code')->unique();

    $table->string('customer_name')->nullable();

    $table->text('note')->nullable();

    $table->decimal('total', 12, 2)->default(0);

    $table->enum('status', [
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ])->default('pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
