<?php namespace Progforce\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Progforce\User\Models\User;

class UsersAddLastSeen extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->timestamp('last_seen')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('last_seen');
        });
    }
}
