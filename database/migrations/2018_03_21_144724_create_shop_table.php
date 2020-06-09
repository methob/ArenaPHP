<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShopTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shop', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('cep', 65535);
			$table->text('address', 65535);
			$table->integer('number');
			$table->text('complement', 65535);
			$table->text('neighborhood', 65535);
			$table->text('cnpj', 65535);
			$table->text('social_name', 65535);
			$table->text('nickname', 65535)->nullable();
			$table->text('owner', 65535)->nullable();
			$table->boolean('is_franchise');
			$table->text('email', 65535)->nullable();
			$table->text('phone_number', 65535)->nullable();
			$table->text('cellphone', 65535)->nullable();
			$table->text('facebook', 65535);
			$table->text('instagram', 65535);
			$table->text('website', 65535);
			$table->float('rating', 10, 0)->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('shop');
	}

}
