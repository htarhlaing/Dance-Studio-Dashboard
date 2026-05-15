<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('studio_id')
                ->nullable()
                ->after('id')
                ->constrained('studios')
                ->nullOnDelete();

            $table->string('role', 32)->default('admin')->after('password');

            $table->index(['studio_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['studio_id', 'role']);
            $table->dropConstrainedForeignId('studio_id');
            $table->dropColumn('role');
        });
    }
};
