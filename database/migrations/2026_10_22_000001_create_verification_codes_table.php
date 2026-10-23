<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kode OTP 6 digit reusable untuk verifikasi email dan atur ulang kata sandi.
     *
     * Kode disimpan dalam bentuk hash (bukan plaintext) beserta metadata
     * keamanan: tujuan, masa berlaku, jumlah percobaan, waktu kirim terakhir,
     * dan waktu konsumsi (sekali pakai).
     */
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 32);
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            // Satu kode aktif per pengguna per tujuan.
            $table->unique(['user_id', 'purpose']);
            $table->index(['user_id', 'purpose', 'consumed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
