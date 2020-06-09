<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserShopTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_shop', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->unsigned()->index('user_shop_user_id_foreign');
			$table->integer('shop_id')->index('user_shop_shop_id_foreign');
			$table->boolean('is_follow')->default(0);
			$table->float('rating', 10, 0)->default(0);
			$table->float('current_credit', 10, 0)->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_shop');
	}

}
