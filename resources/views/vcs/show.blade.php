@extends('layouts.app')

@section('content')
<div class="page-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">

    {{-- Page Header / Navigation --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="{{ route('vcs.index') }}" 
               style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #6366f1; text-decoration: none; margin-bottom: 8px; transition: all 0.2s;"
               onmouseover="this.style.transform='translateX(-3px)'"
               onmouseout="this.style.transform='none'">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m7 7l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to VCS Dashboard
            </a>
            <h1 class="page-title" style="font-size: 24px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.03em; margin: 0;">
                VCS Developer Analysis
            </h1>
            <p style="font-size: 13.5px; color: var(--text-muted); margin: 4px 0 0 0;">
                Detailed git metrics & AI analysis report for <strong style="color: #6366f1;">{{ $employee->name }}</strong>
            </p>
        </div>

        {{-- Generate/Refresh Form --}}
        <div>
            <form action="{{ route('vcs.analysis.generate', $employee) }}" method="POST" id="ai-generate-form">
                @csrf
                <button type="submit" onclick="showLoadingState(event)"
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: linear-gradient(135deg, #6366f1, #7c3aed); color: #fff; font-size: 13px; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);">
                    <svg id="ai-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg id="loading-spinner" class="animate-spin" style="display: none; width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="button-text">{{ $report ? 'Refresh AI Evaluation' : 'Generate VCS AI Report' }}</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Success Alert --}}
    @if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; color: #065f46; font-size: 13.5px; font-weight: 500;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Main 2-Column Dashboard Grid --}}
    <div style="display: grid; grid-template-columns: 1fr; gap: 24px; margin-bottom: 24px; align-items: start;">
        
        {{-- Row 1: Profile & AI Overview --}}
        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
            @if(1)
            <div style="display: grid; grid-template-columns: 1fr lg:grid-cols-[1.1fr_1.9fr]; gap: 24px;">
                
                {{-- Column A: Developer Profile Info Card --}}
                <div class="card p-6" style="background: #fff; border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px;">
                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 style="font-size: 17px; font-weight: 800; color: #111827; margin: 0;">{{ $employee->name }}</h3>
                                <p style="font-size: 12.5px; color: #4f46e5; font-weight: 600; margin: 2px 0 0 0;">{{ $employee->role }}</p>
                                <p style="font-size: 11.5px; color: var(--text-muted); margin: 2px 0 0 0;">{{ $employee->department }}</p>
                            </div>
                        </div>

                        {{-- Providers and usernames --}}
                        <div style="margin-bottom: 20px;">
                            <p style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Git Connection Details</p>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                @forelse($metrics['git_usernames'] as $prov => $uname)
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                                        <span style="font-size: 12.5px; font-weight: 600; text-transform: capitalize; color: #374151; display: inline-flex; align-items: center; gap: 6px;">
                                            @if($prov === 'github')
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="#000"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                                            @elseif($prov === 'gitlab')
                                                <svg width="12" height="12" viewBox="0 0 25 24" fill="#fc6d26"><path d="M24.507 9.5l-.034-.09L21.082.562a.896.896 0 00-1.694.091l-2.29 7.01H7.825L5.535.653a.898.898 0 00-1.694-.09L.451 9.411.416 9.5a6.297 6.297 0 002.09 7.278l.012.01.03.022 5.16 3.867 2.56 1.935 1.554 1.176a1.051 1.051 0 001.268 0l1.555-1.176 2.56-1.935 5.197-3.89.014-.01A6.297 6.297 0 0024.507 9.5z"/></svg>
                                            @elseif($prov === 'bitbucket')
                                                <svg width="12" height="12" viewBox="0 0 32 32" fill="#0052cc"><path d="M2 5a1 1 0 00-.984 1.17l4.254 24.748a1.37 1.37 0 001.346 1.145h19.084a1 1 0 00.985-.83L30.984 6.17A1 1 0 0030 5H2zm17.692 17.078h-7.382L10.5 13.09h11l-1.808 8.988z"/></svg>
                                            @endif
                                            {{ ucfirst($prov) }}
                                        </span>
                                        <span style="font-family: monospace; font-size: 12.5px; color: #ec4899; font-weight: 600;">
                                            {{ $uname }}
                                        </span>
                                    </div>
                                @empty
                                    <span style="font-size: 12px; color: var(--text-muted);">No profiles linked.</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Connected repositories --}}
                        <div>
                            <p style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Active Repositories ({{ count($metrics['repository_list']) }})</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @forelse($metrics['repository_list'] as $repo)
                                    <span style="font-size: 11px; font-weight: 600; color: #4338ca; background: #e0e7ff; border: 1px solid #c7d2fe; border-radius: 6px; padding: 4px 8px; font-family: monospace;">
                                        {{ $repo }}
                                    </span>
                                @empty
                                    <span style="font-size: 12px; color: var(--text-muted);">No active repository syncs.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Column B: AI Analysis Report Summary --}}
                <div class="card p-6" style="background: #fff; border: 1px solid rgba(99, 102, 241, 0.15); border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1; display: inline-block;"></span>
                            VCS AI Report Summary
                        </h3>

                        @if($report)
                            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 20px; line-height: 1.6; font-size: 13.5px; color: #374151;">
                                {{ $report->summary }}
                            </div>

                            {{-- Summary Metrics Display --}}
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                                <div style="text-align: center; background: #faf5ff; border: 1px solid #f3e8ff; border-radius: 10px; padding: 10px;">
                                    <div style="font-size: 20px; font-weight: 900; color: #a855f7;">{{ $report->code_quality_score }}<span style="font-size:11px; font-weight:500;">/100</span></div>
                                    <div style="font-size: 11px; font-weight: 700; color: #7e22ce; text-transform: uppercase; margin-top: 4px;">Code Quality</div>
                                </div>
                                <div style="text-align: center; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 10px; padding: 10px;">
                                    <div style="font-size: 20px; font-weight: 900; color: #22c55e;">{{ $report->collaboration_score }}<span style="font-size:11px; font-weight:500;">/100</span></div>
                                    <div style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase; margin-top: 4px;">Collaboration</div>
                                </div>
                                <div style="text-align: center; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px; padding: 10px;">
                                    <div style="font-size: 20px; font-weight: 900; color: #3b82f6;">{{ $report->delivery_score }}<span style="font-size:11px; font-weight:500;">/100</span></div>
                                    <div style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; margin-top: 4px;">Delivery Speed</div>
                                </div>
                            </div>
                        @else
                            <div style="border: 2px dashed rgba(99, 102, 241, 0.2); border-radius: 12px; padding: 40px 20px; text-align: center; background: #fafafc;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0e7ff; display: inline-flex; align-items: center; justify-content: center; color: #6366f1; margin-bottom: 12px;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <h4 style="font-size: 14.5px; font-weight: 700; color: #374151; margin: 0 0 6px 0;">No AI Analysis Found</h4>
                                <p style="font-size: 12px; color: var(--text-muted); max-width: 280px; margin: 0 auto 16px auto;">
                                    Generate an AI analysis evaluation to audit code quality, delivery speed, and code review trends.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            @endif
        </div>

        {{-- Row 2: Comprehensive VCS Metrics Grid --}}
        <div>
            <h2 style="font-size: 15px; font-weight: 700; color: #111827; margin: 12px 0 16px 0; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Detailed Version Control Activity Metrics
            </h2>

            <div style="display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 16px; width: 100%;" class="sm:grid-cols-2 lg:grid-cols-3">
                
                {{-- Code Activity --}}
                <div class="card p-5" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #4b5563; margin-top: 0; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #6366f1;"></span>
                        Code Activity
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Total Commits</span>
                            <strong style="color: #059669; font-size: 14px;">{{ $metrics['total_commits'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Weekly Commits</span>
                            <strong style="color: #111827;">{{ $metrics['weekly_commits'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Monthly Commits</span>
                            <strong style="color: #111827;">{{ $metrics['monthly_commits'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                            <span style="color: var(--text-muted);">Files Changed</span>
                            <strong style="color: #4f46e5;">{{ $metrics['files_changed'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Lines Added</span>
                            <strong style="color: #16a34a;">+{{ number_format($metrics['lines_added']) }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Lines Deleted</span>
                            <strong style="color: #dc2626;">-{{ number_format($metrics['lines_deleted']) }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Pull Requests --}}
                <div class="card p-5" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #4b5563; margin-top: 0; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                        Pull Request Analysis
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">PRs Created</span>
                            <strong style="color: #2563eb; font-size: 14px;">{{ $metrics['prs_created'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">PRs Merged</span>
                            <strong style="color: #16a34a;">{{ $metrics['prs_merged'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">PRs Closed</span>
                            <strong style="color: #9ca3af;">{{ $metrics['prs_closed'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                            <span style="color: var(--text-muted);">Avg Merge Time</span>
                            <strong style="color: #111827;">{{ $metrics['avg_pr_merge_time'] }} hrs</strong>
                        </div>
                    </div>
                </div>

                {{-- Code Review --}}
                <div class="card p-5" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #4b5563; margin-top: 0; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #8b5cf6;"></span>
                        Code Review Activity
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Reviews Given</span>
                            <strong style="color: #7c3aed; font-size: 14px;">{{ $metrics['reviews_given'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Reviews Received</span>
                            <strong style="color: #111827;">{{ $metrics['reviews_received'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Approvals Granted</span>
                            <strong style="color: #111827;">{{ $metrics['approval_count'] }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Bug Analysis --}}
                <div class="card p-5" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #4b5563; margin-top: 0; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #ec4899;"></span>
                        Bug Analysis
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Bugs Reported</span>
                            <strong style="color: #111827;">{{ $metrics['bugs_reported'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Bugs Fixed</span>
                            <strong style="color: #16a34a;">{{ $metrics['bugs_fixed'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                            <span style="color: var(--text-muted);">Open Bugs Pending</span>
                            <strong style="color: #dc2626; font-size: 14px;">{{ $metrics['open_bugs'] }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Deployment --}}
                <div class="card p-5" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #4b5563; margin-top: 0; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #f97316;"></span>
                        Deployments
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Deployment Count</span>
                            <strong style="color: #ea580c; font-size: 14px;">{{ $metrics['deployment_count'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span style="color: var(--text-muted);">Deployment Frequency</span>
                            <strong style="color: #111827;">{{ $metrics['deployment_frequency'] }} / week</strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Row 3: Ollama Detailed AI Analysis Sections --}}
        @if($report)
        <div>
            <h2 style="font-size: 15px; font-weight: 700; color: #111827; margin: 12px 0 16px 0; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" fill="none" stroke="#6366f1" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Ollama AI Evaluation Reports
            </h2>

            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;" class="lg:grid-cols-2">
                
                {{-- Risk Analysis Card --}}
                <div class="card p-6" style="background: #fff; border: 1px solid #fecaca; border-radius: 16px; box-shadow: 0 4px 18px rgba(239, 68, 68, 0.02);">
                    <h3 style="font-size: 14.5px; font-weight: 800; color: #991b1b; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Risk Detection & Bottlenecks
                    </h3>
                    <div style="font-size: 13.5px; color: #7f1d1d; line-height: 1.6; background: #fff5f5; border-radius: 10px; padding: 14px; border: 1px solid #fee2e2;">
                        {{ $report->risk_analysis }}
                    </div>
                </div>

                {{-- Recommendations Card --}}
                <div class="card p-6" style="background: #fff; border: 1px solid #bdedde; border-radius: 16px; box-shadow: 0 4px 18px rgba(16, 185, 129, 0.02);">
                    <h3 style="font-size: 14.5px; font-weight: 800; color: #065f46; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Improvement Suggestions
                    </h3>
                    <div style="font-size: 13.5px; color: #064e3b; line-height: 1.6; background: #f0fdf4; border-radius: 10px; padding: 14px; border: 1px solid #dcfce7;">
                        {!! nl2br(e($report->recommendations)) !!}
                    </div>
                </div>

            </div>
        </div>
        @endif

    </div>

</div>

<script>
    function showLoadingState(event) {
        const form = document.getElementById('ai-generate-form');
        const icon = document.getElementById('ai-icon');
        const spinner = document.getElementById('loading-spinner');
        const btnText = document.getElementById('button-text');
        
        icon.style.display = 'none';
        spinner.style.display = 'inline-block';
        btnText.innerText = 'Evaluating VCS Metrics with Ollama...';
        
        // Disable button to prevent double submit
        event.currentTarget.disabled = true;
        
        form.submit();
    }
</script>
@endsection
