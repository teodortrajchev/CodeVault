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
        abort_unless($project->roleFor($request->user()), 403);

        return view('tasks.create', compact('project'));
    }


    public function store(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $task = Task::create($data + ['project_id' => $project->id,]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task created.');
    }

   
    public function show(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $project->load('members', 'tasks');

        return view('projects.show', compact('project'));
    }
}