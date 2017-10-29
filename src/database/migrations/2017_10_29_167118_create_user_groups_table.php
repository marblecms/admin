<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('allowedClasses', 512)->default('a:1:{s:3:"all";i:1;}');
            $table->integer("entryNodeId")->default(0);
            
            $table->tinyInteger("createUser")->default(0);
            $table->tinyInteger("createGroup")->default(0);
            $table->tinyInteger("createClass")->default(0);
            
            $table->tinyInteger("deleteUser")->default(0);
            $table->tinyInteger("deleteGroup")->default(0);
            $table->tinyInteger("deleteClass")->default(0);
            
            $table->tinyInteger("editUser")->default(0);
            $table->tinyInteger("editGroup")->default(0);
            $table->tinyInteger("editClass")->default(0);
            
            $table->tinyInteger("listUser")->default(0);
            $table->tinyInteger("listGroup")->default(0);
            $table->tinyInteger("listClass")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_groups');
    }
}
