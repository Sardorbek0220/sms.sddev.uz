<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRoleAndOperatorIdToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 32)->default('operator')->after('password');
            }

            if (!Schema::hasColumn('users', 'operator_id')) {
                $table->unsignedBigInteger('operator_id')->nullable()->after('role');
                $table->unique('operator_id', 'users_operator_id_unique');
            }
        });

        DB::table('users')->whereIn('email', ['admin@gmail.com', 'sardor@gmail.com'])->update(['role' => 'admin']);
        DB::table('users')->whereNotIn('email', ['admin@gmail.com', 'sardor@gmail.com'])->update(['role' => 'operator']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'operator_id')) {
                $table->dropUnique('users_operator_id_unique');
                $table->dropColumn('operator_id');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
}
