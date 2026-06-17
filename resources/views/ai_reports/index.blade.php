@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:1200px;">

    {{-- Header --}}
    <div class="section-header" style="margin-bottom:24px;">
        <div>
            <h1 class="page-title">AI Reports</h1>
            <p class="page-subtitle">Automated performance insights for your engineering team.</p>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert-success" style="margin-bottom:18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="table-container">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Employee</th>
                        <th>Generated</th>
                        <th>Summary</th>
                        <th style="text-align:center;">Strengths</th>
                        <th style="text-align:center;">Areas</th>
                        <th style="text-align:center;">Tips</th>
                        <th style="text-align:right; padding-right:24px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td style="padding-left:24px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar avatar-sm">
                                        {{ strtoupper(substr($report->employee->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p style="font-size:13.5px; font-weight:700; color:var(--text-primary); white-space:nowrap;">{{ $report->employee->name }}</p>
                                        <p style="font-size:12px; color:var(--text-muted);">{{ $report->employee->role }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:13px; color:var(--text-secondary);">{{ $report->created_at->format('M d, Y') }}</span>
                                <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $report->created_at->format('H:i') }}</p>
                            </td>
                            <td>
                                <p style="color:var(--text-secondary); font-size:13px; max-width:260px; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $report->summary }}</p>
                            </td>
                            <td style="text-align:center;">
                                <p style="font-size:18px; font-weight:700; color:#34d399;">{{ count($report->strengths) }}</p>
                                <p style="font-size:11px; color:var(--text-muted);">Strengths</p>
                            </td>
                            <td style="text-align:center;">
                                <p style="font-size:18px; font-weight:700; color:#fbbf24;">{{ count($report->weaknesses) }}</p>
                                <p style="font-size:11px; color:var(--text-muted);">Areas</p>
                            </td>
                            <td style="text-align:center;">
                                <p style="font-size:18px; font-weight:700; color:#60a5fa;">{{ count($report->suggestions) }}</p>
                                <p style="font-size:11px; color:var(--text-muted);">Tips</p>
                            </td>
                            <td style="text-align:right; padding-right:24px;">
                                <a href="{{ route('ai.report.show', $report->employee) }}" class="btn-ghost" style="font-size:12px; padding:5px 10px;">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:60px 24px;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                    <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15); display:flex; align-items:center; justify-content:center;">
                                        <svg width="22" height="22" fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No AI reports yet</p>
                                        <p style="font-size:12.5px; color:#334155; margin-top:4px;">Open an employee profile and click "Generate AI Report" to create one.</p>
                                    </div>
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
