<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserCreditsDeclinedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_credits_declined', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('message', 65535)->nullable();
			$table->integer('answer_option')->nullable();
			$table->integer('user_credit')->index('user_credit_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_credits_declined');
	}

}
