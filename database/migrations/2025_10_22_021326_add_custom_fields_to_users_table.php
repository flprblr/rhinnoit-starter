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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('pin')->nullable()->after('remember_token');
            $table->boolean('status')->after('pin')->default(true);
            $table->string('dni')->unique()->after('status')->nullable();
            $table->string('phone')->after('dni')->nullable();
            $table->string('google_id')->nullable()->unique()->after('phone');
            $table->string('avatar')->nullable()->after('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dni', 'phone', 'google_id', 'avatar']);
        });
    }
};
