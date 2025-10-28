<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('publisher_name')->nullable();
            $table->longText('description')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('publisher_logo_id')->nullable();
            $table->unsignedBigInteger('publisher_cover_image_id')->nullable();
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->bigInteger('state_id')->unsigned()->nullable();
            $table->string('city')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('pinterest')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('publisher_logo_id')->references('id')->on('attachments')->onDelete('cascade');
            $table->foreign('publisher_cover_image_id')->references('id')->on('attachments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
