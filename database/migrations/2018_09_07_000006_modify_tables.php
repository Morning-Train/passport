<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('user_id');
		});

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('user_id');

			$table->index(['user_id', 'user_type']);
		});

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('user_id');

			$table->index(['user_id', 'user_type']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
			$table->dropColumn('user_type');
		});

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
			$table->dropColumn('user_type');

			$table->dropIndex(['user_id', 'user_type']);
		});

        Schema::table('oauth_clients', function (Blueprint $table) {
			$table->dropColumn('user_type');

			$table->dropIndex(['user_id', 'user_type']);
		});
    }

}
