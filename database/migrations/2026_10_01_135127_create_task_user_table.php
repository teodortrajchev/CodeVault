<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
        });

        // Carry over existing single assignees.
        DB::table('task_user')->insertUsing(
            ['task_id', 'user_id', 'created_at', 'updated_at'],
            DB::table('tasks')
                ->whereNotNull('assigned_to')
                ->select('id', 'assigned_to', DB::raw('CURRENT_TIMESTAMP'), DB::raw('CURRENT_TIMESTAMP'))
        );

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        });

        // Keep the first assignee of each task.
        DB::table('tasks')->orderBy('id')->each(function ($task) {
            $first = DB::table('task_user')->where('task_id', $task->id)->orderBy('id')->value('user_id');

            if ($first) {
                DB::table('tasks')->where('id', $task->id)->update(['assigned_to' => $first]);
            }
        });

        Schema::dropIfExists('task_user');
    }
};