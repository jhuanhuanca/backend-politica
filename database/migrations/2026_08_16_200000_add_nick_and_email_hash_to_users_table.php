<?php

use App\Support\IdentityHasher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nick')->nullable()->unique()->after('name');
            $table->string('email_hash', 64)->nullable()->unique()->after('email');
        });

        $hasher = app(IdentityHasher::class);

        DB::table('users')->orderBy('id')->each(function (object $user) use ($hasher): void {
            $base = Str::limit(trim((string) $user->name), 40, '') ?: 'user';
            $nick = $base.'-'.$user->id;

            DB::table('users')->where('id', $user->id)->update([
                'nick' => $nick,
                'email_hash' => $hasher->hashEmail((string) $user->email),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nick']);
            $table->dropUnique(['email_hash']);
            $table->dropColumn(['nick', 'email_hash']);
        });
    }
};
