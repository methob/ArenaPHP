<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profiles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('name', 65535)->nullable();
			$table->string('gender')->nullable();
			$table->string('email', 191)->nullable()->unique('email');
			$table->integer('city_id')->unsigned()->nullable()->index('orofiles_city_id_foreign');
			$table->text('neighborhood', 65535)->nullable();
			$table->integer('state_id')->unsigned()->nullable()->index('orofiles_states_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('profiles');
	}

}
