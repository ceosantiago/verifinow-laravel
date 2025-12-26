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
        Schema::create('user_authentications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('verification_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('authentication_id')->unique();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('result', ['approved', 'rejected', 'pending'])->default('pending');
            $table->float('confidence_score')->default(0);
            $table->float('liveness_score')->default(0);
            $table->float('face_match_score')->default(0);
            $table->json('device_info')->nullable();
            $table->json('location_data')->nullable();
            $table->timestamp('authenticated_at')->nullable();
            $table->timestamps();

            $table->foreign('verification_id')
                ->references('id')
                ->on('verifications')
                ->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index(['authentication_id']);
            $table->index(['status', 'result']);
        });
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('user_authentications');
    }
};
