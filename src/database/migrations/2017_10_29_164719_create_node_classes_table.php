<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNodeClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_classes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('groupId');
            $table->string('name');
            $table->string('namedIdentifier');
            $table->string('icon');
            $table->tinyInteger('allowChildren');
            $table->tinyInteger('listChildren');
            $table->tinyInteger('locked');
            $table->tinyInteger('showInTree');
            $table->tinyInteger('tabs');
            $table->text('allowedChildClasses');
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_classes');
    }
}
