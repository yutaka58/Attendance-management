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
            $table->foreignID('user_id')->constrained()->onDelete('cascade');
            $table->string('work_action_id');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('rest_start')->nullable();
            $table->string('rest_end')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('status')->default(0);
            $table->time('total_rest_time')->nullable();
            $table->time('work_time')->nullable();
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
        Schema::dropIfExists('attendances');
    }
}
