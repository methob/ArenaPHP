<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProfilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('profiles', function(Blueprint $table)
		{
			$table->foreign('city_id', 'orofiles_city_id_foreign')->references('id')->on('cities')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('state_id', 'orofiles_states_id_foreign')->references('id')->on('states')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('profiles', function(Blueprint $table)
		{
			$table->dropForeign('orofiles_city_id_foreign');
			$table->dropForeign('orofiles_states_id_foreign');
		});
	}

}
