<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // 外部キー（users.id）
            $table->date('date');
            $table->time('clock_in')->nullable(); // 出勤時刻（nullable）
            $table->time('clock_out')->nullable(); // 退勤時刻（nullable）
            $table->decimal('total_hours', 5, 2)->nullable(); // 総労働時間（nullable）
            $table->enum('status', ['waiting_approval', 'approved'])->default('waiting_approval'); // 勤怠ステータス

            $table->timestamps(); // created_at, updated_at

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
