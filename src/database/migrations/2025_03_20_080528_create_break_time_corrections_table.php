<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimeCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_time_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('break_time_id')->constrained('break_times')->onDelete('cascade');
            $table->time('requested_break_start')->nullable();
            $table->time('requested_break_end')->nullable();
            $table->text('request_reason')->nullable();
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
        Schema::dropIfExists('break_time_corrections');
    }
}
