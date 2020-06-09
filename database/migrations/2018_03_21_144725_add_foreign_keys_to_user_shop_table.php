<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserShopTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_shop', function(Blueprint $table)
		{
			$table->foreign('shop_id')->references('id')->on('shop')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_shop', function(Blueprint $table)
		{
			$table->dropForeign('user_shop_shop_id_foreign');
			$table->dropForeign('user_shop_user_id_foreign');
		});
	}

}
