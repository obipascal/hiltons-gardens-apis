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
		Schema::create("payment_methods", function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger("pay_method_id")->unique();
			$table
				->foreignId("account_id")
				->constrained("users", "account_id")
				->cascadeOnUpdate()
				->cascadeOnDelete();

			$table
				->string("reference")
				->nullable()
				->unique();
			$table
				->text("auth_token")
				->nullable()
				->unique();

			$table->enum("status", ["active", "inactive"])->default("inactive");

			$table->string("bin")->nullable();
			$table->string("last4")->nullable();
			$table->string("exp_month")->nullable();
			$table->string("exp_year")->nullable();
			$table->string("card_type")->nullable();
			$table->string("bank")->nullable();
			$table->string("card_name")->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("payment_methods");
	}
};
