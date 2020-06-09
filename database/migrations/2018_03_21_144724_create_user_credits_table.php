<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_credits', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->boolean('is_declined');
			$table->float('value', 10, 0);
			$table->integer('user_shop_id')->index('fk_user_credits_user_shop_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_credits');
	}

}
