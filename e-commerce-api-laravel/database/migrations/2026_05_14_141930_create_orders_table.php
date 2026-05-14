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

			$table->string('order_number')->unique();
			$table->enum('status', [
				'pending',
				'processing',
				'shipped',
				'delivered',
				'cancelled'
			])->default('pending');
			$table->decimal('subtotal', 10, 2);
			$table->decimal('tax', 10, 2)->default(0);
			$table->decimal('shipping', 10, 2)->default(0);
			$table->decimal('total', 10, 2);

			$table->string('shipping_name');
			$table->string('shipping_email');
			$table->string('shipping_phone');
			$table->text('shipping_address');
			$table->string('shipping_city');
			$table->string('shipping_state');
			$table->string('shipping_zip');
			$table->string('shipping_country');

			$table->text('notes')->nullable();
			$table->timestamp('completed_at')->nullable();

			// Foreign
			$table->foreignId('user_id')->constrained()->onDelete('cascade');

			// Index
			$table->index('order_number');
			$table->index('status');
			$table->index('user_id');

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
