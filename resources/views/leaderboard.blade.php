@extends('layouts.app')

@section('content')

{{--
    ══════════════════════════════════════════════════════════════════════
    LEADERBOARD — Modern SaaS Design
    ──────────────────────────────────────────────────────────────────────
    Technology: Tailwind CSS, Alpine.js
    Data:       $leaderboard (Collection of LeadershipScore with ->employee)
    ══════════════════════════════════════════════════════════════════════
--}}

<div
    x-data="{ search: '', sortBy: 'leadership', activeTab: 'individual' }"
    class="ea-lb-page animate-fade-up"
    style="max-width:900px;"
    id="leaderboard-page"
>

<style>
    .ea-lb-page { font-family:'Inter',sans-serif; }

    /* ── COMPACT PODIUM ─────────────────────────────────────────── */
    .ea-podium-wrap {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        padding: 0 4px;
    }

    .ea-pod-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        border-radius: 14px;
        padding: 14px 12px 12px;
        width: 148px;
        overflow: hidden;
        transition: transform .25s ease, box-shadow .25s ease;
        cursor: default;
    }
    .ea-pod-card:hover { transform: translateY(-4px); }

    /* Shimmer sweep */
    .ea-pod-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.07) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform .55s ease;
        pointer-events: none;
    }
    .ea-pod-card:hover::before { transform: translateX(100%); }

    /* Rank-specific themes */
    .ea-pod-1 {
        background: linear-gradient(160deg, #1c1507 0%, #2b1d02 60%, #1a1200 100%);
        border: 1.5px solid rgba(245,158,11,.45);
        box-shadow: 0 0 0 1px rgba(245,158,11,.08), 0 8px 28px rgba(245,158,11,.18), inset 0 1px 0 rgba(255,255,255,.04);
        min-height: 200px;
    }
    .ea-pod-2 {
        background: linear-gradient(160deg, #0f1117 0%, #1a1f2e 100%);
        border: 1.5px solid rgba(148,163,184,.3);
        box-shadow: 0 0 0 1px rgba(148,163,184,.06), 0 6px 20px rgba(148,163,184,.12);
        min-height: 172px;
    }
    .ea-pod-3 {
        background: linear-gradient(160deg, #120e09 0%, #1e1409 100%);
        border: 1.5px solid rgba(194,133,94,.3);
        box-shadow: 0 0 0 1px rgba(194,133,94,.06), 0 6px 20px rgba(194,133,94,.12);
        min-height: 172px;
    }

    /* Medal crown */
    .ea-pod-crown {
        font-size: 22px;
        line-height: 1;
        margin-bottom: 6px;
        filter: drop-shadow(0 2px 6px rgba(0,0,0,.5));
    }

    /* Avatar */
    .ea-pod-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 8px;
        flex-shrink: 0;
    }
    .ea-pod-1 .ea-pod-avatar {
        width: 60px;
        height: 60px;
        border: 2.5px solid #f59e0b;
        box-shadow: 0 0 16px rgba(245,158,11,.35);
    }
    .ea-pod-2 .ea-pod-avatar { border: 2px solid #64748b; }
    .ea-pod-3 .ea-pod-avatar { border: 2px solid #a16136; }

    /* Name */
    .ea-pod-name {
        font-size: 12.5px;
        font-weight: 800;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
        color: #f1f5f9;
        margin-bottom: 2px;
    }
    .ea-pod-1 .ea-pod-name { font-size: 13.5px; color: #fef3c7; }

    /* Role */
    .ea-pod-role {
        font-size: 10px;
        color: #64748b;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
        margin-bottom: 10px;
        font-weight: 500;
    }

    /* Score row */
    .ea-pod-scores {
        display: flex;
        gap: 5px;
        width: 100%;
        justify-content: center;
    }
    .ea-pod-score-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 5px 8px;
        border-radius: 8px;
        min-width: 52px;
        flex: 1;
    }
    .ea-pod-score-label {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        opacity: .6;
    }
    .ea-pod-score-val {
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
    }

    /* Gold chips */
    .ea-pod-1 .ea-chip-prod { background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.25); }
    .ea-pod-1 .ea-chip-prod .ea-pod-score-label { color: #d97706; }
    .ea-pod-1 .ea-chip-prod .ea-pod-score-val { color: #fbbf24; }
    .ea-pod-1 .ea-chip-lead { background: rgba(99,102,241,.12); border: 1px solid rgba(99,102,241,.25); }
    .ea-pod-1 .ea-chip-lead .ea-pod-score-label { color: #818cf8; }
    .ea-pod-1 .ea-chip-lead .ea-pod-score-val { color: #a5b4fc; }

    /* Silver chips */
    .ea-pod-2 .ea-chip-prod { background: rgba(148,163,184,.1); border: 1px solid rgba(148,163,184,.2); }
    .ea-pod-2 .ea-chip-prod .ea-pod-score-label { color: #94a3b8; }
    .ea-pod-2 .ea-chip-prod .ea-pod-score-val { color: #cbd5e1; }
    .ea-pod-2 .ea-chip-lead { background: rgba(99,102,241,.08); border: 1px solid rgba(99,102,241,.18); }
    .ea-pod-2 .ea-chip-lead .ea-pod-score-label { color: #818cf8; }
    .ea-pod-2 .ea-chip-lead .ea-pod-score-val { color: #a5b4fc; }

    /* Bronze chips */
    .ea-pod-3 .ea-chip-prod { background: rgba(194,133,94,.1); border: 1px solid rgba(194,133,94,.2); }
    .ea-pod-3 .ea-chip-prod .ea-pod-score-label { color: #c2855e; }
    .ea-pod-3 .ea-chip-prod .ea-pod-score-val { color: #d4956e; }
    .ea-pod-3 .ea-chip-lead { background: rgba(99,102,241,.08); border: 1px solid rgba(99,102,241,.18); }
    .ea-pod-3 .ea-chip-lead .ea-pod-score-label { color: #818cf8; }
    .ea-pod-3 .ea-chip-lead .ea-pod-score-val { color: #a5b4fc; }

    /* Rank label strip at bottom */
    .ea-pod-rank-strip {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 0 0 14px 14px;
    }
    .ea-pod-1 .ea-pod-rank-strip { background: linear-gradient(90deg,#f59e0b,#d97706); }
    .ea-pod-2 .ea-pod-rank-strip { background: linear-gradient(90deg,#94a3b8,#64748b); }
    .ea-pod-3 .ea-pod-rank-strip { background: linear-gradient(90deg,#c2855e,#a16136); }

    /* Table card */
    .ea-lb-table-card { background:var(--surface, #0e1018); border:1px solid rgba(99,102,241,.12); border-radius:16px; overflow:hidden; }

    /* Row */
    .ea-lb-tr { display:grid; grid-template-columns:56px 1fr 120px 120px; align-items:center; padding:13px 22px; border-bottom:1px solid rgba(99,102,241,.06); transition:background .15s; }
    .ea-lb-tr:last-child { border-bottom:none; }
    .ea-lb-tr:hover { background:rgba(99,102,241,.04); }
    .ea-lb-tr-head { background:rgba(99,102,241,.06); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); }
    .ea-lb-tr-head:hover { background:rgba(99,102,241,.06); }

    /* Rank badge */
    .ea-rank-badge { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; }
    .r1 { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 4px 12px rgba(245,158,11,.4); }
    .r2 { background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; box-shadow:0 4px 12px rgba(148,163,184,.3); }
    .r3 { background:linear-gradient(135deg,#c2855e,#a16136); color:#fff; box-shadow:0 4px 12px rgba(194,133,94,.3); }
    .r-n { background:rgba(99,102,241,.1); color:var(--text-muted); }

    /* Score pill */
    .ea-score-pill { display:inline-flex; align-items:baseline; gap:3px; font-size:16px; font-weight:800; }
    .ea-score-denom { font-size:11px; font-weight:500; color:var(--text-muted); }

    /* Search input */
    .ea-search { background:rgba(99,102,241,.07); border:1px solid rgba(99,102,241,.15); border-radius:10px; padding:9px 14px 9px 38px; font-size:13px; color:var(--text-primary); outline:none; width:220px; transition:all .2s; }
    .ea-search:focus { border-color:rgba(99,102,241,.4); background:rgba(99,102,241,.1); }
    .ea-search::placeholder { color:var(--text-muted); }

    @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
    .animate-fade-up { animation:fadeUp .35s ease forwards; }

    @keyframes podPop {
        0%   { opacity:0; transform:translateY(18px) scale(.96); }
        100% { opacity:1; transform:translateY(0)   scale(1); }
    }
    .ea-pod-card { animation: podPop .4s ease forwards; }
    .ea-pod-card:nth-child(1) { animation-delay:.05s; }
    .ea-pod-card:nth-child(2) { animation-delay:.1s; }
    .ea-pod-card:nth-child(3) { animation-delay:.15s; }
</style>

    {{-- ── PAGE HEADER ───────────────────────────────────────────────── --}}
    <div style="margin-bottom:20px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 class="page-title" style="font-size:22px;">🏆 Leaderboard</h1>
            <p class="page-subtitle" x-text="activeTab === 'individual' ? 'Ranked by leadership score · productivity · consistency' : 'Ranked by team leadership score · productivity · collaboration'"></p>
            @if(isset($periodLabel) && $periodLabel !== 'All Time')
                <span style="display:inline-flex; align-items:center; gap:5px; margin-top:6px; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; background:rgba(99,102,241,0.12); color:#818cf8; border:1px solid rgba(99,102,241,0.25);">
                    📅 {{ $periodLabel }}
                </span>
            @endif
        </div>
        <div style="display:flex; align-items:center; gap:10px;" x-show="activeTab === 'individual'">
            <a
                href="{{ route('leaderboard.export') }}"
                id="export-csv-btn"
                style="display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-size:12px; font-weight:700; text-decoration:none; border:1px solid rgba(22,163,74,0.4); box-shadow:0 4px 14px rgba(22,163,74,0.25); transition:all .2s; letter-spacing:.02em;"
                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(22,163,74,0.35)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(22,163,74,0.25)';"
            >
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ── PERIOD FILTER BAR ─────────────────────────────────────────────── --}}
    <div style="margin-bottom:22px;">

        {{-- Quick period pills --}}
        <form method="GET" action="{{ route('leaderboard.filtered') }}" id="period-filter-form">
            @php
                $activePeriod = $period ?? 'all';
                $periods = [
                    'all'       => ['label' => 'All Time',   'icon' => '∞'],
                    'daily'     => ['label' => 'Daily',      'icon' => '☀️'],
                    'weekly'    => ['label' => 'Weekly',     'icon' => '📆'],
                    'monthly'   => ['label' => 'Monthly',    'icon' => '🗓️'],
                    'quarterly' => ['label' => 'Quarterly',  'icon' => '📊'],
                    'yearly'    => ['label' => 'Yearly',     'icon' => '🌟'],
                    'custom'    => ['label' => 'Custom',     'icon' => '🔧'],
                ];
            @endphp

            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:6px;">
                @foreach($periods as $key => $meta)
                    @if($key === 'all')
                        <a href="{{ route('leaderboard') }}"
                           id="period-btn-all"
                           style="display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:10px; font-size:12px; font-weight:700; text-decoration:none; transition:all .2s; cursor:pointer; border:1px solid;
                               {{ $activePeriod === 'all' ? 'background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border-color:transparent; box-shadow:0 4px 12px rgba(99,102,241,0.3);' : 'background:rgba(99,102,241,0.07); color:var(--text-muted); border-color:rgba(99,102,241,0.15);' }}"
                        >{{ $meta['icon'] }} {{ $meta['label'] }}</a>
                    @elseif($key === 'custom')
                        <button type="button"
                            id="period-btn-custom"
                            onclick="document.getElementById('custom-range-box').style.display = document.getElementById('custom-range-box').style.display === 'none' ? 'flex' : 'none';"
                            style="display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; border:1px solid; font-family:inherit;
                                {{ $activePeriod === 'custom' ? 'background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border-color:transparent; box-shadow:0 4px 12px rgba(99,102,241,0.3);' : 'background:rgba(99,102,241,0.07); color:var(--text-muted); border-color:rgba(99,102,241,0.15);' }}"
                        >{{ $meta['icon'] }} {{ $meta['label'] }} ▾</button>
                    @else
                        <button type="submit" name="period" value="{{ $key }}"
                            id="period-btn-{{ $key }}"
                            style="display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; border:1px solid; font-family:inherit;
                                {{ $activePeriod === $key ? 'background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border-color:transparent; box-shadow:0 4px 12px rgba(99,102,241,0.3);' : 'background:rgba(99,102,241,0.07); color:var(--text-muted); border-color:rgba(99,102,241,0.15);' }}"
                            onmouseover="if('{{ $activePeriod }}' !== '{{ $key }}') { this.style.background='rgba(99,102,241,0.14)'; this.style.color='#818cf8'; }"
                            onmouseout="if('{{ $activePeriod }}' !== '{{ $key }}') { this.style.background='rgba(99,102,241,0.07)'; this.style.color='var(--text-muted)'; }"
                        >{{ $meta['icon'] }} {{ $meta['label'] }}</button>
                    @endif
                @endforeach
            </div>

            {{-- Custom date range expander --}}
            <div id="custom-range-box"
                 style="display:{{ $activePeriod === 'custom' ? 'flex' : 'none' }}; align-items:center; gap:10px; margin-top:12px; flex-wrap:wrap; padding:14px 16px; background:rgba(99,102,241,0.05); border:1px solid rgba(99,102,241,0.15); border-radius:12px;">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted);">From</span>
                <input type="date" name="from" id="custom-from"
                       value="{{ request('from', $from ? $from->format('Y-m-d') : '') }}"
                       style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); border-radius:8px; padding:6px 10px; font-size:12px; color:var(--text-primary); outline:none; font-family:inherit;">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted);">To</span>
                <input type="date" name="to" id="custom-to"
                       value="{{ request('to', $to ? $to->format('Y-m-d') : '') }}"
                       style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); border-radius:8px; padding:6px 10px; font-size:12px; color:var(--text-primary); outline:none; font-family:inherit;">
                <button type="submit" name="period" value="custom"
                        style="display:inline-flex; align-items:center; gap:5px; padding:7px 14px; border-radius:8px; background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; font-size:12px; font-weight:700; cursor:pointer; border:none; font-family:inherit;">
                    Apply Range
                </button>
            </div>
        </form>
    </div>

    {{-- ── LEADERBOARD TABS ─────────────────────────────────────────── --}}
    <div style="display:inline-flex; background:rgba(99,102,241,0.07); border:1px solid rgba(99,102,241,0.15); padding:4px; border-radius:12px; margin-bottom:24px;">
        <button
            @click="activeTab = 'individual'; search = '';"
            :style="activeTab === 'individual' ? 'background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow:0 4px 12px rgba(99,102,241,0.3); border:none;' : 'background:transparent; color:var(--text-muted); border:none;'"
            style="padding:8px 18px; border-radius:9px; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; outline:none;"
        >
            👤 Individual Performance
        </button>
        <button
            @click="activeTab = 'team'; search = '';"
            :style="activeTab === 'team' ? 'background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow:0 4px 12px rgba(99,102,241,0.3); border:none;' : 'background:transparent; color:var(--text-muted); border:none;'"
            style="padding:8px 18px; border-radius:9px; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; outline:none;"
        >
            👥 Team Performance
        </button>
    </div>

    {{-- ── TOP 3 PODIUM — Compact Premium Cards ──────────────────── --}}
    @if($leaderboard->count() >= 3)
    @php
        $sorted = $leaderboard->values();
        $first  = $sorted->get(0);
        $second = $sorted->get(1);
        $third  = $sorted->get(2);
    @endphp

    <div x-show="activeTab === 'individual'" class="ea-podium-wrap" id="leaderboard-podium">

        {{-- ─── 2nd Place (left, shorter) ─── --}}
        <div class="ea-pod-card ea-pod-2">
            <div class="ea-pod-crown">🥈</div>
            <img
                class="ea-pod-avatar"
                src="https://ui-avatars.com/api/?name={{ urlencode($second->employee->name ?? 'B') }}&background=475569&color=fff&size=100"
                alt="{{ $second->employee->name ?? '2nd' }}">
            <p class="ea-pod-name">{{ $second->employee->name ?? '—' }}</p>
            <p class="ea-pod-role">{{ $second->employee->role ?? $second->employee->department ?? 'Engineer' }}</p>
            <div class="ea-pod-scores">
                <div class="ea-pod-score-chip ea-chip-prod">
                    <span class="ea-pod-score-label">🔥 Prod</span>
                    <span class="ea-pod-score-val">{{ number_format($second->productivity_score, 1) }}</span>
                </div>
                <div class="ea-pod-score-chip ea-chip-lead">
                    <span class="ea-pod-score-label">⭐ Lead</span>
                    <span class="ea-pod-score-val">{{ number_format($second->leadership_score, 1) }}</span>
                </div>
            </div>
            <div class="ea-pod-rank-strip"></div>
        </div>

        {{-- ─── 1st Place (center, tallest) ─── --}}
        <div class="ea-pod-card ea-pod-1">
            <div class="ea-pod-crown">🥇</div>
            <img
                class="ea-pod-avatar"
                src="https://ui-avatars.com/api/?name={{ urlencode($first->employee->name ?? 'A') }}&background=b45309&color=fff&size=120"
                alt="{{ $first->employee->name ?? '1st' }}">
            <p class="ea-pod-name">{{ $first->employee->name ?? '—' }}</p>
            <p class="ea-pod-role">{{ $first->employee->role ?? $first->employee->department ?? 'Engineer' }}</p>
            <div class="ea-pod-scores">
                <div class="ea-pod-score-chip ea-chip-prod">
                    <span class="ea-pod-score-label">🔥 Prod</span>
                    <span class="ea-pod-score-val">{{ number_format($first->productivity_score, 1) }}</span>
                </div>
                <div class="ea-pod-score-chip ea-chip-lead">
                    <span class="ea-pod-score-label">⭐ Lead</span>
                    <span class="ea-pod-score-val">{{ number_format($first->leadership_score, 1) }}</span>
                </div>
            </div>
            <div class="ea-pod-rank-strip"></div>
        </div>

        {{-- ─── 3rd Place (right, shorter) ─── --}}
        <div class="ea-pod-card ea-pod-3">
            <div class="ea-pod-crown">🥉</div>
            <img
                class="ea-pod-avatar"
                src="https://ui-avatars.com/api/?name={{ urlencode($third->employee->name ?? 'C') }}&background=92400e&color=fff&size=100"
                alt="{{ $third->employee->name ?? '3rd' }}">
            <p class="ea-pod-name">{{ $third->employee->name ?? '—' }}</p>
            <p class="ea-pod-role">{{ $third->employee->role ?? $third->employee->department ?? 'Engineer' }}</p>
            <div class="ea-pod-scores">
                <div class="ea-pod-score-chip ea-chip-prod">
                    <span class="ea-pod-score-label">🔥 Prod</span>
                    <span class="ea-pod-score-val">{{ number_format($third->productivity_score, 1) }}</span>
                </div>
                <div class="ea-pod-score-chip ea-chip-lead">
                    <span class="ea-pod-score-label">⭐ Lead</span>
                    <span class="ea-pod-score-val">{{ number_format($third->leadership_score, 1) }}</span>
                </div>
            </div>
            <div class="ea-pod-rank-strip"></div>
        </div>

    </div>
    @endif


    {{-- ── SEARCH & FILTER ────────────────────────────────────────────── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted);">
            All Rankings · 
            <span x-show="activeTab === 'individual'" style="color:#818cf8;">{{ $leaderboard->count() }} members</span>
            <span x-show="activeTab === 'team'" style="color:#818cf8; display:none;">{{ $teamLeaderboard->count() }} teams</span>
        </p>
        <div style="position:relative;">
            <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input
                type="text"
                class="ea-search"
                id="leaderboard-search"
                :placeholder="activeTab === 'individual' ? 'Search member…' : 'Search team…'"
                x-model="search"
            >
        </div>
    </div>

    {{-- ── FULL TABLE ──────────────────────────────────────────────────── --}}
    <div x-show="activeTab === 'individual'" class="ea-lb-table-card" id="leaderboard-table">

        {{-- Header --}}
        <div class="ea-lb-tr ea-lb-tr-head">
            <div>Rank</div>
            <div>Member</div>
            <div style="text-align:right;">Productivity</div>
            <div style="text-align:right;">Leadership</div>
        </div>

        @forelse($leaderboard->values() as $entry)
            <div
                class="ea-lb-tr"
                id="lb-row-{{ $loop->iteration }}"
                x-show="!search || '{{ strtolower($entry->employee->name ?? '') }}'.includes(search.toLowerCase())"
                style="{{ $loop->first ? 'background:rgba(245,158,11,.04); border-left:3px solid #f59e0b;' : ($loop->last ? 'background:rgba(239,68,68,.03); border-left:3px solid rgba(239,68,68,.25);' : '') }}"
            >
                {{-- Rank Serial Number --}}
                <div>
                    <div class="ea-rank-badge {{ $loop->iteration === 1 ? 'r1' : ($loop->iteration === 2 ? 'r2' : ($loop->iteration === 3 ? 'r3' : 'r-n')) }}">
                        {{ $loop->iteration }}
                    </div>
                </div>

                {{-- Member --}}
                <div style="display:flex; align-items:center; gap:11px;">
                    <img
                        style="width:36px; height:36px; border-radius:10px; object-fit:cover; flex-shrink:0;"
                        src="https://ui-avatars.com/api/?name={{ urlencode($entry->employee->name ?? 'U') }}&background={{ $loop->iteration === 1 ? 'd97706' : ($loop->iteration === 2 ? '64748b' : ($loop->iteration === 3 ? 'a16136' : '4f46e5')) }}&color=fff&size=72"
                        alt="{{ $entry->employee->name ?? 'Unknown' }}"
                    >
                    <div>
                        <p style="font-size:13.5px; font-weight:700; color:var(--text-primary);">{{ $entry->employee->name ?? '—' }}</p>
                        <p style="font-size:11px; color:var(--text-muted);">
                            {{ $entry->employee->role ?? ($entry->employee->department ?? 'Engineer') }}
                        </p>
                    </div>
                    {{-- Top performer badge --}}
                    @if($loop->first)
                        <span style="font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:99px; background:rgba(245,158,11,.12); color:#f59e0b; border:1px solid rgba(245,158,11,.25); margin-left:4px; flex-shrink:0;">⭐ Rank 1</span>
                    @elseif($loop->last)
                        <span style="font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:99px; background:rgba(239,68,68,.08); color:#f87171; border:1px solid rgba(239,68,68,.2); margin-left:4px; flex-shrink:0;">Rank {{ $loop->iteration }}</span>
                    @endif
                </div>

                {{-- Productivity Score --}}
                <div style="text-align:right;">
                    <span class="ea-score-pill" style="color:{{ $entry->productivity_score >= 7 ? '#4ade80' : ($entry->productivity_score >= 5 ? '#fbbf24' : '#94a3b8') }};">
                        {{ number_format($entry->productivity_score, 2) }}
                        <span class="ea-score-denom">/10</span>
                    </span>
                </div>

                {{-- Leadership Score --}}
                <div style="text-align:right;">
                    <span class="ea-score-pill" style="color:{{ $entry->leadership_score >= 7 ? '#818cf8' : ($entry->leadership_score >= 5 ? '#fbbf24' : '#94a3b8') }};">
                        {{ number_format($entry->leadership_score, 2) }}
                        <span class="ea-score-denom">/10</span>
                    </span>
                </div>
            </div>
        @empty
            <div style="padding:56px 22px; text-align:center; color:#334155;">
                <div style="font-size:40px; margin-bottom:12px;">🏆</div>
                <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No leaderboard data yet.</p>
                <p style="font-size:12.5px; color:#334155; margin-top:6px;">
                    Run <code style="font-size:11.5px; background:rgba(99,102,241,.1); color:#818cf8; padding:2px 8px; border-radius:5px;">php artisan engineering</code> to generate scores.
                </p>
            </div>
        @endforelse

        {{-- No search results message --}}
        @if($leaderboard->count())
        <div x-show="search && !$el.previousElementSibling.querySelectorAll('[x-show]').length" style="padding:32px; text-align:center; color:var(--text-muted); font-size:13px;">
            No members match "<span x-text="search" style="color:#818cf8;"></span>"
        </div>
        @endif
    </div>

    {{-- ── TEAM TABLE ──────────────────────────────────────────────────── --}}
    <div x-show="activeTab === 'team'" class="ea-lb-table-card" id="team-leaderboard-table" style="display:none;">

        {{-- Header --}}
        <div class="ea-lb-tr ea-lb-tr-head" style="grid-template-columns: 56px 1fr 100px 140px 110px 110px 140px;">
            <div>Rank</div>
            <div>Team Name</div>
            <div style="text-align:right;">Members</div>
            <div style="text-align:right;">Completed Tasks</div>
            <div style="text-align:right;">Productivity</div>
            <div style="text-align:right;">Leadership</div>
            <div style="text-align:center;">AI Report</div>
        </div>

        @forelse($teamLeaderboard as $teamData)
            <div
                class="ea-lb-tr"
                id="team-lb-row-{{ $loop->iteration }}"
                x-show="!search || '{{ strtolower($teamData['team_name'] ?? '') }}'.includes(search.toLowerCase())"
                style="grid-template-columns: 56px 1fr 100px 140px 110px 110px 140px; {{ $loop->first ? 'background:rgba(245,158,11,.04); border-left:3px solid #f59e0b;' : ($loop->last && $loop->iteration > 1 ? 'background:rgba(239,68,68,.03); border-left:3px solid rgba(239,68,68,.25);' : '') }}"
            >
                {{-- Rank Serial Number --}}
                <div>
                    <div class="ea-rank-badge {{ $loop->iteration === 1 ? 'r1' : ($loop->iteration === 2 ? 'r2' : ($loop->iteration === 3 ? 'r3' : 'r-n')) }}">
                        {{ $loop->iteration }}
                    </div>
                </div>

                {{-- Team Name --}}
                <div style="display:flex; align-items:center; gap:11px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.25); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                        👥
                    </div>
                    <div>
                        <p style="font-size:13.5px; font-weight:700; color:var(--text-primary);">{{ $teamData['team_name'] }}</p>
                        @if(!empty($teamData['description']))
                            <p style="font-size:11px; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;">
                                {{ $teamData['description'] }}
                            </p>
                        @endif
                    </div>
                    {{-- Top performer badge --}}
                    @if($loop->first)
                        <span style="font-size:9.5px; font-weight:700; padding:2px 7px; border-radius:99px; background:rgba(245,158,11,.12); color:#f59e0b; border:1px solid rgba(245,158,11,.25); margin-left:4px; flex-shrink:0;">🏆 Top Team</span>
                    @endif
                </div>

                {{-- Team Members Count --}}
                <div style="text-align:right; font-size:13.5px; font-weight:700; color:var(--text-primary);">
                    {{ $teamData['members_count'] }}
                </div>

                {{-- Completed Tasks --}}
                <div style="text-align:right; font-size:13.5px; font-weight:700; color:var(--text-primary);">
                    {{ $teamData['completed_tasks'] }}
                </div>

                {{-- Productivity Score --}}
                <div style="text-align:right;">
                    <span class="ea-score-pill" style="color:{{ $teamData['productivity_score'] >= 7 ? '#4ade80' : ($teamData['productivity_score'] >= 5 ? '#fbbf24' : '#94a3b8') }};">
                        {{ number_format($teamData['productivity_score'], 2) }}
                        <span class="ea-score-denom">/10</span>
                    </span>
                </div>

                {{-- Leadership Score --}}
                <div style="text-align:right;">
                    <span class="ea-score-pill" style="color:{{ $teamData['leadership_score'] >= 7 ? '#818cf8' : ($teamData['leadership_score'] >= 5 ? '#fbbf24' : '#94a3b8') }};">
                        {{ number_format($teamData['leadership_score'], 2) }}
                        <span class="ea-score-denom">/10</span>
                    </span>
                </div>

                {{-- AI Report link --}}
                <div style="text-align:center;">
                    <a
                        href="{{ route('ai.report.show_team', $teamData['team_id']) }}"
                        style="display:inline-flex; align-items:center; justify-content:center; gap:4px; padding:6px 12px; border-radius:8px; background:rgba(99,102,241,0.08); color:#818cf8; border:1px solid rgba(99,102,241,0.2); font-size:11.5px; font-weight:700; text-decoration:none; transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(99,102,241,0.14)'; this.style.transform='translateY(-0.5px)';"
                        onmouseout="this.style.background='rgba(99,102,241,0.08)'; this.style.transform='translateY(0)';"
                    >
                        🧠 Report
                    </a>
                </div>
            </div>
        @empty
            <div style="padding:56px 22px; text-align:center; color:#334155;">
                <div style="font-size:40px; margin-bottom:12px;">👥</div>
                <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No team leaderboard data yet.</p>
            </div>
        @endforelse

        {{-- No search results message --}}
        @if($teamLeaderboard->count())
        <div x-show="search && !Array.from($el.parentElement.querySelectorAll('.ea-lb-tr')).slice(1).some(row => row.style.display !== 'none')" style="padding:32px; text-align:center; color:var(--text-muted); font-size:13px; display:none;">
            No teams match "<span x-text="search" style="color:#818cf8;"></span>"
        </div>
        @endif
    </div>

</div>

@endsection
