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
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('role');
            }

            if (! Schema::hasColumn('users', 'linkedin_id')) {
                $table->string('linkedin_id')->nullable()->unique()->after('google_id');
            }

            if (! Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->unique()->after('linkedin_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'facebook_id')) {
                $table->dropUnique(['facebook_id']);
                $table->dropColumn('facebook_id');
            }

            if (Schema::hasColumn('users', 'linkedin_id')) {
                $table->dropUnique(['linkedin_id']);
                $table->dropColumn('linkedin_id');
            }
        });
    }
};
