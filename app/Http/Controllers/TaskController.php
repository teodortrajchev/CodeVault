<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    
    public function index(Request $request)
    {
        $projectIds = $request->user()->projects()->pluck('projects.id');

        $tasks = Task::whereIn('project_id', $projectIds)
            ->latest('created_at')
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    
    public function create(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);

        return view('tasks.create', compact('project'));
    }

    
    public function store(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
        ]);

        $task = Task::create($data + [
            'project_id' => $project->id,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task created.');
    }

  
    public function edit(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_unless($task->project_id === $project->id, 404);

        return view('tasks.edit', compact('project', 'task'));
    }

    public function update(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canContribute(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'in:todo,in_progress,done'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
        ]);

        $task->update($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task updated.');
    }

    public function destroy(Request $request, Project $project, Task $task)
    {
        abort_unless($project->roleFor($request->user())?->canManage(), 403);
        abort_unless($task->project_id === $project->id, 404);

        $task->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task deleted.');
    }

 
    public function show(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $project->load('members', 'tasks');

        return view('projects.show', compact('project'));
    }
}