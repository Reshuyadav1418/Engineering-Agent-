<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SimpelTask') }} — Engineering Dashboard</title>
    <meta name="description" content="SimpelTask Engineering Agent — AI-powered team performance dashboard.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Global CSS custom-property defaults (light mode) */
        :root {
            --bg: #f1f5f9;
            --surface: #ffffff;
            --surface2: #f8fafc;
            --border: rgba(99,102,241,.15);
            --text: #0f172a;
            --muted: #64748b;
            --accent: #6366f1;
            --accent2: #8b5cf6;
        }
    </style>
</head>
<body style="background:var(--bg-base); color:var(--text-primary);">

<div class="flex h-screen overflow-hidden" style="background:var(--bg-base);">

    {{-- ═══════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════ --}}
    <aside class="sidebar w-60 flex flex-col flex-shrink-0" style="width:232px;">

        {{-- Brand --}}
        <div style="height:60px; display:flex; align-items:center; padding:0 20px; border-bottom:1px solid rgba(99,102,241,0.1);">
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
                <div style="width:30px; height:30px; background:linear-gradient(135deg,#6366f1,#8b5cf6); border-radius:8px; display:flex; align-items:center; justify-content:center; box-shadow:0 0 12px rgba(99,102,241,0.4);">
                    <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <span style="font-size:14px; font-weight:800; color:var(--text-primary); letter-spacing:-0.02em;">SimpelTask</span>
                    <div style="font-size:9.5px; color:#4f46e5; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; line-height:1;">Engineering</div>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav style="flex:1; padding:16px 12px; display:flex; flex-direction:column; gap:2px; overflow-y:auto;">

            {{-- Section Label --}}
            <div style="font-size:10px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:0.1em; padding:8px 14px 6px; margin-bottom:4px;">Main</div>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Employees
            </a>

            <a href="{{ route('teams.index') }}" class="sidebar-link {{ request()->routeIs('teams.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Teams
            </a>

            <a href="{{ route('tasks.index') }}" class="sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/>
                </svg>
                Tasks
            </a>

            <a href="{{ route('leaderboard') }}" class="sidebar-link {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M18 20V10M12 20V4M6 20v-6"/>
                </svg>
                Leaderboard
            </a>

            {{-- Divider --}}
            <div style="height:1px; background:rgba(99,102,241,0.08); margin:10px 14px;"></div>
            <div style="font-size:10px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:0.1em; padding:4px 14px 6px;">AI Intelligence</div>

            <a href="{{ route('ai.report.index') }}" class="sidebar-link {{ request()->routeIs('ai.report.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                AI Reports
                <span style="margin-left:auto; font-size:9.5px; padding:2px 6px; background:rgba(99,102,241,0.15); color:#818cf8; border-radius:99px; font-weight:700; border:1px solid rgba(99,102,241,0.2);">AI</span>
            </a>

            <a href="{{ route('vcs.index') }}" class="sidebar-link {{ request()->routeIs('vcs.*') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                    <path d="M6 9v6M18 9a9 9 0 00-9 9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                VCS Integration
                <span style="margin-left:auto; font-size:9.5px; padding:2px 6px; background:rgba(16,185,129,0.15); color:#10b981; border-radius:99px; font-weight:700; border:1px solid rgba(16,185,129,0.2);">VCS</span>
            </a>

            {{-- Divider --}}
            <div style="height:1px; background:rgba(99,102,241,0.08); margin:10px 14px;"></div>
            <div style="font-size:10px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:0.1em; padding:4px 14px 6px;">Development</div>

            <a href="{{ route('developer.tools') }}" class="sidebar-link {{ request()->routeIs('developer.tools') ? 'active' : '' }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M16 18l6-6-6-6M8 6l-6 6 6 6M12 4l-4 16" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Developer Tools
                <span style="margin-left:auto; font-size:9.5px; padding:2px 6px; background:rgba(245,158,11,0.15); color:#f59e0b; border-radius:99px; font-weight:700; border:1px solid rgba(245,158,11,0.2);">Dev</span>
            </a>
        </nav>

        {{-- Bottom Profile --}}
        <div style="padding:14px 16px; border-top:1px solid rgba(99,102,241,0.1);">
            <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; background:rgba(0,0,0,0.03); border:1px solid rgba(99,102,241,0.1);">
                <img
                    style="width:32px; height:32px; border-radius:8px; object-fit:cover; border:1px solid rgba(99,102,241,0.3);"
                    src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff&size=64"
                    alt="Admin">
                <div style="flex:1; min-width:0;">
                    <p style="font-size:12.5px; font-weight:700; color:var(--text-primary); line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Admin User</p>
                    <p style="font-size:10.5px; color:#4f46e5; font-weight:600; line-height:1.2;">Administrator</p>
                </div>
                <div style="width:7px; height:7px; background:#22c55e; border-radius:50%; box-shadow:0 0 6px rgba(34,197,94,0.5); flex-shrink:0;"></div>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════ --}}
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0;">

        {{-- Top Header --}}
        <header class="topbar" style="height:60px; display:flex; align-items:center; justify-content:space-between; padding:0 28px; flex-shrink:0;">
            {{-- Left: Page Title --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="font-size:14px; font-weight:700; color:var(--text-primary); letter-spacing:-0.02em;">
                    @if(request()->routeIs('dashboard'))
                        <span style="color:var(--text-muted);">Dashboard</span>
                    @elseif(request()->routeIs('employees.create'))
                        <a href="{{ route('employees.index') }}" style="color:var(--text-muted); text-decoration:none;">Employees</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">New Employee</span>
                    @elseif(request()->routeIs('employees.edit'))
                        <a href="{{ route('employees.index') }}" style="color:var(--text-muted); text-decoration:none;">Employees</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Edit</span>
                    @elseif(request()->routeIs('employees.show'))
                        <a href="{{ route('employees.index') }}" style="color:var(--text-muted); text-decoration:none;">Employees</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Profile</span>
                    @elseif(request()->routeIs('employees.*'))
                        <span style="color:var(--text-muted);">Employees</span>
                    @elseif(request()->routeIs('teams.create'))
                        <a href="{{ route('teams.index') }}" style="color:var(--text-muted); text-decoration:none;">Teams</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">New Team</span>
                    @elseif(request()->routeIs('teams.edit'))
                        <a href="{{ route('teams.index') }}" style="color:var(--text-muted); text-decoration:none;">Teams</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Edit</span>
                    @elseif(request()->routeIs('teams.show'))
                        <a href="{{ route('teams.index') }}" style="color:var(--text-muted); text-decoration:none;">Teams</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Details</span>
                    @elseif(request()->routeIs('teams.*'))
                        <span style="color:var(--text-muted);">Teams</span>
                    @elseif(request()->routeIs('tasks.create'))
                        <a href="{{ route('tasks.index') }}" style="color:var(--text-muted); text-decoration:none;">Tasks</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">New Task</span>
                    @elseif(request()->routeIs('tasks.edit'))
                        <a href="{{ route('tasks.index') }}" style="color:var(--text-muted); text-decoration:none;">Tasks</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Edit</span>
                    @elseif(request()->routeIs('tasks.show'))
                        <a href="{{ route('tasks.index') }}" style="color:var(--text-muted); text-decoration:none;">Tasks</a>
                        <span style="color:#334155; margin:0 6px;">/</span>
                        <span style="color:var(--text-secondary);">Details</span>
                    @elseif(request()->routeIs('tasks.*'))
                        <span style="color:var(--text-muted);">Tasks</span>
                    @elseif(request()->routeIs('leaderboard'))
                        <span style="color:var(--text-muted);">Leaderboard</span>
                    @elseif(request()->routeIs('ai.report.*'))
                        <span style="color:var(--text-muted);">AI Reports</span>
                    @elseif(request()->routeIs('vcs.*'))
                        <span style="color:var(--text-muted);">VCS Integration</span>
                    @elseif(request()->routeIs('developer.tools'))
                        <span style="color:var(--text-muted);">Developer Tools</span>
                    @else
                        <span style="color:var(--text-muted);">Overview</span>
                    @endif
                </div>
            </div>

            {{-- Right: Date & Status --}}
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="font-size:12px; color:var(--text-muted); font-weight:500;">
                    {{ now()->format('D, M d, Y') }}
                </div>
                <div style="width:1px; height:18px; background:rgba(99,102,241,0.15);"></div>
            </div>{{-- /right flex container --}}
        </header>

        {{-- Main Content --}}
        <main style="flex:1; overflow-y:auto; padding:28px 28px;">
            @yield('content')
        </main>
    </div>
</div>

<x-chatbot />

@yield('scripts')
</body>
</html>
