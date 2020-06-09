<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopNewsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shop_news', function(Blueprint $table)
		{
			$table->integer('id')->primary();
			$table->text('title', 65535)->nullable();
			$table->text('description', 65535)->nullable();
			$table->text('image', 65535)->nullable();
			$table->integer('shop_id')->index('shop_news_shop_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop_news');
	}

}
