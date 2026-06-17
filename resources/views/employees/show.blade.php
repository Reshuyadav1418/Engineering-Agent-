@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:900px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:28px;">
        <a href="{{ route('employees.index') }}" class="btn-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="page-title">Developer Profile</h1>
            <p class="page-subtitle">Detailed profile and workload overview.</p>
        </div>
    </div>

    {{-- Main Grid --}}
    <div style="display:grid; grid-template-columns:280px 1fr; gap:20px; align-items:start;">

        {{-- Profile Card --}}
        <div class="table-container" style="padding:24px; display:flex; flex-direction:column; gap:20px;">

            {{-- Avatar & Info --}}
            <div style="text-align:center;">
                <div class="avatar avatar-xl" style="margin:0 auto 14px;">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <h2 style="font-size:16px; font-weight:800; color:var(--text-primary); letter-spacing:-0.02em;">{{ $employee->name }}</h2>
                <p style="font-size:12.5px; color:#818cf8; font-weight:600; margin-top:3px;">{{ $employee->role }}</p>
                <span class="dept-tag" style="margin-top:8px; display:inline-flex;">{{ $employee->department }}</span>
            </div>

            {{-- Details List --}}
            <div style="border-top:1px solid rgba(99,102,241,0.08); padding-top:16px; display:flex; flex-direction:column; gap:13px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11.5px; color:var(--text-muted); font-weight:500;">Email</span>
                    <a href="mailto:{{ $employee->email }}" style="font-size:12px; color:#818cf8; font-weight:600; text-decoration:none; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $employee->email }}</a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11.5px; color:var(--text-muted); font-weight:500;">GitHub</span>
                    @if($employee->github_username)
                        <a href="https://github.com/{{ $employee->github_username }}" target="_blank" style="font-size:12px; color:#818cf8; font-weight:600; text-decoration:none;">@{{ $employee->github_username }}</a>
                    @else
                        <span style="font-size:12px; color:#334155; font-style:italic;">Not set</span>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; flex-direction:column; gap:8px; border-top:1px solid rgba(99,102,241,0.08); padding-top:16px;">
                <a href="{{ route('employees.edit', $employee) }}" class="btn-ghost" style="justify-content:center; font-size:13px; padding:9px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Account
                </a>
                <a href="{{ route('ai.report.show', $employee) }}" style="display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:9px; border-radius:10px; background:rgba(16,185,129,0.08); color:#34d399; border:1px solid rgba(16,185,129,0.2); font-size:13px; font-weight:600; text-decoration:none; transition:all 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.14)'" onmouseout="this.style.background='rgba(16,185,129,0.08)'">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    View AI Report
                </a>
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Delete this developer? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger" style="width:100%; justify-content:center; cursor:pointer; font-family:inherit; font-size:13px; padding:9px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- Tasks Card --}}
        <div class="table-container">
            <div style="padding:18px 22px; border-bottom:1px solid rgba(99,102,241,0.08);">
                <h2 style="font-size:15px; font-weight:700; color:var(--text-primary); letter-spacing:-0.02em;">Assigned Sprint Tasks</h2>
                <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">Active tasks and backlog assignments</p>
            </div>

            <div style="overflow-y:auto; max-height:420px; padding:4px 0;">
                @forelse($employee->tasks ?? [] as $task)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:13px 22px; border-bottom:1px solid rgba(0,0,0,0.02); transition:background 0.15s; gap:12px;" onmouseover="this.style.background='rgba(99,102,241,0.04)'" onmouseout="this.style.background='transparent'">
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:13.5px; font-weight:600; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->title }}</p>
                            <p style="font-size:11.5px; color:var(--text-muted); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->description ?? 'No description provided.' }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                            @if ($task->status === 'Completed')
                                <span class="badge badge-completed"><span class="badge-dot"></span>Completed</span>
                            @elseif ($task->status === 'In Progress')
                                <span class="badge badge-in-progress"><span class="badge-dot"></span>In Progress</span>
                            @else
                                <span class="badge badge-pending"><span class="badge-dot"></span>Pending</span>
                            @endif
                            <span style="font-size:11.5px; color:var(--text-muted); font-family:monospace; font-weight:600;">{{ $task->estimated_hours }}h</span>
                        </div>
                    </div>
                @empty
                    <div style="padding:60px 22px; text-align:center;">
                        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        <p style="font-size:13px; font-weight:600; color:var(--text-muted);">No tasks assigned yet.</p>
                        <p style="font-size:12px; color:#334155; margin-top:4px;">Assign tasks from the Tasks module.</p>
                    </div>
                @endforelse
            </div>

            @if(count($employee->tasks ?? []) > 0)
                <div style="padding:14px 22px; border-top:1px solid rgba(99,102,241,0.08); display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:var(--text-muted);">
                        Total: <strong style="color:var(--text-primary);">{{ count($employee->tasks) }}</strong> tasks
                    </span>
                    <span style="font-size:12px; color:var(--text-muted);">
                        Completed: <strong style="color:#34d399;">{{ count($employee->tasks->where('status', 'Completed')) }}</strong>
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Attendance & Work Logs Section --}}
    <style>
        .attendance-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: stretch;
        }
        @media (max-width: 768px) {
            .attendance-grid {
                grid-template-columns: 1fr;
            }
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <div x-data="employeeAttendance({{ $employee->id }})" class="animate-fade-up" style="margin-top: 24px; display: flex; flex-direction: column; gap: 20px; margin-bottom: 24px;">
        
        {{-- Section Title --}}
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; border-top: 1px solid rgba(99,102,241,0.08); padding-top:24px;">
            <div>
                <h2 style="font-size:15px; font-weight:800; color:var(--text-primary); letter-spacing:-0.02em; display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Attendance & Work Logs
                </h2>
                <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">Daily attendance tracking and logged working hours.</p>
            </div>
            <div>
                <span style="font-size:11px; color:var(--text-muted); display:inline-flex; align-items:center; gap:5px; background:rgba(99,102,241,0.05); padding:4px 10px; border-radius:8px; border:1px solid rgba(99,102,241,0.1);">
                    <span style="width:5px; height:5px; background:#818cf8; border-radius:50%; display:inline-block; animation:pulse 2s infinite;"></span>
                    API Connected
                </span>
            </div>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="table-container" style="padding: 40px 20px; text-align: center;">
            <div style="display:inline-block; width:24px; height:24px; border:2.5px solid rgba(99,102,241,0.1); border-radius:50%; border-top-color:#6366f1; animation:spin 1s linear infinite; margin-bottom:12px;"></div>
            <p style="font-size:12.5px; color:var(--text-muted); font-weight:500;">Fetching attendance and work logs from API...</p>
        </div>

        {{-- Error State --}}
        <div x-show="!loading && error" class="alert-error" style="justify-content: space-between; align-items: center;">
            <div style="display:flex; align-items:center; gap:10px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span x-text="error"></span>
            </div>
            <button @click="init()" class="btn-ghost" style="padding: 4px 8px; font-size: 11px; color:#fca5a5; border-color:rgba(239,68,68,0.2); cursor:pointer;">Retry</button>
        </div>

        {{-- Content State --}}
        <div x-show="!loading && !error" style="display:flex; flex-direction:column; gap:20px;">
            
            {{-- Empty State --}}
            <template x-if="records.length === 0">
                <div class="table-container" style="padding:40px 22px; text-align:center;">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p style="font-size:13px; font-weight:600; color:var(--text-muted);">No attendance or working hour records found.</p>
                    <p style="font-size:12px; color:#334155; margin-top:4px;">Add records using the Developer Sandbox.</p>
                </div>
            </template>

            <template x-if="records.length > 0">
                <div style="display:flex; flex-direction:column; gap:20px;">
                    
                    {{-- Grid for statistics and charts --}}
                    <div class="attendance-grid">
                        
                        {{-- Stats --}}
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                            
                            {{-- Attendance Rate Card --}}
                            <div class="table-container" style="padding: 16px 20px; display:flex; flex-direction:column; justify-content:space-between; gap:12px; position:relative; overflow:hidden;">
                                <div>
                                    <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em;">Attendance Rate</span>
                                    <div style="display:flex; align-items:baseline; gap:8px; margin-top:8px;">
                                        <span style="font-size:26px; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em;" x-text="summary.attendance_rate + '%'">0%</span>
                                        <span style="font-size:11px; color:var(--text-muted);">rate</span>
                                    </div>
                                </div>
                                
                                {{-- Circular Progress Indicator --}}
                                <div style="display:flex; align-items:center; gap:12px; border-top:1px solid rgba(0,0,0,0.02); padding-top:10px;">
                                    <div style="position:relative; width:36px; height:36px;">
                                        <svg width="36" height="36" viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                                            <circle cx="18" cy="18" r="15" fill="none" stroke="rgba(0,0,0,0.03)" stroke-width="3" />
                                            <circle cx="18" cy="18" r="15" fill="none" stroke="url(#indigoGrad)" stroke-width="3"
                                                    x-bind:stroke-dasharray="94.2"
                                                    x-bind:stroke-dashoffset="94.2 - (94.2 * summary.attendance_rate / 100)" />
                                            <defs>
                                                <linearGradient id="indigoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" stop-color="#6366f1" />
                                                    <stop offset="100%" stop-color="#8b5cf6" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                        <div style="position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:9.5px; font-weight:700; color:#a5b4fc;" x-text="summary.present_count + 'd'">0d</div>
                                    </div>
                                    <div style="font-size:11.5px; color:var(--text-muted); line-height:1.3;">
                                        Present: <strong style="color:var(--text-primary);" x-text="summary.present_count">0</strong><br>
                                        Late: <strong style="color:#fbbf24;" x-text="summary.late_count">0</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Working Hours Card --}}
                            <div class="table-container" style="padding: 16px 20px; display:flex; flex-direction:column; justify-content:space-between; gap:12px;">
                                <div>
                                    <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em;">Total Work Hours</span>
                                    <div style="display:flex; align-items:baseline; gap:8px; margin-top:8px;">
                                        <span style="font-size:26px; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em;" x-text="summary.total_hours.toFixed(1) + 'h'">0h</span>
                                        <span style="font-size:11px; color:var(--text-muted);" x-text="'Avg: ' + summary.avg_hours + 'h/d'">Avg: 0h/d</span>
                                    </div>
                                </div>

                                <div style="border-top:1px solid rgba(0,0,0,0.02); padding-top:10px;">
                                    <div style="display:flex; justify-content:space-between; font-size:11.5px; color:var(--text-muted); margin-bottom:5px;">
                                        <span>Sprint Progress</span>
                                        <span style="font-weight:600; color:var(--text-primary);" x-text="Math.min(100, Math.round((summary.total_hours / 80) * 100)) + '%'">0%</span>
                                    </div>
                                    <div class="progress-track" style="height:5px;">
                                        <div class="progress-bar" style="background:linear-gradient(90deg, #6366f1, #8b5cf6);" x-bind:style="'width: ' + Math.min(100, (summary.total_hours / 80) * 100) + '%'"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed count breakdown --}}
                            <div class="table-container" style="padding: 12px 18px; display:flex; align-items:center; justify-content:space-between; grid-column: span 2; background:rgba(255,255,255,0.01);">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:7px; height:7px; background:#34d399; border-radius:50%;"></span>
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Present</span>
                                    <span style="font-size:13px; font-weight:700; color:var(--text-primary); margin-left:2px;" x-text="summary.present_count">0</span>
                                </div>
                                <div style="width:1px; height:14px; background:rgba(0,0,0,0.05);"></div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:7px; height:7px; background:#fbbf24; border-radius:50%;"></span>
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Late</span>
                                    <span style="font-size:13px; font-weight:700; color:var(--text-primary); margin-left:2px;" x-text="summary.late_count">0</span>
                                </div>
                                <div style="width:1px; height:14px; background:rgba(0,0,0,0.05);"></div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:7px; height:7px; background:#ef4444; border-radius:50%;"></span>
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Absent</span>
                                    <span style="font-size:13px; font-weight:700; color:var(--text-primary); margin-left:2px;" x-text="summary.absent_count">0</span>
                                </div>
                                <div style="width:1px; height:14px; background:rgba(0,0,0,0.05);"></div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:7px; height:7px; background:#3b82f6; border-radius:50%;"></span>
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Leave</span>
                                    <span style="font-size:13px; font-weight:700; color:var(--text-primary); margin-left:2px;" x-text="summary.leave_count">0</span>
                                </div>
                            </div>

                        </div>

                        {{-- Chart Card --}}
                        <div class="table-container" style="padding: 16px 20px; display:flex; flex-direction:column; gap:12px;">
                            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em;">Hours Logged (Last 14 Records)</span>
                            <div style="position:relative; flex:1; min-height:140px;">
                                <canvas id="employeeHoursChart" style="width:100%; height:100%;"></canvas>
                            </div>
                        </div>

                    </div>

                    {{-- Table --}}
                    <div class="table-container">
                        <div style="padding:14px 20px; border-bottom:1px solid rgba(0,0,0,0.03); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:12.5px; font-weight:700; color:var(--text-primary);">Daily Attendance Logs</span>
                            <span style="font-size:11.5px; color:var(--text-muted);" x-text="'Showing last ' + records.length + ' days'">Showing last 30 days</span>
                        </div>
                        
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="padding: 10px 20px;">Date</th>
                                        <th style="padding: 10px 20px;">Status</th>
                                        <th style="padding: 10px 20px; text-align: right;">Hours Logged</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="r in records" :key="r.date">
                                        <tr>
                                            <td style="padding: 10px 20px; font-weight:500;" x-text="new Date(r.date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })"></td>
                                            <td style="padding: 10px 20px;">
                                                <template x-if="r.status === 'Present'">
                                                    <span class="badge badge-completed"><span class="badge-dot"></span>Present</span>
                                                </template>
                                                <template x-if="r.status === 'Late'">
                                                    <span class="badge badge-in-progress"><span class="badge-dot"></span>Late</span>
                                                </template>
                                                <template x-if="r.status === 'Absent'">
                                                    <span class="badge badge-danger" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; font-size: 11.5px; font-weight: 600; background:rgba(239,68,68,0.1); color:#fca5a5; border:1px solid rgba(239,68,68,0.2);"><span class="badge-dot" style="background:#ef4444;"></span>Absent</span>
                                                </template>
                                                <template x-if="r.status === 'Leave'">
                                                    <span class="badge badge-pending" style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; font-size: 11.5px; font-weight: 600; background:rgba(59,130,246,0.1); color:#93c5fd; border:1px solid rgba(59,130,246,0.2);"><span class="badge-dot" style="background:#3b82f6;"></span>Leave</span>
                                                </template>
                                                <template x-if="r.status === 'N/A'">
                                                    <span class="badge badge-pending"><span class="badge-dot"></span>N/A</span>
                                                </template>
                                            </td>
                                            <td style="padding: 10px 20px; text-align: right; font-family: monospace; font-weight: 600; color:var(--text-primary);" x-text="r.hours.toFixed(1) + 'h'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </template>
            
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('employeeAttendance', (employeeId) => ({
        loading: true,
        error: null,
        records: [],
        summary: {
            total_hours: 0,
            present_count: 0,
            absent_count: 0,
            late_count: 0,
            leave_count: 0,
            total_days: 0,
            avg_hours: 0,
            attendance_rate: 0
        },
        chartInstance: null,

        async init() {
            try {
                this.loading = true;
                this.error = null;
                const response = await axios.get(`/api/employees/${employeeId}/attendance-hours`);
                const data = response.data;
                this.records = data.records;
                this.summary = data.summary;
                
                if (this.summary.total_days > 0) {
                    this.summary.avg_hours = (this.summary.total_hours / this.summary.total_days).toFixed(1);
                    this.summary.attendance_rate = Math.round(
                        ((this.summary.present_count + this.summary.late_count) / this.summary.total_days) * 100
                    );
                } else {
                    this.summary.avg_hours = 0;
                    this.summary.attendance_rate = 0;
                }

                this.$nextTick(() => {
                    this.initChart();
                });
            } catch (err) {
                console.error(err);
                this.error = 'Failed to load attendance and working hours data.';
            } finally {
                this.loading = false;
            }
        },

        initChart() {
            const ctx = document.getElementById('employeeHoursChart');
            if (!ctx || this.records.length === 0) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            // Reverse to show chronological order, and take last 14 records
            const chartRecords = [...this.records].reverse().slice(-14);

            const labels = chartRecords.map(r => {
                const date = new Date(r.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const data = chartRecords.map(r => r.hours);
            const statuses = chartRecords.map(r => r.status);

            const chartCtx = ctx.getContext('2d');
            const gradient = chartCtx.createLinearGradient(0, 0, 0, 140);
            gradient.addColorStop(0, '#6366f1');
            gradient.addColorStop(1, '#8b5cf6');

            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hours Logged',
                        data: data,
                        backgroundColor: chartRecords.map(r => {
                            if (r.status === 'Absent') return 'rgba(239, 68, 68, 0.2)';
                            if (r.status === 'Late') return 'rgba(245, 158, 11, 0.6)';
                            if (r.status === 'Leave') return 'rgba(59, 130, 246, 0.4)';
                            return gradient;
                        }),
                        borderColor: chartRecords.map(r => {
                            if (r.status === 'Absent') return '#ef4444';
                            if (r.status === 'Late') return '#f59e0b';
                            if (r.status === 'Leave') return '#3b82f6';
                            return '#6366f1';
                        }),
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const status = statuses[index];
                                    return `Hours: ${context.raw}h (${status})`;
                                }
                            },
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#475569',
                            borderColor: 'rgba(99, 102, 241, 0.2)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: 'rgba(0, 0, 0, 0.05)' },
                            ticks: { color: '#475569', font: { size: 9 } },
                            min: 0,
                            max: 12
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#475569', font: { size: 9 } }
                        }
                    }
                }
            });
        }
    }));
});
</script>
@endsection
