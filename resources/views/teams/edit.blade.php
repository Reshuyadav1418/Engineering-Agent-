@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:800px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <a href="{{ route('teams.index') }}" class="btn-back">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m7 7l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">Edit Team: {{ $team->name }}</h1>
            <p class="page-subtitle">Update the team's configuration, change lead, or manage members.</p>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="form-section">
        <form method="POST" action="{{ route('teams.update', $team) }}">
            @csrf
            @method('PUT')

            <div style="display:flex; flex-direction:column; gap:20px;">
                {{-- Team Name --}}
                <div>
                    <label class="form-label" for="name">Team Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" class="form-input" required>
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-input" style="resize:vertical;">{{ old('description', $team->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Team Lead --}}
                <div>
                    <label class="form-label" for="team_lead_id">Team Lead</label>
                    <select id="team_lead_id" name="team_lead_id" class="form-input">
                        <option value="">-- No Lead Assigned --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('team_lead_id', $team->team_lead_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->role }})
                            </option>
                        @endforeach
                    </select>
                    @error('team_lead_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="divider" style="margin: 10px 0;">

                {{-- Team Members Sub-form using AlpineJS --}}
                <div x-data="{ members: @js(old('members', $team->members->map(fn($m) => ['employee_id' => $m->id, 'role' => $m->pivot->role])->toArray())) }">
                    <div style="display:flex; align-items:center; justify-content:between; margin-bottom:14px;">
                        <label class="form-label" style="margin-bottom:0;">Team Members</label>
                        <button type="button" @click="members.push({ employee_id: '', role: 'Developer' })" class="btn-ghost" style="padding:6px 12px; font-size:12px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:2px; display:inline-block; vertical-align:middle;"><path d="M12 5v14M5 12h14"/></svg>
                            Add Member
                        </button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <template x-for="(member, index) in members" :key="index">
                            <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:12px; align-items:center; background:rgba(255,255,255,0.02); border:1px solid var(--border-subtle); padding:10px 14px; border-radius:10px;">
                                {{-- Employee Select --}}
                                <div>
                                    <select :name="'members['+index+'][employee_id]'" x-model="member.employee_id" class="form-input" style="font-size:13px; padding:8px 12px;" required>
                                        <option value="">-- Select Employee --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Role Input --}}
                                <div>
                                    <input type="text" :name="'members['+index+'][role]'" x-model="member.role" placeholder="e.g. Lead, Developer, Reviewer" class="form-input" style="font-size:13px; padding:8px 12px;" required>
                                </div>

                                {{-- Delete Button --}}
                                <div>
                                    <button type="button" @click="members.splice(index, 1)" class="btn-danger" style="padding:8px; display:flex; align-items:center; justify-content:center;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="members.length === 0" style="text-align:center; padding:20px; border:1px dashed var(--border-subtle); border-radius:10px; color:var(--text-muted); font-size:13px;">
                            No members added to this team yet. Click "Add Member" to add employees.
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
                    <a href="{{ route('teams.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </div>

        </form>
    </div>

</div>
@endsection
