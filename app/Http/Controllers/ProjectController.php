<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = $request->user()->projects()->latest('projects.created_at')->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $user = $request->user();

        $project = DB::transaction(function () use ($data, $user) {
            $project = Project::create($data + ['owner_id' => $user->id]);
            $project->members()->attach($user->id, ['role' => ProjectRole::Owner->value]);

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('status', 'Project created.');
    }

    public function show(Request $request, Project $project)
    {
        abort_unless($project->roleFor($request->user()), 403);

        $project->load('members');

        return view('projects.show', compact('project'));
    }
}
?>