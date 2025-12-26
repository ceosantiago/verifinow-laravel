<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('verification_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('verification_id');
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('response_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('verification_id')
                ->references('id')
                ->on('verifications')
                ->onDelete('cascade');

            $table->index(['verification_id', 'attempt_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_attempts');
    }
};
