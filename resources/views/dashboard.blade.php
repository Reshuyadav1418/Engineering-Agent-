@extends('layouts.app')

@section('content')

{{--
    ══════════════════════════════════════════════════════════════════════
    ENGINEERING AGENT — MODERN SAAS DASHBOARD
    ──────────────────────────────────────────────────────────────────────
    Technology Stack:
    • Tailwind CSS   — utility-first styling via compiled app.css
    • Alpine.js      — reactive dark-mode toggle & micro-interactions
    • Chart.js       — Productivity Trend, Leadership Distribution,
                       Task Completion Trend charts

    Data fed by DashboardController:
    • $employeesCount, $tasksCount, $completedTasks, $inProgressTasks, $pendingTasks
    • $avgProductivity, $topPerformer, $topPerformerScore
    • $productivityTrendLabels, $productivityTrendData
    • $leadershipBuckets
    • $taskCompletionLabels, $taskCompletionData
    • $topLeaderboard
    • $recentReports
    ══════════════════════════════════════════════════════════════════════
--}}

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- ─────────────────────────────────────────────────────────────────────
     DASHBOARD ROOT  (Alpine dark-mode scope)
     ───────────────────────────────────────────────────────────────────── --}}
<div
    x-data="{
        darkMode: localStorage.getItem('ea_theme') !== 'light',
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('ea_theme', this.darkMode ? 'dark' : 'light');
        }
    }"
    :class="darkMode ? 'ea-dark' : 'ea-light'"
    class="ea-dashboard"
    id="dashboard-root"
>

    {{-- ── INLINE STYLES ──────────────────────────────────────────────── --}}
    <style>
        /* ── Design tokens ───────────────────────────────────────── */
        .ea-dark  { --bg: #07080f; --surface: #0e1018; --surface2: #13161f; --border: rgba(99,102,241,.12); --text: #e2e8f0; --muted: #64748b; --accent: #6366f1; --accent2: #8b5cf6; --green: #22c55e; --amber: #f59e0b; --red: #ef4444; }
        .ea-light { --bg: #f1f5f9; --surface: #ffffff; --surface2: #f8fafc; --border: rgba(99,102,241,.15); --text: #0f172a; --muted: #64748b; --accent: #4f46e5; --accent2: #7c3aed; --green: #16a34a; --amber: #d97706; --red: #dc2626; }

        .ea-dashboard { min-height:100vh; background:var(--bg); color:var(--text); transition:background .25s, color .25s; font-family:'Inter',sans-serif; padding:0; }

        /* ── Section Title ────────────────────────────────────────── */
        .ea-section-title { font-size:11px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:16px; }

        /* ── Cards ────────────────────────────────────────────────── */
        .ea-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; transition:all .2s; }
        .ea-card:hover { border-color:rgba(99,102,241,.3); box-shadow:0 8px 32px rgba(99,102,241,.08); transform:translateY(-1px); }
        .ea-card-p { padding:22px 24px; }

        /* ── Stat Cards ───────────────────────────────────────────── */
        .ea-stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        @media(max-width:1100px){ .ea-stat-grid { grid-template-columns:repeat(2,1fr); } }
        @media(max-width:640px) { .ea-stat-grid { grid-template-columns:1fr; } }

        .ea-stat-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
        .ea-stat-label { font-size:11.5px; font-weight:600; color:var(--muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:.06em; }
        .ea-stat-value { font-size:28px; font-weight:800; letter-spacing:-.03em; line-height:1; }
        .ea-stat-sub   { font-size:12px; color:var(--muted); margin-top:8px; }

        /* ── Charts Grid ──────────────────────────────────────────── */
        .ea-charts-grid { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px; }
        @media(max-width:900px){ .ea-charts-grid { grid-template-columns:1fr; } }

        .ea-task-trend-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
        @media(max-width:900px){ .ea-task-trend-grid { grid-template-columns:1fr; } }

        /* ── Bottom Grid ──────────────────────────────────────────── */
        .ea-bottom-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        @media(max-width:900px){ .ea-bottom-grid { grid-template-columns:1fr; } }

        /* ── Leaderboard Table ────────────────────────────────────── */
        .ea-lb-row { display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid var(--border); }
        .ea-lb-row:last-child { border-bottom:none; }
        .ea-lb-rank { width:26px; height:26px; border-radius:8px; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .ea-lb-avatar { width:34px; height:34px; border-radius:10px; object-fit:cover; flex-shrink:0; }
        .ea-lb-name { font-size:13.5px; font-weight:700; color:var(--text); }
        .ea-lb-role { font-size:11px; color:var(--muted); }
        .ea-lb-score { margin-left:auto; font-size:15px; font-weight:800; }
        .ea-lb-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; margin-left:8px; }

        /* ── Recent Reports ───────────────────────────────────────── */
        .ea-report-card { padding:14px 16px; border-radius:12px; background:var(--surface2); border:1px solid var(--border); transition:all .15s; }
        .ea-report-card:hover { border-color:rgba(99,102,241,.3); background:var(--surface); }

        /* ── Progress Bar ─────────────────────────────────────────── */
        .ea-progress-track { height:6px; background:rgba(99,102,241,.08); border-radius:99px; overflow:hidden; }
        .ea-progress-bar   { height:100%; border-radius:99px; transition:width .6s ease; }

        /* ── Dark Mode Toggle ─────────────────────────────────────── */
        .ea-toggle { width:44px; height:24px; border-radius:99px; background:rgba(99,102,241,.2); border:1px solid rgba(99,102,241,.3); cursor:pointer; position:relative; transition:background .2s; display:flex; align-items:center; padding:2px; }
        .ea-toggle-thumb { width:18px; height:18px; border-radius:50%; background:var(--accent); transition:transform .2s; box-shadow:0 2px 6px rgba(99,102,241,.5); }
        .ea-dark .ea-toggle-thumb  { transform:translateX(20px); }
        .ea-light .ea-toggle-thumb { transform:translateX(0); }

        /* ── Badges ───────────────────────────────────────────────── */
        .badge-completed { background:rgba(34,197,94,.12); color:#22c55e; border:1px solid rgba(34,197,94,.25); }
        .badge-progress  { background:rgba(245,158,11,.12); color:#f59e0b; border:1px solid rgba(245,158,11,.25); }
        .badge-pending   { background:rgba(100,116,139,.1);  color:var(--text-secondary); border:1px solid rgba(100,116,139,.2); }

        /* ── Rank colours ─────────────────────────────────────────── */
        .rank-1 { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; }
        .rank-2 { background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; }
        .rank-3 { background:linear-gradient(135deg,#c2855e,#a16136); color:#fff; }
        .rank-n { background:rgba(99,102,241,.1); color:var(--muted); }

        /* ── Page Wrapper ─────────────────────────────────────────── */
        .ea-page { max-width:1280px; margin:0 auto; padding:24px 28px; }
        @media(max-width:640px){ .ea-page { padding:16px; } }

        /* ── Chart canvas wrapper ─────────────────────────────────── */
        .ea-chart-wrap { position:relative; height:220px; }
    </style>

    {{-- ──────────────────────────────────────────────────────────────── --}}
    <div class="ea-page">

        {{-- ── PAGE HEADER ───────────────────────────────────────────── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="font-size:22px; font-weight:800; letter-spacing:-.03em;">
                    Welcome
                </h1>
                <p style="font-size:13px; color:var(--muted); margin-top:4px;">
                    Here's your engineering team performance at a glance.
                </p>
            </div>

            {{-- Dark Mode Toggle --}}
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:12px; font-weight:600; color:var(--muted);" x-text="darkMode ? '🌙 Dark' : '☀️ Light'"></span>
                <button
                    @click="toggleDark()"
                    class="ea-toggle"
                    id="dark-mode-toggle"
                    :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                    title="Toggle dark mode"
                >
                    <div class="ea-toggle-thumb"></div>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SECTION 1 — STAT CARDS
             ══════════════════════════════════════════════════════════ --}}

        <p class="ea-section-title">Overview</p>
        <div class="ea-stat-grid">

            {{-- ① Total Employees --}}
            <div class="ea-card ea-card-p" id="stat-employees">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                    <div class="ea-stat-icon" style="background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.2);">
                        <svg width="20" height="20" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                    <span style="font-size:10px; font-weight:700; color:#818cf8; background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.2); padding:3px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:.06em;">Team</span>
                </div>
                <p class="ea-stat-label">Total Employees</p>
                <p class="ea-stat-value" style="color:#818cf8;">{{ $employeesCount }}</p>
                <p class="ea-stat-sub"><span style="color:#818cf8; font-weight:600;">Active</span> team members registered</p>
            </div>

            {{-- ② Total Tasks --}}
            <div class="ea-card ea-card-p" id="stat-tasks">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                    <div class="ea-stat-icon" style="background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.2);">
                        <svg width="20" height="20" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <span style="font-size:10px; font-weight:700; color:#fbbf24; background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.2); padding:3px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:.06em;">Sprint</span>
                </div>
                <p class="ea-stat-label">Total Tasks</p>
                <p class="ea-stat-value" style="color:#fbbf24;">{{ $tasksCount }}</p>
                <p class="ea-stat-sub"><span style="color:#fbbf24; font-weight:600;">{{ $inProgressTasks }}</span> currently in progress</p>
            </div>

            {{-- ③ Average Productivity --}}
            <div class="ea-card ea-card-p" id="stat-productivity">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                    <div class="ea-stat-icon" style="background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.2);">
                        <svg width="20" height="20" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </div>
                    <span style="font-size:10px; font-weight:700; color:#4ade80; background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.2); padding:3px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:.06em;">Score</span>
                </div>
                <p class="ea-stat-label">Avg Productivity</p>
                <p class="ea-stat-value" style="color:#4ade80;">{{ $avgProductivity }}<span style="font-size:16px; font-weight:600;">/10</span></p>
                <p class="ea-stat-sub">Across all <span style="color:#4ade80; font-weight:600;">{{ $employeesCount }}</span> team members</p>
            </div>

            {{-- ④ Top Performer --}}
            <div class="ea-card ea-card-p" id="stat-top-performer" style="background:linear-gradient(135deg, rgba(99,102,241,.08) 0%, rgba(139,92,246,.05) 100%);">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                    <div class="ea-stat-icon" style="background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(139,92,246,.2)); border:1px solid rgba(99,102,241,.3);">
                        <svg width="20" height="20" fill="none" stroke="#a78bfa" stroke-width="2" viewBox="0 0 24 24">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <span style="font-size:10px; font-weight:700; color:#a78bfa; background:rgba(139,92,246,.12); border:1px solid rgba(139,92,246,.25); padding:3px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:.06em;">MVP</span>
                </div>
                <p class="ea-stat-label">Top Performer</p>
                @if($topPerformer)
                    <p class="ea-stat-value" style="color:#a78bfa; font-size:20px; font-weight:800;">{{ $topPerformer->name }}</p>
                    <p class="ea-stat-sub">Score: <span style="color:#a78bfa; font-weight:700;">{{ number_format($topPerformerScore->productivity_score, 1) }}/10</span> · {{ $topPerformer->department ?? 'Engineering' }}</p>
                @else
                    <p class="ea-stat-value" style="color:var(--muted); font-size:18px;">—</p>
                    <p class="ea-stat-sub">Run scoring to populate</p>
                @endif
            </div>

        </div>{{-- /stat grid --}}

        {{-- ══════════════════════════════════════════════════════════
             SECTION 1b — ATTENDANCE OVERVIEW WIDGET
             ══════════════════════════════════════════════════════════ --}}

        {{-- Inline styles for the attendance widget --}}
        <style>
            /* ── Attendance Ring ──────────────────────────────── */
            .att-ring-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .att-ring-wrap svg { transform: rotate(-90deg); }
            .att-ring-center {
                position: absolute;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
            /* ── Attendance bar ───────────────────────────────── */
            .att-bar-track {
                height: 7px;
                border-radius: 99px;
                background: rgba(99,102,241,.07);
                overflow: hidden;
                flex: 1;
            }
            .att-bar-fill {
                height: 100%;
                border-radius: 99px;
                transition: width .9s cubic-bezier(.4,0,.2,1);
            }
            /* ── Attendance card grid ─────────────────────────── */
            .att-grid {
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 24px;
                align-items: center;
            }
            @media(max-width:700px){ .att-grid { grid-template-columns: 1fr; } }
        </style>

        @php
            $totalAtt      = $totalAttendanceToday;
            $noData        = $totalAtt === 0;
            /* SVG ring math — r=48, circumference=2π×48≈301.6 */
            $circ          = 301.59;
            $presentDash   = $noData ? 0 : round(($presentPct / 100) * $circ, 2);
            $lateDash      = $noData ? 0 : round(($latePct    / 100) * $circ, 2);
            $absentDash    = $noData ? 0 : round(($absentPct  / 100) * $circ, 2);
        @endphp

        <p class="ea-section-title" style="margin-top:28px;">Attendance</p>
        <div class="ea-card ea-card-p" id="attendance-overview-widget" style="margin-bottom:24px;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:10px;">
                <div>
                    <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">📋 Today's Attendance</h2>
                    <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">
                        {{ now()->format('l, d M Y') }}
                        @if($noData)
                            &nbsp;·&nbsp;<span style="color:var(--amber); font-weight:600;">No records logged yet</span>
                        @else
                            &nbsp;·&nbsp;<span style="font-weight:600; color:var(--text);">{{ $totalAtt }}</span> records
                        @endif
                    </p>
                </div>
                {{-- Live indicator dot --}}
                <div style="display:flex; align-items:center; gap:7px; font-size:11px; font-weight:600; color:var(--green); background:rgba(34,197,94,.08); border:1px solid rgba(34,197,94,.2); border-radius:8px; padding:5px 12px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:var(--green); animation:pulse 2s infinite; display:inline-block;"></span>
                    Live
                </div>
            </div>

            <div class="att-grid">

                {{-- ── Stacked Donut Ring ────────────────────────────────── --}}
                <div style="display:flex; justify-content:center;">
                    <div class="att-ring-wrap" style="width:136px; height:136px;">
                        <svg width="136" height="136" viewBox="0 0 136 136">
                            {{-- Track --}}
                            <circle cx="68" cy="68" r="48"
                                fill="none" stroke="rgba(99,102,241,.08)" stroke-width="14"/>
                            {{-- Present (green) --}}
                            <circle cx="68" cy="68" r="48"
                                fill="none" stroke="#22c55e" stroke-width="14"
                                stroke-dasharray="{{ $presentDash }} {{ $circ }}"
                                stroke-dashoffset="0"
                                stroke-linecap="round"/>
                            {{-- Late (amber) — offset by present arc --}}
                            <circle cx="68" cy="68" r="48"
                                fill="none" stroke="#f59e0b" stroke-width="14"
                                stroke-dasharray="{{ $lateDash }} {{ $circ }}"
                                stroke-dashoffset="{{ -$presentDash }}"
                                stroke-linecap="round"/>
                            {{-- Absent (red) — offset by present + late arcs --}}
                            <circle cx="68" cy="68" r="48"
                                fill="none" stroke="#ef4444" stroke-width="14"
                                stroke-dasharray="{{ $absentDash }} {{ $circ }}"
                                stroke-dashoffset="{{ -($presentDash + $lateDash) }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="att-ring-center">
                            @if($noData)
                                <span style="font-size:11px; font-weight:600; color:var(--muted);">No data</span>
                            @else
                                <span style="font-size:26px; font-weight:800; letter-spacing:-.04em; color:var(--text); line-height:1;">{{ $presentPct }}<span style="font-size:13px; font-weight:600;">%</span></span>
                                <span style="font-size:10px; font-weight:600; color:var(--muted); margin-top:2px;">Present</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Breakdown Bars ────────────────────────────────────── --}}
                <div style="display:flex; flex-direction:column; gap:18px;">

                    {{-- Present --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:7px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="width:9px; height:9px; border-radius:50%; background:#22c55e; display:inline-block; box-shadow:0 0 6px rgba(34,197,94,.5);"></span>
                                <span style="font-size:12.5px; font-weight:600; color:var(--text);">Present</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:20px; font-weight:800; color:#22c55e; letter-spacing:-.03em;">{{ $presentToday }}</span>
                                <span style="font-size:11px; font-weight:700; color:#22c55e; background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.2); padding:2px 8px; border-radius:6px;">{{ $presentPct }}%</span>
                            </div>
                        </div>
                        <div class="att-bar-track">
                            <div class="att-bar-fill" style="width:{{ $presentPct }}%; background:linear-gradient(90deg,#16a34a,#22c55e);"></div>
                        </div>
                    </div>

                    {{-- Late --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:7px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="width:9px; height:9px; border-radius:50%; background:#f59e0b; display:inline-block; box-shadow:0 0 6px rgba(245,158,11,.5);"></span>
                                <span style="font-size:12.5px; font-weight:600; color:var(--text);">Late</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:20px; font-weight:800; color:#f59e0b; letter-spacing:-.03em;">{{ $lateToday }}</span>
                                <span style="font-size:11px; font-weight:700; color:#f59e0b; background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.2); padding:2px 8px; border-radius:6px;">{{ $latePct }}%</span>
                            </div>
                        </div>
                        <div class="att-bar-track">
                            <div class="att-bar-fill" style="width:{{ $latePct }}%; background:linear-gradient(90deg,#d97706,#f59e0b);"></div>
                        </div>
                    </div>

                    {{-- Absent --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:7px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="width:9px; height:9px; border-radius:50%; background:#ef4444; display:inline-block; box-shadow:0 0 6px rgba(239,68,68,.5);"></span>
                                <span style="font-size:12.5px; font-weight:600; color:var(--text);">Absent</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:20px; font-weight:800; color:#ef4444; letter-spacing:-.03em;">{{ $absentToday }}</span>
                                <span style="font-size:11px; font-weight:700; color:#ef4444; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2); padding:2px 8px; border-radius:6px;">{{ $absentPct }}%</span>
                            </div>
                        </div>
                        <div class="att-bar-track">
                            <div class="att-bar-fill" style="width:{{ $absentPct }}%; background:linear-gradient(90deg,#dc2626,#ef4444);"></div>
                        </div>
                    </div>

                </div>{{-- /bars --}}

            </div>{{-- /att-grid --}}

        </div>{{-- /attendance-overview-widget --}}

        {{-- ══════════════════════════════════════════════════════════
             SECTION 2 — CHARTS ROW 1: Productivity Trend + Leadership Distribution
             ══════════════════════════════════════════════════════════ --}}

        <p class="ea-section-title" style="margin-top:32px;">Analytics</p>
        <div class="ea-charts-grid">

            {{-- Productivity Trend (Line Chart) --}}
            <div class="ea-card ea-card-p" id="chart-productivity-trend">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">Productivity Trend</h2>
                        <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Average team score over time</p>
                    </div>
                    <div style="font-size:10px; font-weight:700; padding:3px 10px; border-radius:6px; background:rgba(99,102,241,.1); color:#818cf8; border:1px solid rgba(99,102,241,.2);">6 Months</div>
                </div>
                <div class="ea-chart-wrap">
                    <canvas id="productivityTrendChart"></canvas>
                </div>
            </div>

            {{-- Leadership Distribution (Doughnut) --}}
            <div class="ea-card ea-card-p" id="chart-leadership-dist">
                <div style="margin-bottom:20px;">
                    <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">Leadership Distribution</h2>
                    <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Score bracket breakdown</p>
                </div>
                <div class="ea-chart-wrap" style="height:185px;">
                    <canvas id="leadershipDistChart"></canvas>
                </div>
                {{-- Legend --}}
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px;">
                    <span style="display:flex; align-items:center; gap:5px; font-size:10.5px; color:var(--muted);"><span style="width:8px; height:8px; border-radius:2px; background:#6366f1; display:inline-block;"></span>Elite</span>
                    <span style="display:flex; align-items:center; gap:5px; font-size:10.5px; color:var(--muted);"><span style="width:8px; height:8px; border-radius:2px; background:#22c55e; display:inline-block;"></span>Strong</span>
                    <span style="display:flex; align-items:center; gap:5px; font-size:10.5px; color:var(--muted);"><span style="width:8px; height:8px; border-radius:2px; background:#f59e0b; display:inline-block;"></span>Growing</span>
                    <span style="display:flex; align-items:center; gap:5px; font-size:10.5px; color:var(--muted);"><span style="width:8px; height:8px; border-radius:2px; background:#475569; display:inline-block;"></span>Emerging</span>
                </div>
            </div>

        </div>{{-- /charts row 1 --}}

        {{-- ══════════════════════════════════════════════════════════
             SECTION 3 — CHARTS ROW 2: Task Completion Trend + Sprint Progress
             ══════════════════════════════════════════════════════════ --}}

        <div class="ea-task-trend-grid">

            {{-- Task Completion Trend (Bar Chart) --}}
            <div class="ea-card ea-card-p" id="chart-task-completion">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <div>
                        <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">Task Completion Trend</h2>
                        <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Completed tasks by month</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" style="font-size:11px; font-weight:600; color:var(--accent); text-decoration:none; display:flex; align-items:center; gap:4px;">
                        All Tasks
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="ea-chart-wrap">
                    <canvas id="taskCompletionChart"></canvas>
                </div>
            </div>

            {{-- Sprint Progress --}}
            <div class="ea-card ea-card-p" id="sprint-progress">
                <div style="margin-bottom:20px;">
                    <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">Sprint Progress</h2>
                    <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Current task breakdown</p>
                </div>
                <div style="display:flex; flex-direction:column; gap:20px;">
                    {{-- Completed --}}
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <div style="display:flex; align-items:center; gap:7px;">
                                <div style="width:8px; height:8px; border-radius:50%; background:#22c55e;"></div>
                                <span style="font-size:12.5px; color:var(--muted); font-weight:500;">Completed</span>
                            </div>
                            <span style="font-size:13px; font-weight:700; color:var(--text);">{{ $completedTasks }}</span>
                        </div>
                        <div class="ea-progress-track">
                            <div class="ea-progress-bar" style="width:{{ $tasksCount > 0 ? ($completedTasks / $tasksCount) * 100 : 0 }}%; background:linear-gradient(90deg,#16a34a,#22c55e);"></div>
                        </div>
                    </div>
                    {{-- In Progress --}}
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <div style="display:flex; align-items:center; gap:7px;">
                                <div style="width:8px; height:8px; border-radius:50%; background:#f59e0b;"></div>
                                <span style="font-size:12.5px; color:var(--muted); font-weight:500;">In Progress</span>
                            </div>
                            <span style="font-size:13px; font-weight:700; color:var(--text);">{{ $inProgressTasks }}</span>
                        </div>
                        <div class="ea-progress-track">
                            <div class="ea-progress-bar" style="width:{{ $tasksCount > 0 ? ($inProgressTasks / $tasksCount) * 100 : 0 }}%; background:linear-gradient(90deg,#d97706,#f59e0b);"></div>
                        </div>
                    </div>
                    {{-- Pending --}}
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <div style="display:flex; align-items:center; gap:7px;">
                                <div style="width:8px; height:8px; border-radius:50%; background:#475569;"></div>
                                <span style="font-size:12.5px; color:var(--muted); font-weight:500;">Pending</span>
                            </div>
                            <span style="font-size:13px; font-weight:700; color:var(--text);">{{ $pendingTasks }}</span>
                        </div>
                        <div class="ea-progress-track">
                            <div class="ea-progress-bar" style="width:{{ $tasksCount > 0 ? ($pendingTasks / $tasksCount) * 100 : 0 }}%; background:linear-gradient(90deg,#334155,#475569);"></div>
                        </div>
                    </div>
                </div>
                {{-- Total --}}
                <div style="margin-top:22px; padding-top:16px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:var(--muted); font-weight:500;">Total Tasks</span>
                    <span style="font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.03em;">{{ $tasksCount }}</span>
                </div>
                <a href="{{ route('tasks.create') }}" id="add-task-btn" style="display:flex; align-items:center; justify-content:center; gap:8px; margin-top:16px; padding:11px 18px; border-radius:10px; background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; text-decoration:none; font-size:13px; font-weight:700; box-shadow:0 4px 14px rgba(99,102,241,.35); transition:all .2s;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Add New Task
                </a>
            </div>

        </div>{{-- /task trend grid --}}

        {{-- ══════════════════════════════════════════════════════════
             SECTION 4 — BOTTOM: Leaderboard + Recent Reports
             ══════════════════════════════════════════════════════════ --}}

        <p class="ea-section-title" style="margin-top:8px;">People & Insights</p>
        <div class="ea-bottom-grid">

            {{-- ── LEADERBOARD WIDGET ──────────────────────────────── --}}
            <div class="ea-card" id="leaderboard-widget">
                <div style="padding:20px 22px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">🏆 Leaderboard</h2>
                        <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Top performers by leadership score</p>
                    </div>
                    <a href="{{ route('leaderboard') }}" style="font-size:11px; font-weight:600; color:var(--accent); text-decoration:none; display:flex; align-items:center; gap:4px;">
                        Full Board
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div style="padding:4px 22px 16px;">
                    @forelse($topLeaderboard as $index => $entry)
                        <div class="ea-lb-row">
                            {{-- Rank Badge --}}
                            <div class="ea-lb-rank {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-n')) }}">
                                {{ $index + 1 }}
                            </div>
                            {{-- Avatar --}}
                            <img
                                class="ea-lb-avatar"
                                src="https://ui-avatars.com/api/?name={{ urlencode($entry->employee->name ?? 'Unknown') }}&background={{ $index === 0 ? 'f59e0b' : ($index === 1 ? '94a3b8' : ($index === 2 ? 'c2855e' : '6366f1')) }}&color=fff&size=68"
                                alt="{{ $entry->employee->name ?? 'Unknown' }}"
                            >
                            {{-- Info --}}
                            <div style="flex:1; min-width:0;">
                                <p class="ea-lb-name">{{ $entry->employee->name ?? '—' }}</p>
                                <p class="ea-lb-role">{{ $entry->employee->role ?? $entry->employee->department ?? 'Engineer' }}</p>
                            </div>
                            {{-- Score --}}
                            <div style="text-align:right; flex-shrink:0;">
                                <p class="ea-lb-score" style="color:{{ $index === 0 ? '#f59e0b' : ($index === 1 ? '#94a3b8' : ($index === 2 ? '#c2855e' : 'var(--accent)')) }};">
                                    {{ number_format($entry->leadership_score, 1) }}
                                </p>
                                <p style="font-size:10px; color:var(--muted);">/ 10</p>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:40px 0; color:var(--muted);">
                            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px; opacity:.4;"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                            <p style="font-size:13px; font-weight:600;">No leaderboard data yet.</p>
                            <p style="font-size:11.5px; margin-top:4px;">Run <code style="font-size:11px; background:rgba(99,102,241,.1); padding:2px 6px; border-radius:4px;">php artisan engineering</code></p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ── RECENT REPORTS WIDGET ───────────────────────────── --}}
            <div class="ea-card" id="recent-reports-widget">
                <div style="padding:20px 22px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">⚡ Recent AI Reports</h2>
                        <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Latest generated employee analyses</p>
                    </div>
                    <a href="{{ route('ai.report.index') }}" style="font-size:11px; font-weight:600; color:var(--accent); text-decoration:none; display:flex; align-items:center; gap:4px;">
                        All Reports
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div style="padding:16px 22px; display:flex; flex-direction:column; gap:10px;">
                    @forelse($recentReports as $report)
                        @if($report->employee_id)
                        <a href="{{ route('ai.report.show', ['employee' => $report->employee_id]) }}" class="ea-report-card" style="text-decoration:none; color:inherit; display:block;" id="report-{{ $report->id }}">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <img
                                    style="width:30px; height:30px; border-radius:8px; object-fit:cover;"
                                    src="https://ui-avatars.com/api/?name={{ urlencode($report->employee->name ?? 'U') }}&background=6366f1&color=fff&size=60"
                                    alt="{{ $report->employee->name ?? 'Employee' }}"
                                >
                                <div>
                                    <p style="font-size:12.5px; font-weight:700; color:var(--text);">{{ $report->employee->name ?? 'Unknown Employee' }}</p>
                                    <p style="font-size:10.5px; color:var(--muted);">
                                        {{ $report->created_at ? $report->created_at->diffForHumans() : 'Recently' }}
                                    </p>
                                </div>
                                <div style="margin-left:auto; display:flex; align-items:center; gap:5px; font-size:10px; font-weight:700; color:#818cf8; background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.2); padding:3px 7px; border-radius:6px;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    AI
                                </div>
                            </div>
                            @if($report->summary)
                                <p style="font-size:11.5px; color:var(--muted); line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                    {{ $report->summary }}
                                </p>
                            @endif
                        </a>
                        @endif
                    @empty
                        <div style="text-align:center; padding:40px 0; color:var(--muted);">
                            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px; opacity:.4;"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <p style="font-size:13px; font-weight:600;">No AI reports yet.</p>
                            <p style="font-size:11.5px; margin-top:4px;">Generate a report from the AI Reports section.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>{{-- /bottom grid --}}

        {{-- ══════════════════════════════════════════════════════════
             SECTION 5 — RECENT TASKS (full width)
             ══════════════════════════════════════════════════════════ --}}

        <p class="ea-section-title" style="margin-top:24px;">Recent Activity</p>
        <div class="ea-card" id="recent-tasks-panel" style="margin-bottom:32px;">
            <div style="padding:18px 22px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:14px; font-weight:700; letter-spacing:-.02em;">Recent Tasks</h2>
                    <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">Latest sprint assignments across the team</p>
                </div>
                <a href="{{ route('tasks.index') }}" style="font-size:11px; font-weight:600; color:var(--accent); text-decoration:none; display:flex; align-items:center; gap:4px;">
                    View All
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div style="padding:4px 0;">
                @forelse($recentTasks as $task)
                    <div
                        x-data="{ hovered: false }"
                        @mouseenter="hovered = true"
                        @mouseleave="hovered = false"
                        :style="hovered ? 'background:rgba(99,102,241,.04)' : 'background:transparent'"
                        style="display:flex; align-items:center; justify-content:space-between; padding:13px 22px; border-bottom:1px solid var(--border); transition:background .15s;"
                    >
                        <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
                            <div style="width:8px; height:8px; border-radius:50%; flex-shrink:0;
                                @if($task->status === 'Completed') background:#22c55e; box-shadow:0 0 6px rgba(34,197,94,.4);
                                @elseif($task->status === 'In Progress') background:#f59e0b; box-shadow:0 0 6px rgba(245,158,11,.4);
                                @else background:#475569;
                                @endif">
                            </div>
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:13.5px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->title }}</p>
                                <p style="font-size:11.5px; color:var(--muted); margin-top:2px;">{{ $task->employee->name ?? 'Unassigned' }}</p>
                            </div>
                        </div>
                        <div style="flex-shrink:0; margin-left:12px;">
                            @if($task->status === 'Completed')
                                <span style="font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:99px;" class="badge-completed">● Completed</span>
                            @elseif($task->status === 'In Progress')
                                <span style="font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:99px;" class="badge-progress">● In Progress</span>
                            @else
                                <span style="font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:99px;" class="badge-pending">● Pending</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:48px 22px; text-align:center; color:var(--muted);">
                        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px; opacity:.4;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        <p style="font-size:13px; font-weight:600;">No tasks created yet.</p>
                        <p style="font-size:11.5px; margin-top:4px;">Create your first task to get started.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>{{-- /ea-page --}}

</div>{{-- /ea-dashboard --}}

{{-- ══════════════════════════════════════════════════════════════════════
     CHART.JS INITIALIZATION
     ══════════════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Shared chart defaults ─────────────────────────────────────────
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // Helper: get CSS var from :root / ea-dark scope
    function cssVar(name) {
        return getComputedStyle(document.getElementById('dashboard-root')).getPropertyValue(name).trim();
    }

    // ── 1. Productivity Trend — Line Chart ───────────────────────────
    const trendLabels = @json($productivityTrendLabels);
    const trendData   = @json($productivityTrendData);

    // Fallback demo data if DB has no scores yet
    const finalTrendLabels = trendLabels.length ? trendLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const finalTrendData   = trendData.length   ? trendData   : [0, 0, 0, 0, 0, 0];

    const trendCtx = document.getElementById('productivityTrendChart').getContext('2d');
    const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 220);
    trendGradient.addColorStop(0, 'rgba(99,102,241,0.25)');
    trendGradient.addColorStop(1, 'rgba(99,102,241,0)');

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: finalTrendLabels,
            datasets: [{
                label: 'Avg Productivity',
                data: finalTrendData,
                borderColor: '#6366f1',
                backgroundColor: trendGradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    borderColor: 'rgba(99,102,241,.3)',
                    borderWidth: 1,
                    titleColor: '#0f172a',
                    bodyColor: '#475569',
                    padding: 12,
                    callbacks: {
                        label: ctx => ` Score: ${ctx.parsed.y.toFixed(2)} / 10`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    min: 0,
                    max: 10,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 11 }, stepSize: 2 },
                },
            },
        },
    });

    // ── 2. Leadership Distribution — Doughnut Chart ──────────────────
    const leadershipBuckets = @json($leadershipBuckets);
    const lbLabels  = Object.keys(leadershipBuckets);
    const lbValues  = Object.values(leadershipBuckets);
    const lbColors  = ['#6366f1', '#22c55e', '#f59e0b', '#475569'];
    const lbHovers  = ['#818cf8', '#4ade80', '#fbbf24', '#64748b'];

    const lbCtx = document.getElementById('leadershipDistChart').getContext('2d');
    new Chart(lbCtx, {
        type: 'doughnut',
        data: {
            labels: lbLabels,
            datasets: [{
                data: lbValues,
                backgroundColor: lbColors,
                hoverBackgroundColor: lbHovers,
                borderWidth: 3,
                borderColor: 'transparent',
                hoverBorderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    borderColor: 'rgba(99,102,241,.3)',
                    borderWidth: 1,
                    titleColor: '#0f172a',
                    bodyColor: '#475569',
                    padding: 12,
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} employees`,
                    },
                },
            },
        },
    });

    // ── 3. Task Completion Trend — Bar Chart ─────────────────────────
    const tcLabels = @json($taskCompletionLabels);
    const tcData   = @json($taskCompletionData);

    const finalTcLabels = tcLabels.length ? tcLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    const finalTcData   = tcData.length   ? tcData   : [0, 0, 0, 0, 0, 0];

    const tcCtx = document.getElementById('taskCompletionChart').getContext('2d');
    const barGradient = tcCtx.createLinearGradient(0, 0, 0, 220);
    barGradient.addColorStop(0, 'rgba(34,197,94,0.85)');
    barGradient.addColorStop(1, 'rgba(34,197,94,0.25)');

    new Chart(tcCtx, {
        type: 'bar',
        data: {
            labels: finalTcLabels,
            datasets: [{
                label: 'Completed Tasks',
                data: finalTcData,
                backgroundColor: barGradient,
                hoverBackgroundColor: '#4ade80',
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    borderColor: 'rgba(34,197,94,.3)',
                    borderWidth: 1,
                    titleColor: '#0f172a',
                    bodyColor: '#475569',
                    padding: 12,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} tasks completed`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 11 }, stepSize: 1 },
                    beginAtZero: true,
                },
            },
        },
    });

});
</script>

@endsection
