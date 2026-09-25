@php
    $actorRole = $project->roleFor(auth()->user());
    $canManage = $actorRole?->canManage() ?? false;
    $isOwner = $actorRole === \App\Enums\ProjectRole::Owner;
    $roleOptions = ['owner' => 'Owner', 'manager' => 'Manager', 'member' => 'Member', 'viewer' => 'Viewer'];
@endphp

<div class="mt-8">
    <h2 class="text-lg font-semibold mb-3">Members</h2>

    @if ($errors->any())
        <div class="mb-3 text-sm text-red-600">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table class="w-full text-sm border-collapse mb-4">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2">Name</th>
                <th class="py-2">Email</th>
                <th class="py-2">Role</th>
                @if ($canManage)
                    <th class="py-2"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($project->members as $member)
                @php
                    $memberRole = $project->roleFor($member);
                    // Only an Owner may change/remove another Owner's role.
                    $canEditThisRow = $canManage && ($memberRole !== \App\Enums\ProjectRole::Owner || $isOwner);
                @endphp
                <tr class="border-b">
                    <td class="py-2">{{ $member->name }}</td>
                    <td class="py-2">{{ $member->email }}</td>
                    <td class="py-2">
                        @if ($canEditThisRow)
                            <form method="POST"
                                  action="{{ route('projects.members.update', [$project, $member]) }}">
                                @csrf
                                @method('PUT')
                                <select name="role" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                    @foreach ($roleOptions as $value => $label)
                                        @if ($value !== 'owner' || $isOwner)
                                            <option value="{{ $value }}" @selected($member->pivot->role === $value)>
                                                {{ $label }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </form>
                        @else
                            {{ $roleOptions[$member->pivot->role] ?? ucfirst($member->pivot->role) }}
                        @endif
                    </td>
                    @if ($canManage)
                        <td class="py-2 text-right">
                            @if ($canEditThisRow)
                                <form method="POST"
                                      action="{{ route('projects.members.destroy', [$project, $member]) }}"
                                      onsubmit="return confirm('Remove this member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600">Remove</button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($canManage)
        <form method="POST" action="{{ route('projects.members.store', $project) }}" class="flex gap-2 items-end">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Add member by email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="mt-1 border rounded px-3 py-2" required>
            </div>
            <select name="role" class="border rounded px-3 py-2">
                @foreach ($roleOptions as $value => $label)
                    @if ($value !== 'owner' || $isOwner)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endif
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
        </form>
    @endif
</div>