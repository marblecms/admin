<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dumpsPath = __DIR__ . "/../dumps/*";
        
        foreach(glob($dumpsPath) as $file){
            
            $sqlDump = file_get_contents($file);
            DB::unprepared($sqlDump);
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
