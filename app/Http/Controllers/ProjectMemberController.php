<?php

namespace App\Http\Controllers;


use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
     public function store(Request $request, Project $project)
    {
        $actorRole = $project->roleFor($request->user());
        abort_unless($actorRole?->canManage(), 403);
 
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'string', 'in:owner,manager,member,viewer'],
        ]);
 
        if ($data['role'] === ProjectRole::Owner->value && $actorRole !== ProjectRole::Owner) {
            return back()->withErrors(['role' => 'Only an owner can grant the owner role.']);
        }
 
        $user = User::where('email', $data['email'])->firstOrFail();
 
        if ($project->members->contains('id', $user->id)) {
            return back()->withErrors(['email' => 'That user is already a member of this project.']);
        }
 
        $project->members()->attach($user->id, ['role' => $data['role']]);
 
        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Member added.');
    }
 
  
    public function update(Request $request, Project $project, User $user)
    {
        $actorRole = $project->roleFor($request->user());
        abort_unless($actorRole?->canManage(), 403);
        abort_unless($project->members->contains('id', $user->id), 404);
 
        $data = $request->validate([
            'role' => ['required', 'string', 'in:owner,manager,member,viewer'],
        ]);
 
        $targetCurrentRole = $project->roleFor($user);
        $targetNewRole = ProjectRole::from($data['role']);
 
        if (($targetCurrentRole === ProjectRole::Owner || $targetNewRole === ProjectRole::Owner)
            && $actorRole !== ProjectRole::Owner) {
            return back()->withErrors(['role' => 'Only an owner can grant or revoke the owner role.']);
        }
 
        if ($targetCurrentRole === ProjectRole::Owner && $targetNewRole !== ProjectRole::Owner) {
            $ownerCount = $project->members()->wherePivot('role', ProjectRole::Owner->value)->count();
 
            if ($ownerCount <= 1) {
                return back()->withErrors(['role' => 'A project must have at least one owner.']);
            }
        }
 
        $project->members()->updateExistingPivot($user->id, ['role' => $data['role']]);
 
        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Member role updated.');
    }
 

    public function destroy(Request $request, Project $project, User $user)
    {
        $actorRole = $project->roleFor($request->user());
        abort_unless($actorRole?->canManage(), 403);
        abort_unless($project->members->contains('id', $user->id), 404);
 
        $targetRole = $project->roleFor($user);
 
        if ($targetRole === ProjectRole::Owner) {
            if ($actorRole !== ProjectRole::Owner) {
                return back()->withErrors(['role' => 'Only an owner can remove another owner.']);
            }
 
            $ownerCount = $project->members()->wherePivot('role', ProjectRole::Owner->value)->count();
 
            if ($ownerCount <= 1) {
                return back()->withErrors(['role' => 'A project must have at least one owner.']);
            }
        }
 
        $project->members()->detach($user->id);
 
        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Member removed.');
    }
}
