<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'militancia';

    public function up(): void
    {
        Schema::connection('militancia')->create('militants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('full_name');
            $table->string('ci', 32);
            $table->string('email');
            $table->string('phone', 32);
            $table->string('city');
            $table->string('department', 64);
            $table->unsignedSmallInteger('birth_year');
            $table->boolean('consent')->default(true);
            $table->string('card_number')->nullable()->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->unique('ci');
            $table->unique('email');
            $table->index(['department', 'city']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection('militancia')->dropIfExists('militants');
    }
};
