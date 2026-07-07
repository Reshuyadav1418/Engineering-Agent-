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

            {{-- GitLab --}}
            <div>
                <label class="form-label" style="margin-bottom:6px; display:block;">GitLab Username</label>
                <input type="text" name="gitlab_username" value="{{ $filters['gitlab_username'] ?? '' }}"
                       placeholder="e.g. torvalds…"
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
                <span style="font-size:12px; color:var(--text-muted);">— {{ $employees->total() }} {{ $employees->total() === 1 ? 'result' : 'results' }}</span>
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
                        <th>
                            <span style="display:inline-flex; align-items:center; gap:5px;">
                                <svg width="13" height="13" viewBox="0 0 380 380" fill="#6366f1"><path d="M282.83 170.73l-.27-.69-26.14-68.22a6.81 6.81 0 00-13.12.45l-17.68 54.16H154.36L136.69 102.27a6.81 6.81 0 00-13.12-.44L97.44 170l-.26.69a48.54 48.54 0 0016.1 56.1l.09.07.24.17 39.82 29.82 19.7 14.91 12 9.06a7.85 7.85 0 009.42 0l12-9.06 19.7-14.91 40.06-30 .1-.08a48.56 48.56 0 0016.16-56.04z"/></svg>
                                GitHub
                            </span>
                        </th>
                        <th>
                            <span style="display:inline-flex; align-items:center; gap:5px;">
                                <svg width="13" height="13" viewBox="0 0 380 380" fill="#e2502a"><path d="M282.83 170.73l-.27-.69-26.14-68.22a6.81 6.81 0 00-13.12.45l-17.68 54.16H154.36L136.69 102.27a6.81 6.81 0 00-13.12-.44L97.44 170l-.26.69a48.54 48.54 0 0016.1 56.1l.09.07.24.17 39.82 29.82 19.7 14.91 12 9.06a7.85 7.85 0 009.42 0l12-9.06 19.7-14.91 40.06-30 .1-.08a48.56 48.56 0 0016.16-56.04z"/></svg>
                                GitLab
                            </span>
                        </th>
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
                                    <a href="https://github.com/{{ $employee->github_username }}" target="_blank"
                                       style="display:inline-flex; align-items:center; gap:4px; color:#818cf8; font-size:12.5px; text-decoration:none; font-weight:600;"
                                       onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#818cf8'">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                                        @{{ $employee->github_username }}
                                    </a>
                                @else
                                    <span style="color:#334155; font-size:12px; font-style:italic;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($employee->gitlab_username)
                                    <a href="{{ rtrim(config('services.gitlab.url', 'https://gitlab.com'), '/') }}/{{ $employee->gitlab_username }}" target="_blank"
                                       style="display:inline-flex; align-items:center; gap:4px; color:#e2502a; font-size:12.5px; text-decoration:none; font-weight:600;"
                                       onmouseover="this.style.color='#fc8c5a'" onmouseout="this.style.color='#e2502a'">
                                        <svg width="12" height="12" viewBox="0 0 380 380" fill="currentColor"><path d="M282.83 170.73l-.27-.69-26.14-68.22a6.81 6.81 0 00-13.12.45l-17.68 54.16H154.36L136.69 102.27a6.81 6.81 0 00-13.12-.44L97.44 170l-.26.69a48.54 48.54 0 0016.1 56.1l.09.07.24.17 39.82 29.82 19.7 14.91 12 9.06a7.85 7.85 0 009.42 0l12-9.06 19.7-14.91 40.06-30 .1-.08a48.56 48.56 0 0016.16-56.04z"/></svg>
                                        {{ '@' . $employee->gitlab_username }}
                                    </a>
                                @else
                                    <span style="color:#334155; font-size:12px; font-style:italic;">—</span>
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
                            <td colspan="7" style="text-align:center; padding:60px 24px;">
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

        {{-- Pagination Summary Row --}}
        @if($employees->total() > 0)
        <div style="padding:14px 24px; border-top:1px solid rgba(99,102,241,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <span style="font-size:12px; color:var(--text-muted);">
                Showing
                <strong style="color:var(--text-primary);">{{ $employees->firstItem() }}</strong>
                –
                <strong style="color:var(--text-primary);">{{ $employees->lastItem() }}</strong>
                of
                <strong style="color:var(--text-primary);">{{ $employees->total() }}</strong>
                employees
            </span>
            <div style="display:flex; align-items:center; gap:6px;">
                {{-- Previous button --}}
                @if($employees->onFirstPage())
                    <span style="display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; color:var(--text-muted); background:rgba(99,102,241,0.04); border:1px solid rgba(99,102,241,0.1); cursor:not-allowed;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </span>
                @else
                    <a href="{{ $employees->withQueryString()->previousPageUrl() }}" style="display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; color:#818cf8; background:rgba(99,102,241,0.07); border:1px solid rgba(99,102,241,0.15); text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='rgba(99,102,241,0.14)'" onmouseout="this.style.background='rgba(99,102,241,0.07)'">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </a>
                @endif

                {{-- Page number pills --}}
                @foreach($employees->withQueryString()->getUrlRange(max(1, $employees->currentPage()-2), min($employees->lastPage(), $employees->currentPage()+2)) as $page => $url)
                    @if($page === $employees->currentPage())
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:700; background:var(--accent,#6366f1); color:#fff; border:1px solid transparent; box-shadow:0 2px 8px rgba(99,102,241,0.25);">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:12px; font-weight:600; color:var(--text-muted); background:rgba(99,102,241,0.05); border:1px solid rgba(99,102,241,0.12); text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='rgba(99,102,241,0.12)'; this.style.color='#818cf8'" onmouseout="this.style.background='rgba(99,102,241,0.05)'; this.style.color='var(--text-muted)'">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next button --}}
                @if($employees->hasMorePages())
                    <a href="{{ $employees->withQueryString()->nextPageUrl() }}" style="display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; color:#818cf8; background:rgba(99,102,241,0.07); border:1px solid rgba(99,102,241,0.15); text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='rgba(99,102,241,0.14)'" onmouseout="this.style.background='rgba(99,102,241,0.07)'">
                        Next
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span style="display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; color:var(--text-muted); background:rgba(99,102,241,0.04); border:1px solid rgba(99,102,241,0.1); cursor:not-allowed;">
                        Next
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
