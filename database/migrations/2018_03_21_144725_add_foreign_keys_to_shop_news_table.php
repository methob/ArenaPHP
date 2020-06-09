<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToShopNewsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('shop_news', function(Blueprint $table)
		{
			$table->foreign('shop_id')->references('id')->on('shop')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('shop_news', function(Blueprint $table)
		{
			$table->dropForeign('shop_news_shop_id_foreign');
		});
	}

}
