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
		Schema::create("hotel_rooms", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("room_id")->unique();

			$table->string("name");
			$table->text("desc");
			$table->text("image");
			$table->float("price")->default(0);
			$table->text("images")->nullable();
			$table->enum("status", ["active", "suspended", "reserved"])->default("active");

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("hotel_rooms");
	}
};