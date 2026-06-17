@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:1200px;">

    {{-- Header --}}
    <div class="section-header" style="margin-bottom:24px;">
        <div>
            <h1 class="page-title">Employees</h1>
            <p class="page-subtitle">Manage all developers in your engineering team.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Add Employee
        </a>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert-success" style="margin-bottom:18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter / Search Panel --}}
    <div class="form-section" style="margin-bottom:22px; padding:18px 20px;">
        <form method="GET" action="{{ route('employees.index') }}" id="filter-form"
              style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:12px; align-items:flex-end;">

            {{-- Name --}}
            <div>
                <label class="form-label" style="margin-bottom:6px; display:block;">Name</label>
                <input type="text" name="name" value="{{ $filters['name'] ?? '' }}"
                       placeholder="Search by name…"
                       class="form-input" style="padding:8px 12px; font-size:13px;">
            </div>

            {{-- Department --}}
            <div>
                <label class="form-label" style="margin-bottom:6px; display:block;">Department</label>
                <input type="text" name="department" value="{{ $filters['department'] ?? '' }}"
                       placeholder="e.g. Backend…"
                       class="form-input" style="padding:8px 12px; font-size:13px;">
            </div>

            {{-- Role --}}
            <div>
                <label class="form-label" style="margin-bottom:6px; display:block;">Role</label>
                <input type="text" name="role" value="{{ $filters['role'] ?? '' }}"
                       placeholder="e.g. Senior Dev…"
                       class="form-input" style="padding:8px 12px; font-size:13px;">
            </div>

            {{-- GitHub --}}
            <div>
                <label class="form-label" style="margin-bottom:6px; display:block;">GitHub Username</label>
                <input type="text" name="github_username" value="{{ $filters['github_username'] ?? '' }}"
                       placeholder="e.g. octocat…"
                       class="form-input" style="padding:8px 12px; font-size:13px;">
            </div>

            {{-- Buttons --}}
            <div style="display:flex; gap:8px; align-items:center;">
                <button type="submit" class="btn-primary" style="flex:1; justify-content:center; padding:8px 14px; font-size:13px; cursor:pointer; font-family:inherit;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    Search
                </button>
                @if(array_filter($filters ?? []))
                    <a href="{{ route('employees.index') }}" class="btn-secondary" style="flex:1; text-align:center; padding:8px 14px; font-size:13px;">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Active filter badges --}}
        @php $activeFilters = array_filter($filters ?? []); @endphp
        @if($activeFilters)
            <div style="margin-top:12px; display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                <span style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Filters:</span>
                @foreach($activeFilters as $key => $val)
                    <span style="display:inline-flex; align-items:center; gap:5px; background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); border-radius:6px; padding:3px 9px; font-size:12px; color:#818cf8; font-weight:500;">
                        {{ ucfirst(str_replace('_', ' ', $key)) }}: <em style="color:var(--text-primary); font-style:normal;">{{ $val }}</em>
                    </span>
                @endforeach
                <span style="font-size:12px; color:var(--text-muted);">— {{ count($employees) }} {{ count($employees) === 1 ? 'result' : 'results' }}</span>
            </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="table-container">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Employee</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>GitHub</th>
                        <th style="text-align:right; padding-right:24px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td style="padding-left:24px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar avatar-sm">
                                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p style="font-size:13.5px; font-weight:700; color:var(--text-primary); white-space:nowrap;">#{{ $employee->id }} — {{ $employee->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="dept-tag">{{ $employee->department }}</span>
                            </td>
                            <td style="color:var(--text-secondary); font-size:13px;">{{ $employee->role }}</td>
                            <td style="color:var(--text-muted); font-size:12.5px; font-family:monospace;">{{ $employee->email }}</td>
                            <td>
                                @if($employee->github_username)
                                    <a href="https://github.com/{{ $employee->github_username }}" target="_blank" style="color:#818cf8; font-size:12.5px; text-decoration:none; font-weight:600;" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#818cf8'">
                                        @{{ $employee->github_username }}
                                    </a>
                                @else
                                    <span style="color:#334155; font-size:12px; font-style:italic;">None</span>
                                @endif
                            </td>
                            <td style="text-align:right; padding-right:24px; white-space:nowrap;">
                                <div style="display:flex; align-items:center; gap:6px; justify-content:flex-end;">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn-ghost" style="font-size:12px; padding:5px 10px;">View</a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn-warn" style="font-size:12px; padding:5px 10px;">Edit</a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this employee? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="font-size:12px; padding:5px 10px; cursor:pointer; font-family:inherit;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:60px 24px;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                    <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15); display:flex; align-items:center; justify-content:center;">
                                        <svg width="22" height="22" fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                    </div>
                                    <div>
                                        @if(array_filter($filters ?? []))
                                            <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No employees match your filters</p>
                                            <p style="font-size:12.5px; color:#334155; margin-top:4px;">Try adjusting or <a href="{{ route('employees.index') }}" style="color:#818cf8;">clearing</a> the search filters.</p>
                                        @else
                                            <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No employees registered</p>
                                            <p style="font-size:12.5px; color:#334155; margin-top:4px;">Click "Add Employee" above to register your first team member.</p>
                                        @endif
                                    </div>
                                    @if(!array_filter($filters ?? []))
                                        <a href="{{ route('employees.create') }}" class="btn-primary" style="margin-top:6px; font-size:13px;">Add Employee</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
