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
		Schema::create("reviews", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("review_id")->unique();
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

			$table->integer("rating")->default(0);
			$table->text("message")->nullable();

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("reviews");
	}
};
