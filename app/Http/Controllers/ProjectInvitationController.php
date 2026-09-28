<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Notifications\ProjectInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectInvitationController extends Controller
{
    //owner sends an invitation. 
    public function store(Request $request, Project $project)
    {
        $actorRole = $project->roleFor($request->user());
        abort_unless($actorRole?->canManage(), 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(ProjectRole::class)],
        ]);

        if ($data['role'] === ProjectRole::Owner->value && $actorRole !== ProjectRole::Owner) {
            return back()->withErrors(['role' => 'Only an owner can invite someone as an owner.'])->withInput();
        }

        $email = Str::lower($data['email']);

        if ($project->members()->whereRaw('lower(users.email) = ?', [$email])->exists()) {
            return back()->withErrors(['email' => 'That person is already a member of this project.'])->withInput();
        }

        $token = Str::random(64);

        $invitation = DB::transaction(function () use ($project, $request, $email, $data, $token) {
            //resend replace old invitation
            $project->invitations()->whereNull('accepted_at')->where('email', $email)->delete();

            return $project->invitations()->create([
                'invited_by' => $request->user()->id,
                'email' => $email,
                'role' => $data['role'],
                'token' => ProjectInvitation::hashToken($token),
                'expires_at' => now()->addDays(7),
            ]);
        });

        Notification::route('mail', $email)->notify(new ProjectInvitationNotification($invitation, $token));

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "Invitation sent to {$email}.");
    }

    //owner revokes an invitation.
    public function destroy(Request $request, Project $project, ProjectInvitation $invitation)
    {
        abort_unless($project->roleFor($request->user())?->canManage(), 403);
        abort_unless($invitation->project_id === $project->id, 404);

        $invitation->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Invitation revoked.');
    }

    public function show(Request $request, string $token)
    {
        $invitation = $this->findByToken($token);
        $state = $this->stateFor($invitation, $request);

        return view('invitations.show', compact('invitation', 'state', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findByToken($token);

        if ($this->stateFor($invitation, $request) !== 'ready') {
            return redirect()->route('invitations.show', $token);
        }

        $user = $request->user();
        $project = $invitation->project;

        DB::transaction(function () use ($invitation, $project, $user) {
            if (! $project->members()->where('users.id', $user->id)->exists()) {
                $project->members()->attach($user->id, ['role' => $invitation->role]);
            }

            $invitation->update(['accepted_at' => now()]);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "You joined {$project->name}.");
    }

    private function findByToken(string $token): ProjectInvitation
    {
        return ProjectInvitation::with(['project', 'inviter'])
            ->where('token', ProjectInvitation::hashToken($token))
            ->firstOrFail();
    }

    private function stateFor(ProjectInvitation $invitation, Request $request): string
    {
        return match (true) {
            $invitation->accepted_at !== null => 'used',
            $invitation->expires_at->isPast() => 'expired',
            strcasecmp($invitation->email, $request->user()->email) !== 0 => 'mismatch',
            default => 'ready',
        };
    }
}