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
		Schema::create("users", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("account_id")->unique();

			$table->string("first_name")->nullable();
			$table->string("last_name")->nullable();

			$table->string("email")->unique();
			$table->string("phone_number")->nullable();
			$table->string("billing_address")->nullable();
			$table->boolean("is_verified")->default(0);

			$table->enum("role", ["system_admin", "admin", "user"])->default("user");

			$table->text("password");
			$table->text("access_token")->nullable();

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("users");
	}
};
