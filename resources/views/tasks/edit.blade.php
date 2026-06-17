@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:800px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
        <a href="{{ route('tasks.index') }}" class="btn-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">Edit Sprint Task: {{ $task->title }}</h1>
            <p class="page-subtitle">Modify task details, update hours, or edit team members.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="form-section" x-data="{ assignmentType: '{{ old('assignment_type', $task->isTeam() ? 'team' : 'individual') }}', members: @js(old('members', $task->members->map(fn($m) => [
        'employee_id' => $m->employee_id,
        'role' => $m->role,
        'assigned_hours' => $m->assigned_hours,
        'actual_hours' => $m->actual_hours,
        'status' => $m->status,
        'started_at' => $m->started_at ? $m->started_at->format('Y-m-d') : '',
        'completed_at' => $m->completed_at ? $m->completed_at->format('Y-m-d') : ''
    ])->toArray())) }">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" style="display:flex; flex-direction:column; gap:22px;">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div>
                <label for="title" class="form-label">Task Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $task->title) }}" required
                    class="form-input">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="form-label">Description <span style="color:var(--text-muted); font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                <textarea name="description" id="description" rows="3"
                    class="form-input" style="resize:vertical;">{{ old('description', $task->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Assignment Type Choice --}}
            <div>
                <label class="form-label">Assignment Type</label>
                <div style="display:flex; gap:20px; align-items:center; margin-top:6px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:14px;">
                        <input type="radio" name="assignment_type" value="individual" x-model="assignmentType" style="accent-color:#6366f1;">
                        Individual
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary); font-size:14px;">
                        <input type="radio" name="assignment_type" value="team" x-model="assignmentType" style="accent-color:#6366f1;">
                        Team
                    </label>
                </div>
            </div>

            {{-- Individual Assignee --}}
            <div x-show="assignmentType === 'individual'" x-transition>
                <label for="employee_id" class="form-label">Assigned Developer</label>
                <select name="employee_id" id="employee_id" :required="assignmentType === 'individual'" class="form-input" style="cursor:pointer;">
                    <option value="" disabled style="color:#000;">Select assignee…</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $task->employee_id) == $employee->id ? 'selected' : '' }} style="color:#000;">
                            {{ $employee->name }} ({{ $employee->role }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Team Assignee --}}
            <div x-show="assignmentType === 'team'" x-transition style="display:flex; flex-direction:column; gap:16px;">
                <div>
                    <label for="team_id" class="form-label">Assigned Team</label>
                    <select name="team_id" id="team_id" :required="assignmentType === 'team'" class="form-input" style="cursor:pointer;">
                        <option value="" disabled style="color:#000;">Select team…</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ old('team_id', $task->team_id) == $team->id ? 'selected' : '' }} style="color:#000;">
                                {{ $team->name }} ({{ $team->members->count() }} members)
                            </option>
                        @endforeach
                    </select>
                    @error('team_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Task Members List --}}
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <label class="form-label" style="margin-bottom:0;">Collaborating Members & Roles</label>
                        <button type="button" @click="members.push({ employee_id: '', role: 'Developer', assigned_hours: 0, actual_hours: 0, status: 'Pending', started_at: '', completed_at: '' })" class="btn-ghost" style="padding:5px 10px; font-size:12px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:2px; display:inline-block; vertical-align:middle;"><path d="M12 5v14M5 12h14"/></svg>
                            Add Member
                        </button>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <template x-for="(member, index) in members" :key="index">
                            <div style="display:flex; flex-direction:column; gap:10px; background:rgba(255,255,255,0.02); border:1px solid var(--border-subtle); padding:14px; border-radius:12px; position:relative;">
                                {{-- Row 1: Employee & Role --}}
                                <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:12px; align-items:center;">
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Employee</label>
                                        <select :name="'members['+index+'][employee_id]'" x-model="member.employee_id" class="form-input" style="font-size:12.5px; padding:6px 10px;" required>
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Task Role</label>
                                        <input type="text" :name="'members['+index+'][role]'" x-model="member.role" placeholder="e.g. Developer, Reviewer" class="form-input" style="font-size:12.5px; padding:6px 10px;" required>
                                    </div>
                                    <div style="align-self:end;">
                                        <button type="button" @click="members.splice(index, 1)" class="btn-danger" style="padding:7px; display:flex; align-items:center; justify-content:center;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Row 2: Hours & Status --}}
                                <div style="display:grid; grid-template-columns: 1fr 1fr 1.2fr; gap:12px; align-items:center;">
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Est. Hours</label>
                                        <input type="number" step="0.1" :name="'members['+index+'][assigned_hours]'" x-model="member.assigned_hours" class="form-input" style="font-size:12.5px; padding:6px 10px; font-family:monospace;" required>
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Act. Hours</label>
                                        <input type="number" step="0.1" :name="'members['+index+'][actual_hours]'" x-model="member.actual_hours" class="form-input" style="font-size:12.5px; padding:6px 10px; font-family:monospace;">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Status</label>
                                        <select :name="'members['+index+'][status]'" x-model="member.status" class="form-input" style="font-size:12.5px; padding:6px 10px; cursor:pointer;" required>
                                            <option value="Pending">Pending</option>
                                            <option value="In Progress">In Progress</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Row 3: Timestamps --}}
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Started At</label>
                                        <input type="date" :name="'members['+index+'][started_at]'" x-model="member.started_at" class="form-input" style="font-size:12px; padding:5px 10px; color-scheme:dark;">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:10px; margin-bottom:4px;">Completed At</label>
                                        <input type="date" :name="'members['+index+'][completed_at]'" x-model="member.completed_at" class="form-input" style="font-size:12px; padding:5px 10px; color-scheme:dark;">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="members.length === 0" style="text-align:center; padding:24px; border:1px dashed var(--border-subtle); border-radius:10px; color:var(--text-muted); font-size:13px;">
                            No collaborating members added yet. Click "Add Member" to assign developers to this team task.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Task Metrics --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label for="status" class="form-label">Overall Status</label>
                    <select name="status" id="status" required class="form-input" style="cursor:pointer;">
                        <option value="Pending"     {{ old('status', $task->status) == 'Pending'     ? 'selected' : '' }} style="color:#000;">Pending</option>
                        <option value="In Progress" {{ old('status', $task->status) == 'In Progress' ? 'selected' : '' }} style="color:#000;">In Progress</option>
                        <option value="Completed"   {{ old('status', $task->status) == 'Completed'   ? 'selected' : '' }} style="color:#000;">Completed</option>
                    </select>
                    @error('status')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="assigned_date" class="form-label">Assigned Date</label>
                    <input type="date" name="assigned_date" id="assigned_date"
                        value="{{ old('assigned_date', $task->assigned_date ? $task->assigned_date->format('Y-m-d') : date('Y-m-d')) }}" required
                        class="form-input" style="color-scheme:dark;">
                    @error('assigned_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                <div>
                    <label for="estimated_hours" class="form-label">Total Estimated Hours</label>
                    <input type="number" step="0.1" name="estimated_hours" id="estimated_hours"
                        value="{{ old('estimated_hours', $task->estimated_hours) }}" required
                        class="form-input" style="font-family:monospace;">
                    @error('estimated_hours')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="actual_hours" class="form-label">Total Actual Hours <span style="color:var(--text-muted); font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                    <input type="number" step="0.1" name="actual_hours" id="actual_hours"
                        value="{{ old('actual_hours', $task->actual_hours) }}"
                        class="form-input" style="font-family:monospace;">
                    @error('actual_hours')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="completed_date" class="form-label">Completed Date <span style="color:var(--text-muted); font-weight:400; text-transform:none; letter-spacing:0;">(optional)</span></label>
                <input type="date" name="completed_date" id="completed_date"
                    value="{{ old('completed_date', $task->completed_date ? $task->completed_date->format('Y-m-d') : '') }}"
                    class="form-input" style="color-scheme:dark;">
                @error('completed_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="divider" style="margin:4px 0;"></div>

            {{-- Buttons --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                <a href="{{ route('tasks.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" style="cursor:pointer; font-family:inherit;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Save Changes
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
