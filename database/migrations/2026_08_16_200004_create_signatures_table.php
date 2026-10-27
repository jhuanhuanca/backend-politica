<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email_hash', 64);
            $table->string('ci_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['vote_topic_id', 'user_id']);
            $table->unique(['vote_topic_id', 'email_hash']);
            $table->unique(['vote_topic_id', 'ci_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
