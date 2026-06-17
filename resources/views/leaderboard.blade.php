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
    x-data="{ search: '', sortBy: 'leadership' }"
    class="ea-lb-page animate-fade-up"
    style="max-width:900px;"
    id="leaderboard-page"
>

<style>
    .ea-lb-page { font-family:'Inter',sans-serif; }

    /* Hero podium */
    .ea-podium { display:flex; align-items:flex-end; justify-content:center; gap:20px; margin-bottom:36px; }
    .ea-podium-slot { display:flex; flex-direction:column; align-items:center; gap:8px; }
    .ea-podium-avatar { border-radius:16px; object-fit:cover; box-shadow:0 8px 24px rgba(0,0,0,.4); }
    .ea-podium-base { border-radius:10px 10px 0 0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; color:#fff; }
    .ea-rank-chip { font-size:11px; font-weight:900; padding:3px 10px; border-radius:99px; letter-spacing:.04em; display:inline-block; margin-bottom:2px; }
    .rank-chip-1 { background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 2px 10px rgba(245,158,11,.5); }
    .rank-chip-2 { background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; box-shadow:0 2px 8px rgba(148,163,184,.4); }
    .rank-chip-3 { background:linear-gradient(135deg,#c2855e,#a16136); color:#fff; box-shadow:0 2px 8px rgba(194,133,94,.4); }

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
</style>

    {{-- ── PAGE HEADER ───────────────────────────────────────────────── --}}
    <div style="margin-bottom:28px; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 class="page-title" style="font-size:22px;">🏆 Leaderboard</h1>
            <p class="page-subtitle">Ranked by leadership score · productivity · consistency</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
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

    {{-- ── TOP 3 PODIUM ──────────────────────────────────────────────── --}}
    @if($leaderboard->count() >= 3)
    @php
        $sorted = $leaderboard->values();
        $first  = $sorted->get(0);
        $second = $sorted->get(1);
        $third  = $sorted->get(2);
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:28px;" id="leaderboard-podium">

        {{-- 1st Place --}}
        <div style="background:var(--surface); border:2px solid #f59e0b; border-radius:16px; padding:20px 16px; display:flex; flex-direction:column; align-items:center; gap:8px; box-shadow:0 4px 20px rgba(245,158,11,0.12); position:relative; order:1;">
            <span style="position:absolute; top:12px; left:12px; font-size:10px; font-weight:800; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; padding:2px 8px; border-radius:99px; letter-spacing:.05em;">🥇 1ST</span>
            <img
                style="width:56px; height:56px; border-radius:12px; object-fit:cover; border:2px solid #f59e0b; margin-top:12px;"
                src="https://ui-avatars.com/api/?name={{ urlencode($first->employee->name ?? 'A') }}&background=d97706&color=fff&size=112"
                alt="{{ $first->employee->name ?? '1st' }}">
            <p style="font-size:13.5px; font-weight:800; color:var(--text); text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;">{{ $first->employee->name ?? '—' }}</p>
            <p style="font-size:11px; color:var(--muted); text-align:center;">{{ $first->employee->role ?? $first->employee->department ?? 'Engineer' }}</p>
            <div style="display:flex; gap:8px; margin-top:4px;">
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.2);">
                    🔥 {{ number_format($first->productivity_score, 1) }}
                </span>
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.2);">
                    ⭐ {{ number_format($first->leadership_score, 1) }}
                </span>
            </div>
        </div>

        {{-- 2nd Place --}}
        <div style="background:var(--surface); border:2px solid rgba(100,116,139,0.4); border-radius:16px; padding:20px 16px; display:flex; flex-direction:column; align-items:center; gap:8px; box-shadow:0 4px 16px rgba(0,0,0,0.05); position:relative; order:2;">
            <span style="position:absolute; top:12px; left:12px; font-size:10px; font-weight:800; background:linear-gradient(135deg,#94a3b8,#64748b); color:#fff; padding:2px 8px; border-radius:99px; letter-spacing:.05em;">🥈 2ND</span>
            <img
                style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:2px solid #64748b; margin-top:12px;"
                src="https://ui-avatars.com/api/?name={{ urlencode($second->employee->name ?? 'B') }}&background=64748b&color=fff&size=104"
                alt="{{ $second->employee->name ?? '2nd' }}">
            <p style="font-size:13px; font-weight:700; color:var(--text); text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;">{{ $second->employee->name ?? '—' }}</p>
            <p style="font-size:11px; color:var(--muted); text-align:center;">{{ $second->employee->role ?? $second->employee->department ?? 'Engineer' }}</p>
            <div style="display:flex; gap:8px; margin-top:4px;">
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.2);">
                    🔥 {{ number_format($second->productivity_score, 1) }}
                </span>
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.2);">
                    ⭐ {{ number_format($second->leadership_score, 1) }}
                </span>
            </div>
        </div>

        {{-- 3rd Place --}}
        <div style="background:var(--surface); border:2px solid rgba(163,120,83,0.4); border-radius:16px; padding:20px 16px; display:flex; flex-direction:column; align-items:center; gap:8px; box-shadow:0 4px 16px rgba(0,0,0,0.05); position:relative; order:3;">
            <span style="position:absolute; top:12px; left:12px; font-size:10px; font-weight:800; background:linear-gradient(135deg,#c2855e,#a16136); color:#fff; padding:2px 8px; border-radius:99px; letter-spacing:.05em;">🥉 3RD</span>
            <img
                style="width:52px; height:52px; border-radius:12px; object-fit:cover; border:2px solid #a16136; margin-top:12px;"
                src="https://ui-avatars.com/api/?name={{ urlencode($third->employee->name ?? 'C') }}&background=a16136&color=fff&size=104"
                alt="{{ $third->employee->name ?? '3rd' }}">
            <p style="font-size:13px; font-weight:700; color:var(--text); text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;">{{ $third->employee->name ?? '—' }}</p>
            <p style="font-size:11px; color:var(--muted); text-align:center;">{{ $third->employee->role ?? $third->employee->department ?? 'Engineer' }}</p>
            <div style="display:flex; gap:8px; margin-top:4px;">
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.2);">
                    🔥 {{ number_format($third->productivity_score, 1) }}
                </span>
                <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:8px; background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.2);">
                    ⭐ {{ number_format($third->leadership_score, 1) }}
                </span>
            </div>
        </div>

    </div>
    @endif


    {{-- ── SEARCH & FILTER ────────────────────────────────────────────── --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted);">
            All Rankings · <span style="color:#818cf8;">{{ $leaderboard->count() }} members</span>
        </p>
        <div style="position:relative;">
            <svg style="position:absolute; left:11px; top:50%; transform:translateY(-50%); pointer-events:none;" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input
                type="text"
                class="ea-search"
                id="leaderboard-search"
                placeholder="Search member…"
                x-model="search"
            >
        </div>
    </div>

    {{-- ── FULL TABLE ──────────────────────────────────────────────────── --}}
    <div class="ea-lb-table-card" id="leaderboard-table">

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

</div>

@endsection
