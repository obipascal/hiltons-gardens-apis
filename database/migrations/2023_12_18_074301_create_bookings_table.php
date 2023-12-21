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
		Schema::create("bookings", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("booking_id")->unique();
			$table
				->foreignId("account_id")
				->constrained("users", "account_id")
				->cascadeOnUpdate()
				->cascadeOnDelete();
			$table
				->foreignId("room_id")
				->constrained("hotel_rooms", "room_id")
				->cascadeOnUpdate()
				->cascadeOnDelete();
			$table->foreignId("trans_id")->nullable();

			// $table->integer("guest")->default(1);
			$table->integer("duration")->default(1);
			$table->integer("num_rooms")->default(1);
			$table->dateTime("checkin")->nullable();
			$table->dateTime("checkout")->nullable();
			$table->enum("status", ["pending", "active", "completed", "cancled"])->default("pending");
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("bookings");
	}
};
