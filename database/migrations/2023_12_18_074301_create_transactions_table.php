<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create("transactions", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("trans_id")->unique();
			$table
				->foreignId("booking_id")
				->constrained("bookings", "booking_id")
				->cascadeOnUpdate()
				->cascadeOnDelete();

			$table->enum("status", ["pending", "success", "failed", "abandoned"])->default("pending");
			$table->string("reference")->unique();
			$table->float("amount")->default(0);
			$table->float("discount")->default(0);
			$table->unsignedBigInteger("subtotal")->default(0);
			$table->unsignedBigInteger("total")->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("transactions");
	}
};
