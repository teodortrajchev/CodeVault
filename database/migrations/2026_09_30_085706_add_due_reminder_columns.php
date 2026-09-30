<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['tasks', 'projects'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('due_soon_notified_at')->nullable();
                $table->timestamp('due_today_notified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['tasks', 'projects'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['due_soon_notified_at', 'due_today_notified_at']);
            });
        }
    }
};