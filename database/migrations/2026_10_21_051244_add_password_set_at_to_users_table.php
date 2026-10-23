<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null berarti kata sandi belum pernah ditetapkan sendiri oleh
            // pengguna (mis. akun hasil auto-register OAuth dengan sandi acak).
            $table->timestamp('password_set_at')->nullable()->after('password');
        });

        // Akun lama (registrasi manual & seeder) sudah memiliki kata sandi
        // yang diketahui pemiliknya, jadi tandai sudah ditetapkan.
        DB::table('users')->whereNull('password_set_at')->update(['password_set_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set_at');
        });
    }
};
