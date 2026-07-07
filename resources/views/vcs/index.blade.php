@extends('layouts.app')

@section('content')
<div class="page-container" x-data="vcsState()" x-init="init()">

    {{-- Page Header --}}
    <div class="section-header mb-6">
        <div>
            <h1 class="page-title" style="font-size:22px; font-weight:800; color:var(--text-primary); letter-spacing:-0.03em;">
                VCS Integration
            </h1>
            <p class="page-subtitle">Track engineering activity across version control providers.</p>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
    <div class="alert-success mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Provider Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

        {{-- GitHub Card --}}
        <div class="card p-5" style="background:#fff; border-color:var(--text-primary);">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:10px; background:#0d1117; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#fff"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:14.5px; font-weight:700; color:#111827;">GitHub</p>
                        <p style="font-size:11.5px; color:#6b7280;">Public & Private Repositories</p>
                    </div>
                </div>
                @if($tokens['github'])
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#059669; background:#d1fae5; border:1px solid #a7f3d0; border-radius:99px; padding:3px 10px;">
                        <div style="width:6px; height:6px; border-radius:50%; background:#10b981;"></div> Live
                    </span>
                @else
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:99px; padding:3px 10px;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                        Simulation
                    </span>
                @endif
            </div>
        </div>

        {{-- GitLab Card --}}
        <div class="card p-5" style="background:#fff; border-color:var(--text-primary);">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:10px; background:#fc6d26; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="22" height="22" viewBox="0 0 25 24" fill="#fff"><path d="M24.507 9.5l-.034-.09L21.082.562a.896.896 0 00-1.694.091l-2.29 7.01H7.825L5.535.653a.898.898 0 00-1.694-.09L.451 9.411.416 9.5a6.297 6.297 0 002.09 7.278l.012.01.03.022 5.16 3.867 2.56 1.935 1.554 1.176a1.051 1.051 0 001.268 0l1.555-1.176 2.56-1.935 5.197-3.89.014-.01A6.297 6.297 0 0024.507 9.5z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:14.5px; font-weight:700; color:#111827;">GitLab</p>
                        <p style="font-size:11.5px; color:#6b7280;">Projects & Merge Requests</p>
                    </div>
                </div>
                @if($tokens['gitlab'])
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#059669; background:#d1fae5; border:1px solid #a7f3d0; border-radius:99px; padding:3px 10px;">
                        <div style="width:6px; height:6px; border-radius:50%; background:#10b981;"></div> Live
                    </span>
                @else
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:99px; padding:3px 10px;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                        Simulation
                    </span>
                @endif
            </div>
        </div>

        {{-- Bitbucket Card --}}
        <div class="card p-5" style="background:#fff; border-color:var(--text-primary);">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width:40px; height:40px; border-radius:10px; background:#0052cc; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="22" height="22" viewBox="0 0 32 32" fill="#fff"><path d="M2 5a1 1 0 00-.984 1.17l4.254 24.748a1.37 1.37 0 001.346 1.145h19.084a1 1 0 00.985-.83L30.984 6.17A1 1 0 0030 5H2zm17.692 17.078h-7.382L10.5 13.09h11l-1.808 8.988z"/></svg>
                    </div>
                    <div>
                        <p style="font-size:14.5px; font-weight:700; color:#111827;">Bitbucket</p>
                        <p style="font-size:11.5px; color:#6b7280;">Repos & Pull Requests</p>
                    </div>
                </div>
                @if($tokens['bitbucket'])
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#059669; background:#d1fae5; border:1px solid #a7f3d0; border-radius:99px; padding:3px 10px;">
                        <div style="width:6px; height:6px; border-radius:50%; background:#10b981;"></div> Live
                    </span>
                @else
                    <span style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#6b7280; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:99px; padding:3px 10px;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                        Simulation
                    </span>
                @endif
            </div>
        </div>

    </div>

    {{-- Metrics Table Card --}}
    <div class="card p-6" style="background:#fff;">

        {{-- Card Header --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 style="font-size:15px; font-weight:700; color:#111827;">Version Control System Metrics</h2>
            </div>
            <form method="POST" action="{{ route('vcs.sync') }}">
                @csrf
                <button type="submit"
                    style="display:inline-flex; align-items:center; gap:7px; padding:9px 18px; background:linear-gradient(135deg,#6366f1,#7c3aed); color:#fff; font-size:13px; font-weight:600; border-radius:10px; border:none; cursor:pointer; transition:all 0.2s ease; box-shadow:0 2px 8px rgba(99,102,241,0.35);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Synchronize All Metrics
                </button>
            </form>
        </div>

        {{-- Architecture Info Box --}}
        <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
            <div class="flex items-start gap-2">
                <svg width="15" height="15" fill="none" stroke="#0284c7" stroke-width="2" viewBox="0 0 24 24" style="margin-top:1px; flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                <div>
                    <p style="font-size:12.5px; font-weight:700; color:#0369a1; margin-bottom:3px;">VCS Synchronization Architecture</p>
                    <p style="font-size:11.5px; color:#0369a1; line-height:1.6;">
                        To fetch live commits, pull requests, and reviews from real public/private repositories, configure <code style="background:#e0f2fe; padding:1px 4px; border-radius:3px;">GITHUB_TOKEN</code>, <code style="background:#e0f2fe; padding:1px 4px; border-radius:3px;">GITLAB_TOKEN</code>, or <code style="background:#e0f2fe; padding:1px 4px; border-radius:3px;">BITBUCKET_TOKEN</code> in your <code style="background:#e0f2fe; padding:1px 4px; border-radius:3px;">.env</code> file. If tokens are not set, the integration engine runs in <strong>Simulation Mode</strong> using a deterministic seeded generation based on employee productivity scores.
                    </p>
                </div>
            </div>
        </div>

        {{-- Search and entries controls --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span style="font-size:13px; color:var(--text-muted);">Show</span>
                <select x-model="perPage" style="padding:4px 10px; border:1px solid #e5e7eb; border-radius:6px; font-size:13px; color:#374151; background:#fff; cursor:pointer;">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span style="font-size:13px; color:var(--text-muted);">entries</span>
            </div>
            <div>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search metrics..."
                    style="padding:7px 14px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; color:#374151; background:#fff; width:220px; outline:none; transition: border-color 0.2s;"
                    @focus="$el.style.borderColor = '#6366f1'"
                    @blur="$el.style.borderColor = '#e5e7eb'"
                >
            </div>
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13.5px;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                        <th style="padding:12px 16px; text-align:left; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer; user-select:none;" @click="sortBy('employee')">
                            Employee <span class="sort-arrow">{{ '↕' }}</span>
                        </th>
                        <th style="padding:12px 16px; text-align:left; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap;">Git Username</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap;">Provider</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('commits')">
                            Commits <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('pull_requests')">
                            PRs <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('reviews')">
                            Reviews <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('bugs_fixed')">
                            Bugs Fixed <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('deployments')">
                            Deployments <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:#f59e0b; letter-spacing:0.04em; white-space:nowrap; cursor:pointer;" @click="sortBy('deployment_frequency')">
                            Frequency (per wk) <span class="sort-arrow">↕</span>
                        </th>
                        <th style="padding:12px 16px; text-align:right; font-size:11.5px; font-weight:700; color:#f97316; letter-spacing:0.04em; white-space:nowrap;">Last Synced</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11.5px; font-weight:700; color:var(--text-muted); letter-spacing:0.04em; white-space:nowrap;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in paginated" :key="row.id">
                        <tr style="border-bottom:1px solid #f1f5f9; transition: background 0.15s;"
                            @mouseenter="$el.style.background = '#f8fafc'"
                            @mouseleave="$el.style.background = 'transparent'">
                            <td style="padding:13px 16px;">
                                <a :href="'/employees/' + row.employee_id" style="font-size:13.5px; font-weight:600; color:#6366f1; text-decoration:none;"
                                    @mouseenter="$el.style.textDecoration = 'underline'"
                                    @mouseleave="$el.style.textDecoration = 'none'"
                                    x-text="row.employee_name"></a>
                            </td>
                            <td style="padding:13px 16px;">
                                <a :href="row.provider === 'github' ? 'https://github.com/' + row.git_username : (row.provider === 'gitlab' ? '{{ rtrim(config('services.gitlab.url', 'https://gitlab.com'), '/') }}/' + row.git_username : 'https://bitbucket.org/' + row.git_username)"
                                    target="_blank"
                                    style="font-size:12.5px; font-family:monospace; color:#ec4899; font-weight:500; text-decoration:none;"
                                    @mouseenter="$el.style.textDecoration = 'underline'"
                                    @mouseleave="$el.style.textDecoration = 'none'"
                                    x-text="row.git_username"></a>
                            </td>
                            <td style="padding:13px 16px;">
                                <span x-html="providerBadge(row.provider)"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:600; color:#059669;" x-text="row.commits"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:600; color:#3b82f6;" x-text="row.pull_requests"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:600; color:#8b5cf6;" x-text="row.reviews"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:600; color:#ef4444;" x-text="row.bugs_fixed"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:700; color:#111827;" x-text="row.deployments"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:13.5px; font-weight:700; color:#f59e0b;" x-text="row.deployment_frequency.toFixed(2)"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:right;">
                                <span style="font-size:12.5px; color:var(--text-secondary);" x-text="row.last_synced"></span>
                            </td>
                            <td style="padding:13px 16px; text-align:center;">
                                <a :href="'/engineering-agent/vcs/' + row.employee_id + '/analysis'"
                                   style="display:inline-flex; align-items:center; gap:5px; padding:5px 14px; background:linear-gradient(135deg,#6366f1,#7c3aed); color:#fff; font-size:12px; font-weight:600; border-radius:7px; text-decoration:none; cursor:pointer; box-shadow:0 2px 6px rgba(99,102,241,0.28); transition:all 0.15s;"
                                   onmouseover="this.style.boxShadow='0 4px 14px rgba(99,102,241,0.45)'; this.style.transform='translateY(-1px)'"
                                   onmouseout="this.style.boxShadow='0 2px 6px rgba(99,102,241,0.28)'; this.style.transform='none'">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="paginated.length === 0">
                        <td colspan="10" style="text-align:center; padding:40px; color:var(--text-secondary); font-size:13px;">
                            No metrics found. Try syncing or adjusting your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="flex items-center justify-between mt-5 pt-4" style="border-top:1px solid #f1f5f9;">
            <div style="font-size:12.5px; color:var(--text-secondary);">
                Showing <span style="font-weight:600; color:var(--text-muted);" x-text="Math.min((currentPage - 1) * perPage + 1, filtered.length)"></span>
                to <span style="font-weight:600; color:var(--text-muted);" x-text="Math.min(currentPage * perPage, filtered.length)"></span>
                of <span style="font-weight:600; color:var(--text-muted);" x-text="filtered.length"></span> entries
            </div>
            <div class="flex items-center gap-1">
                <button @click="currentPage = Math.max(1, currentPage - 1)"
                    :disabled="currentPage === 1"
                    style="padding:5px 12px; font-size:12.5px; font-weight:600; border:1px solid #e5e7eb; border-radius:6px; background:#fff; color:#374151; cursor:pointer; transition:all 0.15s;"
                    :style="currentPage === 1 ? 'opacity:0.4; cursor:not-allowed;' : ''">
                    Previous
                </button>
                <template x-for="p in totalPages" :key="p">
                    <button @click="currentPage = p"
                        :style="currentPage === p ? 'background:#6366f1; color:#fff; border-color:#6366f1;' : ''"
                        style="padding:5px 10px; font-size:12.5px; font-weight:600; border:1px solid #e5e7eb; border-radius:6px; background:#fff; color:#374151; cursor:pointer; transition:all 0.15s;"
                        x-text="p">
                    </button>
                </template>
                <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    style="padding:5px 12px; font-size:12.5px; font-weight:600; border:1px solid #e5e7eb; border-radius:6px; background:#fff; color:#374151; cursor:pointer; transition:all 0.15s;"
                    :style="currentPage === totalPages ? 'opacity:0.4; cursor:not-allowed;' : ''">
                    Next
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
function vcsState() {
    // Raw data from PHP
    @php
        $serializedMetrics = json_encode($metrics->map(function($m) {
            return [
                'id' => $m->id,
                'employee_id' => $m->employee_id,
                'employee_name' => optional($m->employee)->name ?? 'Unknown',
                'git_username' => $m->git_username,
                'provider' => $m->provider,
                'commits' => (int) $m->commits,
                'pull_requests' => (int) $m->pull_requests,
                'reviews' => (int) $m->reviews,
                'bugs_fixed' => (int) $m->bugs_fixed,
                'deployments' => (int) $m->deployments,
                'deployment_frequency' => (float) $m->deployment_frequency,
                'last_synced' => $m->last_synced_at ? $m->last_synced_at->diffForHumans() : 'Never',
            ];
        }));
    @endphp
    const allRows = {!! $serializedMetrics !!};

    return {
        rows: allRows,
        search: '',
        perPage: 25,
        currentPage: 1,
        sortKey: '',
        sortDir: 'asc',

        init() {
            this.$watch('search', () => { this.currentPage = 1; });
            this.$watch('perPage', () => { this.currentPage = 1; });
        },

        get filtered() {
            let r = this.rows;
            const q = this.search.toLowerCase().trim();
            if (q) {
                r = r.filter(row =>
                    row.employee_name.toLowerCase().includes(q) ||
                    row.git_username.toLowerCase().includes(q) ||
                    row.provider.toLowerCase().includes(q)
                );
            }
            if (this.sortKey) {
                r = [...r].sort((a, b) => {
                    let av = a[this.sortKey], bv = b[this.sortKey];
                    if (typeof av === 'string') av = av.toLowerCase();
                    if (typeof bv === 'string') bv = bv.toLowerCase();
                    if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                    if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }
            return r;
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get paginated() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        sortBy(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
            this.currentPage = 1;
        },

        providerBadge(provider) {
            const icons = {
                github: `<span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#111827;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#111827"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                    Github
                </span>`,
                gitlab: `<span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#fc6d26;">
                    <svg width="14" height="14" viewBox="0 0 25 24" fill="#fc6d26"><path d="M24.507 9.5l-.034-.09L21.082.562a.896.896 0 00-1.694.091l-2.29 7.01H7.825L5.535.653a.898.898 0 00-1.694-.09L.451 9.411.416 9.5a6.297 6.297 0 002.09 7.278l.012.01.03.022 5.16 3.867 2.56 1.935 1.554 1.176a1.051 1.051 0 001.268 0l1.555-1.176 2.56-1.935 5.197-3.89.014-.01A6.297 6.297 0 0024.507 9.5z"/></svg>
                    Gitlab
                </span>`,
                bitbucket: `<span style="display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#0052cc;">
                    <svg width="14" height="14" viewBox="0 0 32 32" fill="#0052cc"><path d="M2 5a1 1 0 00-.984 1.17l4.254 24.748a1.37 1.37 0 001.346 1.145h19.084a1 1 0 00.985-.83L30.984 6.17A1 1 0 0030 5H2zm17.692 17.078h-7.382L10.5 13.09h11l-1.808 8.988z"/></svg>
                    Bitbucket
                </span>`,
            };
            return icons[provider] || provider;
        }
    };
}
</script>
@endsection
