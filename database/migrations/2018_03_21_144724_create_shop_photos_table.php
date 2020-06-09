<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopPhotosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shop_photos', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('filename', 65535)->nullable();
			$table->integer('shop_id')->nullable()->index('fk_shop_id_shop_photos_id_shop');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop_photos');
	}

}
