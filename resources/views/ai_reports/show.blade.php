@extends('layouts.app')

@section('content')
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="bg-gradient-to-br from-indigo-800 via-purple-800 to-slate-900 py-20 px-6 mb-12 rounded-3xl text-center shadow-2xl">
            <h1 class="text-6xl font-extrabold text-white tracking-tight mb-4">AI Analysis Report</h1>
            <p class="text-indigo-100 text-lg max-w-2xl mx-auto">Deep team intelligence for <span class="text-indigo-300 font-semibold">{{ $employee->name }}</span> based on task and score metrics.</p>
            <div class="flex gap-3 flex-wrap justify-center mt-8">
                <a href="{{ route('ai.report.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 border border-slate-700/50 hover:border-slate-600/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    All Reports
                </a>
                <form action="{{ route('ai.report.generate', $employee) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 px-6 py-3 text-sm font-semibold text-white transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Generate Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div
                class="rounded-2xl bg-gradient-to-r from-emerald-500/20 to-emerald-600/20 border border-emerald-500/40 backdrop-blur-sm p-5 text-sm text-emerald-200 flex items-center gap-3 shadow-lg">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr]">
            <!-- Left: Employee Profile & Stats -->
            <section class="glass border border-slate-700/50 rounded-3xl overflow-hidden shadow-2xl">
                <!-- Profile Header -->
                <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 border-b border-slate-700/30 px-8 py-8">
                    <div class="flex items-center gap-5">
                        <div
                            class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-3xl shadow-lg">
                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">{{ $employee->name }}</h3>
                            <p class="text-indigo-400 font-semibold text-sm mt-1">{{ $employee->role }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ $employee->department }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="px-8 py-6 space-y-4 border-b border-slate-700/20">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-900 text-sm">📧 Email</span>
                        <span class="text-slate-900 font-semibold text-sm">{{ $employee->email }}</span>
                    </div>
                </div>

                <!-- Task Statistics -->
                <div class="px-8 py-8">
                    <h3 class="text-lg font-bold text-white mb-6">📊 Task Statistics</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="rounded-2xl bg-gradient-to-br from-blue-500/20 to-blue-600/20 border border-blue-500/30 p-5 hover:border-blue-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-blue-300">Tasks Assigned</p>
                            <p class="mt-3 text-4xl font-black text-blue-400">{{ $tasksAssigned }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $individualTasksCount }} Individual / {{ $teamTasksCount }} Team</p>
                        </div>
                        <div
                            class="rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 border border-emerald-500/30 p-5 hover:border-emerald-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-emerald-300">Completed</p>
                            <p class="mt-3 text-4xl font-black text-emerald-400">{{ $tasksCompleted }}</p>
                            <p class="mt-1 text-xs text-slate-400">tasks</p>
                        </div>
                        <div
                            class="rounded-2xl bg-gradient-to-br from-purple-500/20 to-purple-600/20 border border-purple-500/30 p-5 hover:border-purple-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-purple-300">Completion</p>
                            <p class="mt-3 text-4xl font-black text-purple-400">{{ number_format($completionRate, 1) }}<span
                                    class="text-2xl">%</span></p>
                            <p class="mt-1 text-xs text-slate-400">rate</p>
                        </div>
                        <div
                            class="rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/20 border border-amber-500/30 p-5 hover:border-amber-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-amber-300">Productivity</p>
                            <p class="mt-3 text-4xl font-black text-amber-400">{{ number_format($productivityScore, 1) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">score</p>
                        </div>
                        <div
                            class="rounded-2xl bg-gradient-to-br from-teal-500/20 to-teal-600/20 border border-teal-500/30 p-5 hover:border-teal-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-teal-300">Team Contribution</p>
                            <p class="mt-3 text-4xl font-black text-teal-400">{{ number_format($teamContribution, 1) }}</p>
                            <p class="mt-1 text-xs text-slate-400">score</p>
                        </div>
                        <div
                            class="rounded-2xl bg-gradient-to-br from-rose-500/20 to-rose-600/20 border border-rose-500/30 p-5 hover:border-rose-500/50 transition-all duration-200">
                            <p class="text-xs uppercase tracking-[0.15em] font-semibold text-rose-300">Leadership</p>
                            <p class="mt-3 text-4xl font-black text-rose-400">{{ number_format($leadershipScore, 1) }}</p>
                            <p class="mt-1 text-xs text-slate-400">score</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right: AI Analysis Report -->
            <section class="glass border border-slate-700/50 rounded-3xl overflow-hidden shadow-2xl">
                @if($report)
                    <!-- Report Header -->
                    <div class="bg-gradient-to-r from-indigo-600/20 to-indigo-700/20 border-b border-slate-700/30 px-8 py-6">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            AI Analysis
                        </h2>
                    </div>

                    <!-- Report Content -->
                    <div class="p-8 space-y-6">
                        <!-- Summary -->
                        <div
                            class="rounded-2xl bg-slate-950/60 border border-slate-700/30 p-6 hover:border-slate-700/50 transition-all duration-200">
                            <h3
                                class="text-sm uppercase tracking-[0.15em] font-bold text-slate-300 mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                Summary
                            </h3>
                            <p class="text-slate-900 leading-relaxed text-base">{{ $report->summary }}</p>
                        </div>

                        <!-- Strengths -->
                        <div
                            class="rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 border border-emerald-500/30 p-6 hover:border-emerald-500/50 transition-all duration-200">
                            <h3
                                class="text-sm uppercase tracking-[0.15em] font-bold text-emerald-300 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Strengths
                            </h3>
                            <ul class="space-y-2">
                                @foreach($report->strengths as $item)
                                    <li class="flex gap-3 text-slate-900 text-sm">
                                        <span class="text-emerald-400 font-bold flex-shrink-0 mt-0.5">✓</span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Weaknesses -->
                        <div
                            class="rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/10 border border-amber-500/30 p-6 hover:border-amber-500/50 transition-all duration-200">
                            <h3
                                class="text-sm uppercase tracking-[0.15em] font-bold text-amber-300 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Areas for Growth
                            </h3>
                            <ul class="space-y-2">
                                @foreach($report->weaknesses as $item)
                                    <li class="flex gap-3 text-slate-900 text-sm">
                                        <span class="text-amber-400 font-bold flex-shrink-0 mt-0.5">•</span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Suggestions -->
                        <div
                            class="rounded-2xl bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/30 p-6 hover:border-blue-500/50 transition-all duration-200">
                            <h3
                                class="text-sm uppercase tracking-[0.15em] font-bold text-blue-300 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5.36-5.36l.707-.707M5.39 12.364L4.683 12M3 12a9 9 0 1118 0 9 9 0 01-18 0z">
                                    </path>
                                </svg>
                                Recommendations
                            </h3>
                            <ul class="space-y-2">
                                @foreach($report->suggestions as $item)
                                    <li class="flex gap-3 text-slate-900 text-sm">
                                        <span class="text-blue-400 font-bold flex-shrink-0 mt-0.5">→</span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <!-- No Report State -->
                    <div class="px-8 py-16 flex flex-col items-center justify-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-slate-800/50 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <p class="text-slate-300 font-semibold text-center">No AI Report Generated</p>
                        <p class="text-slate-500 text-sm text-center max-w-xs">Generate an AI-powered analysis by clicking the
                            button above to get personalized insights and recommendations.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection