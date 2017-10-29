<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('class_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('namedIdentifier');
            $table->text('configuration');
            $table->integer('classId');
            $table->integer('attributeId');
            $table->integer('groupId');
            $table->integer('sortOrder');
            $table->tinyInteger('translate');
            $table->tinyInteger('locked');
            $table->tinyInteger('showName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('class_attributes');
    }
}
