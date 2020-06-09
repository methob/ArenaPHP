<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserCreditsDeclinedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_credits_declined', function(Blueprint $table)
		{
			$table->foreign('user_credit', 'user_credit_id_foreign')->references('id')->on('user_credits')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_credits_declined', function(Blueprint $table)
		{
			$table->dropForeign('user_credit_id_foreign');
		});
	}

}
