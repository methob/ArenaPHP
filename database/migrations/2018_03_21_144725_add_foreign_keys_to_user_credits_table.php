<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_credits', function(Blueprint $table)
		{
			$table->foreign('user_shop_id', 'fk_user_credits_user_shop_id')->references('id')->on('user_shop')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_credits', function(Blueprint $table)
		{
			$table->dropForeign('fk_user_credits_user_shop_id');
		});
	}

}
