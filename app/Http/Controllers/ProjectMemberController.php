<?php

namespace App\Http\Controllers;


use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        abort_unless($actorRole?->canChangeRoles(), 403);
        abort_unless($project->members->contains('id', $user->id), 404);

        $data = $request->validateWithBag('roleUpdate', [
            'role' => ['required', Rule::enum(ProjectRole::class)],
        ]);

        $currentRole = $project->roleFor($user);
        $newRole = ProjectRole::from($data['role']);

        if ($currentRole === $newRole) {
            return redirect()->route('projects.show', $project);
        }

        if ($currentRole === ProjectRole::Owner) {
            $ownerCount = $project->members()->wherePivot('role', ProjectRole::Owner->value)->count();

            if ($ownerCount <= 1) {
                return back()->withErrors(
                    ['role' => 'A project must have at least one owner. Promote someone else to owner first.'],
                    'roleUpdate'
                );
            }
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $newRole->value]);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "{$user->name} is now {$newRole->label()}.");
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
