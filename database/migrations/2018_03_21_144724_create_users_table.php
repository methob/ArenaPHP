<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('cpf', 191)->unique('cpf');
			$table->string('email', 191)->nullable()->unique('email');
			$table->string('password');
			$table->integer('profile_id')->nullable()->index('users_profiles_id_foreign');
			$table->string('remember_token', 100)->nullable();
			$table->integer('user_type')->default(0);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
