<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->string('attendance_id')->nullable();
            $table->foreignID('user_id')->constrained()->onDelete('cascade');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('rest_start')->nullable();
            $table->string('rest_end')->nullable();
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
        Schema::dropIfExists('correction_request');
    }
}
