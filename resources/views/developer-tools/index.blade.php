@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width: 1600px;" x-data="devToolsState()">

    <!-- Header Section -->
    <div class="section-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title gradient-text text-3xl font-extrabold tracking-tight">Developer Tools & API Sandbox</h1>
            <p class="page-subtitle text-gray-400">Simulate Workdesk sync, manage developer keys, and sandbox API data in real-time.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                Workdesk Integration: Simulated Ready
            </span>
        </div>
    </div>

    <!-- Success & Error Alerts -->
    @if (session('success'))
        <div class="alert-success" style="margin-bottom: 20px;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert-error" style="margin-bottom: 20px;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            <div>
                <span class="font-semibold text-red-200">Validation Errors:</span>
                <ul class="list-disc pl-5 mt-1 text-sm text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- 3-Column Layout: (App Sidebar is left, Content is center, API Catalog is right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- CENTER PANEL (lg:col-span-8) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            
            <!-- 1. Access Tokens Purple Card (As shown in image) -->
            <div class="rounded-2xl p-6 shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">Access Tokens</h2>
                        <div class="flex items-center gap-1.5 text-purple-200/90 text-xs mt-1.5 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Create & manage your api keys
                        </div>
                    </div>
                    <div>
                        <button @click="generateToken()" class="px-5 py-2 rounded-lg text-xs font-semibold bg-[#10b981] hover:bg-[#059669] text-white transition-all shadow-md active:scale-95">
                            Generate API key
                        </button>
                    </div>
                </div>

                <!-- Tokens Table -->
                <div class="mt-6 bg-white rounded-xl overflow-hidden shadow-inner">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-150 text-gray-500 font-bold">
                                    <th class="p-3.5 pl-5">Token</th>
                                    <th class="p-3.5">Created On</th>
                                    <th class="p-3.5 pr-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <template x-for="(tok, idx) in tokens" :key="idx">
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="p-3.5 pl-5 font-mono text-xs flex items-center gap-2">
                                            <span x-text="tok.reveal ? tok.key : '****************************************'" class="select-all"></span>
                                            <button @click="tok.reveal = !tok.reveal" class="text-gray-400 hover:text-gray-600 transition" title="Toggle Visibility">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <button @click="copyText(tok.key)" class="text-gray-400 hover:text-gray-600 transition" title="Copy Key">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </td>
                                        <td class="p-3.5">
                                            <span class="bg-gray-100 text-gray-600 font-semibold px-2.5 py-1 rounded text-[10.5px] font-mono" x-text="tok.created_at"></span>
                                        </td>
                                        <td class="p-3.5 pr-5 text-right">
                                            <button @click="deleteToken(idx)" class="text-gray-400 hover:text-red-600 transition" title="Delete Key">
                                                <svg class="w-4.5 h-4.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="tokens.length === 0">
                                    <td colspan="3" class="text-center py-6 text-gray-400 italic">No access tokens generated. Click the button above to generate one.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabs: Tester & Sandbox Selector -->
            <div class="flex gap-2 border-b border-gray-800 pb-px">
                <button @click="centerTab = 'tester'" 
                        :class="centerTab === 'tester' ? 'border-indigo-500 text-black font-semibold' : 'border-transparent text-gray-900 hover:text-gray-300'"
                        class="px-4 py-2 border-b-2 text-xs transition-all duration-150 uppercase tracking-wider">
                    API Tester / Runner
                </button>
                <button @click="centerTab = 'sandbox'" 
                        :class="centerTab === 'sandbox' ? 'border-indigo-500 text-black font-semibold' : 'border-transparent text-gray-900 hover:text-gray-300'"
                        class="px-4 py-2 border-b-2 text-xs transition-all duration-150 uppercase tracking-wider">
                    API Sandbox / MySQL Writer
                </button>
            </div>

            <!-- Tab Content: API Tester -->
            <div x-show="centerTab === 'tester'" class="flex flex-col gap-6">
                <!-- Request Builder Card -->
                <div class="card p-6" style="background: var(--bg-surface);">
                    <div class="flex items-center gap-3 border-b border-gray-800 pb-3 mb-5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 17l6-6-6-6M12 19h8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300">API Request Builder</h3>
                            <p class="text-xs text-gray-500 mt-1">Configure and execute HTTP requests against simulated endpoints.</p>
                        </div>
                    </div>

                    <form @submit.prevent="sendTestRequest()" class="space-y-4">
                        <div class="flex flex-col md:flex-row gap-2">
                            <div class="md:w-32 flex-shrink-0">
                                <label class="form-label">HTTP Method</label>
                                <select x-model="testerMethod" class="form-input h-[45px] pr-8 bg-black text-center font-bold text-white">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="form-label">Endpoint Route</label>
                                <div class="flex items-stretch">
                                    <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-800 bg-[#07070b] text-gray-500 text-xs font-mono select-none" x-text="baseUrl"></span>
                                    <input type="text" x-model="testerEndpoint" class="form-input rounded-l-none font-mono text-xs w-full">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">HTTP Request Headers (JSON)</label>
                                <textarea x-model="testerHeaders" rows="4" class="form-input font-mono text-xs text-indigo-300" placeholder='{ "Accept": "application/json" }'></textarea>
                            </div>
                            <div>
                                <label class="form-label">Request Payload / Body (JSON)</label>
                                <textarea x-model="testerBody" rows="4" :disabled="testerMethod === 'GET'" class="form-input font-mono text-xs text-emerald-300 disabled:opacity-40" placeholder='{ "name": "Ada Lovelace" }'></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-gray-800">
                            <button type="button" @click="resetTester()" class="btn-secondary text-xs">Reset Form</button>
                            <button type="submit" :disabled="testerLoading" class="btn-primary px-6 flex gap-2">
                                <svg x-show="testerLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                <span x-text="testerLoading ? 'Sending Request...' : 'Send API Request'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Response Panel -->
                <div x-show="testerResponse !== null" class="card p-6 animate-fade-up" style="background: var(--bg-surface);">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3 mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Response Panel</h3>
                        <div class="flex items-center gap-3">
                            <span :class="testerResponseStatusClass()" 
                                  class="px-2.5 py-1 rounded text-xs font-bold border tracking-wider" 
                                  x-text="'Status: ' + testerResponse.status"></span>
                            <span class="px-2.5 py-1 rounded text-xs font-bold bg-[#07070b] border border-gray-800 text-gray-400 tracking-wider" 
                                  x-text="'Time: ' + testerResponse.time_ms + ' ms'"></span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <!-- Headers Accordion -->
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center justify-between w-full p-2 bg-[#07070b] border border-gray-800 rounded-lg text-xs font-semibold text-gray-400 hover:text-white transition">
                                <span>Response Headers</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </button>
                            <div x-show="open" class="mt-2 p-3 bg-black border border-gray-800 rounded-lg max-h-40 overflow-y-auto">
                                <pre class="text-[11px] font-mono text-gray-500 select-all" x-text="JSON.stringify(testerResponse.headers, null, 2)"></pre>
                            </div>
                        </div>
                        <!-- Response Body -->
                        <div>
                            <pre class="bg-[#07070b] border border-gray-800/80 rounded-xl p-4 overflow-x-auto text-xs font-mono text-emerald-400 select-all max-h-80" 
                                 x-text="JSON.stringify(testerResponse.body, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Sandbox Forms -->
            <div x-show="centerTab === 'sandbox'" class="flex flex-col gap-6" x-data="{ formType: 'employee' }">
                <div class="card p-5" style="background: var(--bg-surface);">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3 mb-5">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300 font-semibold">MySQL Sandbox Seeding</h3>
                            <p class="text-xs text-gray-500 mt-1">Populate records directly into MySQL and auto-recalculate scores.</p>
                        </div>
                        <div class="flex gap-1.5">
                            <button @click="formType = 'employee'" :class="formType === 'employee' ? 'bg-indigo-500/10 border-indigo-500/35 text-indigo-400' : 'bg-transparent text-gray-500 border-transparent'" class="px-2.5 py-1 text-xs rounded border font-semibold">Employee</button>
                            <button @click="formType = 'task'" :class="formType === 'task' ? 'bg-indigo-500/10 border-indigo-500/35 text-indigo-400' : 'bg-transparent text-gray-500 border-transparent'" class="px-2.5 py-1 text-xs rounded border font-semibold">Task</button>
                            <button @click="formType = 'hours'" :class="formType === 'hours' ? 'bg-indigo-500/10 border-indigo-500/35 text-indigo-400' : 'bg-transparent text-gray-500 border-transparent'" class="px-2.5 py-1 text-xs rounded border font-semibold">Working Hours</button>
                            <button @click="formType = 'attendance'" :class="formType === 'attendance' ? 'bg-indigo-500/10 border-indigo-500/35 text-indigo-400' : 'bg-transparent text-gray-500 border-transparent'" class="px-2.5 py-1 text-xs rounded border font-semibold">Attendance</button>
                        </div>
                    </div>

                    <!-- Sandbox Forms -->
                    <div>
                        <!-- Employee Form -->
                        <form x-show="formType === 'employee'" method="POST" action="{{ route('developer.tools.employee') }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" required placeholder="e.g. Alan Turing" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" required placeholder="e.g. turing@simpel.task" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" required placeholder="e.g. Backend Platform" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Role</label>
                                    <input type="text" name="role" required placeholder="e.g. Senior Security Architect" class="form-input">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">GitHub Username</label>
                                <input type="text" name="github_username" required placeholder="e.g. turing-dev" class="form-input">
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm">Create Employee & Compute Metrics</button>
                            </div>
                        </form>

                        <!-- Task Form -->
                        <form x-show="formType === 'task'" method="POST" action="{{ route('developer.tools.task') }}" class="space-y-4" x-data="{ taskStatus: 'Pending' }">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Assignee (Employee)</label>
                                    <select name="employee_id" required class="form-input h-[45px] pr-8 bg-black">
                                        <option value="">Select Assignee...</option>
                                        @foreach($employees as $e)
                                            <option value="{{ $e->id }}">#{{ $e->id }} — {{ $e->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Task Status</label>
                                    <select name="status" x-model="taskStatus" required class="form-input h-[45px] pr-8 bg-black">
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Assigned Date</label>
                                    <input type="date" name="assigned_date" required value="{{ date('Y-m-d') }}" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Completed Date</label>
                                    <input type="date" name="completed_date" :required="taskStatus === 'Completed'" :disabled="taskStatus !== 'Completed'" class="form-input disabled:opacity-40">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Task Title</label>
                                <input type="text" name="title" required placeholder="e.g. Implement OIDC Client Flow" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Task Description</label>
                                <textarea name="description" rows="2" placeholder="Describe the task parameters..." class="form-input"></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Estimated Hours</label>
                                    <input type="number" name="estimated_hours" required step="0.5" placeholder="8.0" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Actual Hours</label>
                                    <input type="number" name="actual_hours" :required="taskStatus === 'Completed'" :disabled="taskStatus !== 'Completed'" step="0.5" placeholder="7.5" class="form-input disabled:opacity-40">
                                </div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm">Save Task & Auto-Recalculate Scores</button>
                            </div>
                        </form>

                        <!-- Working Hours Form -->
                        <form x-show="formType === 'hours'" method="POST" action="{{ route('developer.tools.working_hours') }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Engineer</label>
                                    <select name="employee_id" required class="form-input h-[45px] pr-8 bg-black">
                                        <option value="">Select Employee...</option>
                                        @foreach($employees as $e)
                                            <option value="{{ $e->id }}">#{{ $e->id }} — {{ $e->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Hours Worked</label>
                                    <input type="number" name="hours_worked" required step="0.5" min="0.0" max="24.0" placeholder="8.0" class="form-input">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Work Date</label>
                                <input type="date" name="work_date" required value="{{ date('Y-m-d') }}" class="form-input">
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm">Log Working Hours & Update Metrics</button>
                            </div>
                        </form>

                        <!-- Attendance Form -->
                        <form x-show="formType === 'attendance'" method="POST" action="{{ route('developer.tools.attendance') }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Engineer</label>
                                    <select name="employee_id" required class="form-input h-[45px] pr-8 bg-black">
                                        <option value="">Select Employee...</option>
                                        @foreach($employees as $e)
                                            <option value="{{ $e->id }}">#{{ $e->id }} — {{ $e->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Attendance Status</label>
                                    <select name="status" required class="form-input h-[45px] pr-8 bg-black">
                                        <option value="Present">Present</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Late">Late</option>
                                        <option value="Leave">Leave</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Date</label>
                                <input type="date" name="attendance_date" required value="{{ date('Y-m-d') }}" class="form-input">
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn-primary w-full justify-center py-2.5 text-sm">Record Attendance & Update Metrics</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sandbox Recent Submissions -->
                <div class="card p-5" style="background: var(--bg-surface);">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Latest DB Sandbox Logs</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Logged Hours</span>
                            <div class="table-container text-xs">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Engineer</th>
                                            <th>Date</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($workingHours as $wh)
                                            <tr>
                                                <td class="font-semibold text-white">{{ $wh->employee->name }}</td>
                                                <td class="text-gray-400 font-mono">{{ $wh->work_date->format('Y-m-d') }}</td>
                                                <td><span class="dept-tag">{{ $wh->hours_worked }}h</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-3 text-gray-600">No working hours logged</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-2">Attendance</span>
                            <div class="table-container text-xs">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Engineer</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attendances as $att)
                                            <tr>
                                                <td class="font-semibold text-white">{{ $att->employee->name }}</td>
                                                <td class="text-gray-400 font-mono">{{ $att->attendance_date->format('Y-m-d') }}</td>
                                                <td>
                                                    <span class="badge {{ $att->status === 'Present' ? 'badge-completed' : ($att->status === 'Absent' ? 'badge-pending' : 'badge-in-progress') }}">
                                                        {{ $att->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-3 text-gray-600">No attendance records</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT DOCS / ENDPOINTS SIDEBAR PANEL (lg:col-span-4) -->
        <div class="lg:col-span-4 flex flex-col border border-gray-800 rounded-2xl overflow-hidden shadow-2xl" style="background: #000000; border-color: rgba(255,255,255,0.06);">
            
            <!-- Language Selector Tabs at top of Right Panel (as shown in image) -->
            <div class="flex bg-[#0f0f18] border-b border-gray-800/80 overflow-x-auto text-[11px] font-bold tracking-wider">
                <button @click="activeLang = 'curl'" :class="activeLang === 'curl' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">CURL</button>
                <button @click="activeLang = 'php'" :class="activeLang === 'php' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">PHP</button>
                <button @click="activeLang = 'node'" :class="activeLang === 'node' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">NODEJS</button>
                <button @click="activeLang = 'python'" :class="activeLang === 'python' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">PYTHON</button>
                <button @click="activeLang = 'java'" :class="activeLang === 'java' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">JAVA</button>
                <button @click="activeLang = 'ruby'" :class="activeLang === 'ruby' ? 'text-white border-b-2 border-white bg-white/[0.03]' : 'text-gray-500 hover:text-gray-300'" class="flex-1 py-3 px-2 text-center uppercase transition">RUBY</button>
            </div>

            <!-- Grouped API Endpoints List -->
            <div class="p-5 flex flex-col gap-5 max-h-[500px] overflow-y-auto">
                
                <!-- Category 1: Employees -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Employees</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-for="ep in endpoints.filter(e => e.category === 'Employees')" :key="ep.id">
                            <button @click="selectEndpoint(ep)" 
                                    class="w-full flex items-center bg-white rounded-lg p-2.5 text-left border border-gray-150 transition-all hover:scale-[1.01] hover:shadow-md shadow-sm">
                                <span :class="ep.methodClass" 
                                      class="text-[9px] font-extrabold px-2 py-0.5 rounded mr-3 tracking-wider text-white" x-text="ep.method"></span>
                                <span class="text-xs font-semibold text-black" x-text="ep.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Category 2: Tasks -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Tasks</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-for="ep in endpoints.filter(e => e.category === 'Tasks')" :key="ep.id">
                            <button @click="selectEndpoint(ep)" 
                                    class="w-full flex items-center bg-white rounded-lg p-2.5 text-left border border-gray-150 transition-all hover:scale-[1.01] hover:shadow-md shadow-sm">
                                <span :class="ep.methodClass" 
                                      class="text-[9px] font-extrabold px-2 py-0.5 rounded mr-3 tracking-wider text-white" x-text="ep.method"></span>
                                <span class="text-xs font-semibold text-black" x-text="ep.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Category 3: Working Hours -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Working Hours</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-for="ep in endpoints.filter(e => e.category === 'Working Hours')" :key="ep.id">
                            <button @click="selectEndpoint(ep)" 
                                    class="w-full flex items-center bg-white rounded-lg p-2.5 text-left border border-gray-150 transition-all hover:scale-[1.01] hover:shadow-md shadow-sm">
                                <span :class="ep.methodClass" 
                                      class="text-[9px] font-extrabold px-2 py-0.5 rounded mr-3 tracking-wider text-white" x-text="ep.method"></span>
                                <span class="text-xs font-semibold text-black" x-text="ep.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Category 4: Attendance -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Attendance</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-for="ep in endpoints.filter(e => e.category === 'Attendance')" :key="ep.id">
                            <button @click="selectEndpoint(ep)" 
                                    class="w-full flex items-center bg-white rounded-lg p-2.5 text-left border border-gray-150 transition-all hover:scale-[1.01] hover:shadow-md shadow-sm">
                                <span :class="ep.methodClass" 
                                      class="text-[9px] font-extrabold px-2 py-0.5 rounded mr-3 tracking-wider text-white" x-text="ep.method"></span>
                                <span class="text-xs font-semibold text-black" x-text="ep.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Category 5: Metrics -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Metrics</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-for="ep in endpoints.filter(e => e.category === 'Metrics')" :key="ep.id">
                            <button @click="selectEndpoint(ep)" 
                                    class="w-full flex items-center bg-white rounded-lg p-2.5 text-left border border-gray-150 transition-all hover:scale-[1.01] hover:shadow-md shadow-sm">
                                <span :class="ep.methodClass" 
                                      class="text-[9px] font-extrabold px-2 py-0.5 rounded mr-3 tracking-wider text-white" x-text="ep.method"></span>
                                <span class="text-xs font-semibold text-black" x-text="ep.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Active Endpoint Details Drawer (Code snippet + parameters details) at bottom of Right Panel -->
            <div class="p-5 border-t border-gray-800 bg-[#07070b]">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="'Selected API: ' + selectedEndpoint().name"></span>
                    <span class="text-[10px] font-mono text-indigo-400" x-text="selectedEndpoint().endpoint"></span>
                </div>
                
                <!-- Code Snippet view -->
                <div class="relative group">
                    <pre class="bg-black/90 border border-gray-800 rounded-xl p-3 overflow-x-auto text-[11px] font-mono text-gray-300 max-h-40" x-text="getCodeSnippet(activeLang)"></pre>
                    <button @click="copyText(getCodeSnippet(activeLang))" class="absolute top-2.5 right-2.5 opacity-0 group-hover:opacity-100 transition-opacity bg-indigo-500 hover:bg-indigo-600 text-white p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>

                <!-- Parameters Summary -->
                <div class="mt-3" x-show="selectedEndpoint().params.length > 0">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Required Parameters</span>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="p in selectedEndpoint().params" :key="p.name">
                            <span class="bg-[#1e1b4b] border border-[#312e81] text-indigo-300 font-mono text-[9px] px-2 py-0.5 rounded" 
                                  x-text="p.name + ' (' + p.type + ')'"></span>
                        </template>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function devToolsState() {
        return {
            centerTab: 'tester',
            baseUrl: "{{ url('/') }}",
            activeLang: 'curl',
            selectedEndpointId: 1,

            // LocalStorage API Token store
            tokens: [],
            
            // API Tester form parameters
            testerMethod: 'GET',
            testerEndpoint: '/api/developer/employees',
            testerHeaders: '{\n  "Accept": "application/json"\n}',
            testerBody: '{\n  "name": "Ada Lovelace",\n  "email": "ada@simpel.task",\n  "department": "Platform Core",\n  "role": "Lead Architect",\n  "github_username": "ada-lovelace"\n}',
            testerLoading: false,
            testerResponse: null,

            // Load generated tokens from localStorage on init
            init() {
                const savedTokens = localStorage.getItem('developer_access_tokens');
                if (savedTokens) {
                    try {
                        this.tokens = JSON.parse(savedTokens);
                    } catch(e) {
                        this.tokens = [];
                    }
                }
            },

            // Create simulator token key
            generateToken() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let randStr = '';
                for (let i = 0; i < 32; i++) {
                    randStr += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                const tokenKey = 'sk_live_' + randStr;
                const dateStr = new Date().toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(/\//g, '-').replace(',', '');
                
                this.tokens.push({
                    key: tokenKey,
                    reveal: false,
                    created_at: dateStr
                });

                localStorage.setItem('developer_access_tokens', JSON.stringify(this.tokens));
            },

            // Delete API token key
            deleteToken(idx) {
                if(confirm('Delete this developer access token key?')) {
                    this.tokens.splice(idx, 1);
                    localStorage.setItem('developer_access_tokens', JSON.stringify(this.tokens));
                }
            },

            endpoints: [
                {
                    id: 1,
                    category: 'Employees',
                    name: 'Get Employees List',
                    method: 'GET',
                    methodClass: 'bg-emerald-500',
                    endpoint: '/api/developer/employees',
                    description: 'Retrieves active employee list mapped from Laravel database.',
                    params: [],
                    headers: '{\n  "Accept": "application/json"\n}',
                    body: '',
                    responseSchema: '[\n  {\n    "id": 1,\n    "name": "Linus Torvalds",\n    "email": "linus@simpel.task",\n    "department": "Kernel",\n    "role": "Lead Maintainer",\n    "github_username": "torvalds",\n    "status": "active",\n    "created_at": "2026-06-11T05:22:16Z"\n  }\n]',
                },
                {
                    id: 2,
                    category: 'Employees',
                    name: 'Register Employee',
                    method: 'POST',
                    methodClass: 'bg-teal-600',
                    endpoint: '/api/developer/employees',
                    description: 'Registers a new employee into SQL database and updates initial scores.',
                    params: [
                        { name: 'name', type: 'String', required: true, description: 'Full name of developer' },
                        { name: 'email', type: 'String', required: true, description: 'Unique email address' },
                        { name: 'department', type: 'String', required: true, description: 'Sub-team or department name' },
                        { name: 'role', type: 'String', required: true, description: 'Official employment title' },
                        { name: 'github_username', type: 'String', required: true, description: 'Valid GitHub user handle' }
                    ],
                    headers: '{\n  "Content-Type": "application/json",\n  "Accept": "application/json"\n}',
                    body: '{\n  "name": "Ada Lovelace",\n  "email": "ada@simpel.task",\n  "department": "Platform Core",\n  "role": "Lead Architect",\n  "github_username": "ada-lovelace"\n}',
                    responseSchema: '{\n  "success": true,\n  "message": "Employee created and scores updated successfully via API.",\n  "data": {\n    "id": 4,\n    "name": "Ada Lovelace",\n    "email": "ada@simpel.task",\n    "department": "Platform Core",\n    "role": "Lead Architect",\n    "github_username": "ada-lovelace",\n    "created_at": "2026-06-11T10:52:00.000000Z"\n  }\n}',
                },
                {
                    id: 3,
                    category: 'Tasks',
                    name: 'Get Tasks & Sprints',
                    method: 'GET',
                    methodClass: 'bg-emerald-500',
                    endpoint: '/api/developer/tasks',
                    description: 'Fetches development tasks from MySQL.',
                    params: [],
                    headers: '{\n  "Accept": "application/json"\n}',
                    body: '',
                    responseSchema: '[\n  {\n    "id": 1,\n    "employee_id": 1,\n    "title": "Build Devtools MVP Interface",\n    "description": "Premium layout with swagger styling",\n    "status": "In Progress",\n    "assigned_date": "2026-06-11",\n    "completed_date": null,\n    "estimated_hours": 8,\n    "actual_hours": 0\n  }\n]',
                },
                {
                    id: 4,
                    category: 'Tasks',
                    name: 'Create Developer Task',
                    method: 'POST',
                    methodClass: 'bg-teal-600',
                    endpoint: '/api/developer/tasks',
                    description: 'Creates a task record and automatically triggers productivity score recalculations.',
                    params: [
                        { name: 'employee_id', type: 'Integer', required: true, description: 'Primary ID of developer (Foreign Key)' },
                        { name: 'title', type: 'String', required: true, description: 'Task title / header' },
                        { name: 'description', type: 'String', required: false, description: 'Detailed criteria (nullable)' },
                        { name: 'status', type: 'Enum', required: true, description: 'Must be: Pending, In Progress, Completed' },
                        { name: 'assigned_date', type: 'Date', required: true, description: 'Y-m-d format' },
                        { name: 'completed_date', type: 'Date', required: false, description: 'Y-m-d format (required if Completed)' },
                        { name: 'estimated_hours', type: 'Float', required: true, description: 'Expected completion time in hours' },
                        { name: 'actual_hours', type: 'Float', required: false, description: 'Logged hours (required if Completed)' }
                    ],
                    headers: '{\n  "Content-Type": "application/json",\n  "Accept": "application/json"\n}',
                    body: '{\n  "employee_id": 1,\n  "title": "Refactor API Controllers",\n  "description": "Clean up code debt",\n  "status": "Completed",\n  "assigned_date": "2026-06-10",\n  "completed_date": "2026-06-11",\n  "estimated_hours": 6.0,\n  "actual_hours": 5.5\n}',
                    responseSchema: '{\n  "success": true,\n  "message": "Task created and metrics updated via API.",\n  "data": {\n    "id": 12,\n    "employee_id": 1,\n    "title": "Refactor API Controllers",\n    "description": "Clean up code debt",\n    "status": "Completed",\n    "assigned_date": "2026-06-10",\n    "completed_date": "2026-06-11",\n    "estimated_hours": 6,\n    "actual_hours": 5.5,\n    "created_at": "2026-06-11T10:55:00.000000Z"\n  }\n}',
                },
                {
                    id: 5,
                    category: 'Working Hours',
                    name: 'Get Working Hours Log',
                    method: 'GET',
                    methodClass: 'bg-emerald-500',
                    endpoint: '/api/developer/working-hours',
                    description: 'Retrieve logged working times.',
                    params: [],
                    headers: '{\n  "Accept": "application/json"\n}',
                    body: '',
                    responseSchema: '[\n  {\n    "id": 1,\n    "employee_id": 1,\n    "work_date": "2026-06-11",\n    "hours_worked": 8.5\n  }\n]',
                },
                {
                    id: 6,
                    category: 'Working Hours',
                    name: 'Log Worked Hours',
                    method: 'POST',
                    methodClass: 'bg-teal-600',
                    endpoint: '/api/developer/working-hours',
                    description: 'Logs hours duration. Recomputes productivity metrics.',
                    params: [
                        { name: 'employee_id', type: 'Integer', required: true, description: 'ID of employee' },
                        { name: 'work_date', type: 'Date', required: true, description: 'Y-m-d format date of log' },
                        { name: 'hours_worked', type: 'Float', required: true, description: 'Daily hours log' }
                    ],
                    headers: '{\n  "Content-Type": "application/json",\n  "Accept": "application/json"\n}',
                    body: '{\n  "employee_id": 1,\n  "work_date": "2026-06-11",\n  "hours_worked": 8.0\n}',
                    responseSchema: '{\n  "success": true,\n  "message": "Working hours record created via API.",\n  "data": {\n    "id": 2,\n    "employee_id": 1,\n    "work_date": "2026-06-11",\n    "hours_worked": 8,\n    "created_at": "2026-06-11T10:56:00.000000Z"\n  }\n}',
                },
                {
                    id: 7,
                    category: 'Attendance',
                    name: 'Get Attendance Logs',
                    method: 'GET',
                    methodClass: 'bg-emerald-500',
                    endpoint: '/api/developer/attendance',
                    description: 'Lists chronological attendance logging.',
                    params: [],
                    headers: '{\n  "Accept": "application/json"\n}',
                    body: '',
                    responseSchema: '[\n  {\n    "id": 1,\n    "employee_id": 1,\n    "attendance_date": "2026-06-11",\n    "status": "Present"\n  }\n]',
                },
                {
                    id: 8,
                    category: 'Attendance',
                    name: 'Record Daily Status',
                    method: 'POST',
                    methodClass: 'bg-teal-600',
                    endpoint: '/api/developer/attendance',
                    description: 'Submits developer status for attendance metrics.',
                    params: [
                        { name: 'employee_id', type: 'Integer', required: true, description: 'ID of employee' },
                        { name: 'attendance_date', type: 'Date', required: true, description: 'Y-m-d format date' },
                        { name: 'status', type: 'Enum', required: true, description: 'Must be: Present, Absent, Late, Leave' }
                    ],
                    headers: '{\n  "Content-Type": "application/json",\n  "Accept": "application/json"\n}',
                    body: '{\n  "employee_id": 1,\n  "attendance_date": "2026-06-11",\n  "status": "Present"\n}',
                    responseSchema: '{\n  "success": true,\n  "message": "Attendance record created via API.",\n  "data": {\n    "id": 2,\n    "employee_id": 1,\n    "attendance_date": "2026-06-11",\n    "status": "Present",\n    "created_at": "2026-06-11T10:57:00.000000Z"\n  }\n}',
                },
                {
                    id: 9,
                    category: 'Metrics',
                    name: 'Get Team Score Metrics',
                    method: 'GET',
                    methodClass: 'bg-emerald-500',
                    endpoint: '/api/developer/metrics',
                    description: 'Fetches structured composite score outputs used for dashboard leaderboard widgets.',
                    params: [],
                    headers: '{\n  "Accept": "application/json"\n}',
                    body: '',
                    responseSchema: '[\n  {\n    "employee_id": 1,\n    "employee_name": "Linus Torvalds",\n    "productivity_score": 9.4,\n    "leadership_score": 8.7,\n    "tasks_assigned": 15,\n    "tasks_completed": 14,\n    "completion_rate": 93.33\n  }\n]',
                }
            ],

            selectedEndpoint() {
                return this.endpoints.find(e => e.id === this.selectedEndpointId) || this.endpoints[0];
            },

            selectEndpoint(ep) {
                this.selectedEndpointId = ep.id;
                this.testerMethod = ep.method;
                this.testerEndpoint = ep.endpoint;
                this.testerHeaders = ep.headers;
                this.testerBody = ep.body || '{}';
                this.testerResponse = null;
            },

            getCodeSnippet(lang) {
                const ep = this.selectedEndpoint();
                const fullUrl = this.baseUrl.replace(/\/$/, '') + '/' + ep.endpoint.replace(/^\//, '');
                
                if (lang === 'curl') {
                    if (ep.method === 'GET') {
                        return `curl -X GET "${fullUrl}" \\\n  -H "Accept: application/json"`;
                    } else {
                        return `curl -X POST "${fullUrl}" \\\n  -H "Content-Type: application/json" \\\n  -H "Accept: application/json" \\\n  -d '${ep.body.replace(/\n/g, '').replace(/\s+/g, ' ')}'`;
                    }
                } else if (lang === 'php') {
                    if (ep.method === 'GET') {
                        return `use Illuminate\\Support\\Facades\\Http;\n\n$response = Http::withHeaders([\n    'Accept' => 'application/json'\n])->get('${fullUrl}');\n\n$data = $response->json();`;
                    } else {
                        return `use Illuminate\\Support\\Facades\\Http;\n\n$response = Http::withHeaders([\n    'Accept' => 'application/json'\n])->post('${fullUrl}', ${ep.body.replace(/\n/g, '\n    ')});\n\n$data = $response->json();`;
                    }
                } else if (lang === 'node') {
                    if (ep.method === 'GET') {
                        return `fetch('${fullUrl}', {\n  method: 'GET',\n  headers: {\n    'Accept': 'application/json'\n  }\n})\n.then(res => res.json())\n.then(data => console.log(data))\n.catch(err => console.error(err));`;
                    } else {
                        return `fetch('${fullUrl}', {\n  method: 'POST',\n  headers: {\n    'Content-Type': 'application/json',\n    'Accept': 'application/json'\n  },\n  body: JSON.stringify(${ep.body.replace(/\n/g, '\n    ')})\n})\n.then(res => res.json())\n.then(data => console.log(data))\n.catch(err => console.error(err));`;
                    }
                } else if (lang === 'python') {
                    if (ep.method === 'GET') {
                        return `import requests\n\nresponse = requests.get(\n    '${fullUrl}',\n    headers={'Accept': 'application/json'}\n)\nprint(response.json())`;
                    } else {
                        return `import requests\nimport json\n\npayload = ${ep.body}\nresponse = requests.post(\n    '${fullUrl}',\n    json=payload,\n    headers={'Accept': 'application/json'}\n)\nprint(response.json())`;
                    }
                } else if (lang === 'java') {
                    if (ep.method === 'GET') {
                        return `// OkHttp Client\nOkHttpClient client = new OkHttpClient();\nRequest request = new Request.Builder()\n  .url("${fullUrl}")\n  .get()\n  .addHeader("Accept", "application/json")\n  .build();\n\nResponse response = client.newCall(request).execute();\nSystem.out.println(response.body().string());`;
                    } else {
                        return `// OkHttp Client\nOkHttpClient client = new OkHttpClient();\nMediaType mediaType = MediaType.parse("application/json");\nRequestBody body = RequestBody.create(mediaType, \n  "${ep.body.replace(/\n/g, '\\n').replace(/"/g, '\\"')}"\n);\nRequest request = new Request.Builder()\n  .url("${fullUrl}")\n  .post(body)\n  .addHeader("Content-Type", "application/json")\n  .addHeader("Accept", "application/json")\n  .build();\n\nResponse response = client.newCall(request).execute();\nSystem.out.println(response.body().string());`;
                    }
                } else if (lang === 'ruby') {
                    if (ep.method === 'GET') {
                        return `require 'net/http'\nrequire 'uri'\n\nuri = URI.parse("${fullUrl}")\nrequest = Net::HTTP::Get.new(uri)\nrequest["Accept"] = "application/json"\n\nresponse = Net::HTTP.start(uri.hostname, uri.port, use_ssl: uri.scheme == 'https') do |http|\n  http.request(request)\nend\nputs response.body`;
                    } else {
                        return `require 'net/http'\nrequire 'uri'\nrequire 'json'\n\nuri = URI.parse("${fullUrl}")\nrequest = Net::HTTP::Post.new(uri)\nrequest["Content-Type"] = "application/json"\nrequest["Accept"] = "application/json"\nrequest.body = ${ep.body.replace(/\n/g, '\n')}\n\nresponse = Net::HTTP.start(uri.hostname, uri.port, use_ssl: uri.scheme == 'https') do |http|\n  http.request(request)\nend\nputs response.body`;
                    }
                }
                return '';
            },

            sendTestRequest() {
                this.testerLoading = true;
                this.testerResponse = null;

                fetch("{{ route('developer.tools.test_api') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        base_url: this.baseUrl,
                        endpoint: this.testerEndpoint,
                        method: this.testerMethod,
                        headers: this.testerHeaders,
                        body: this.testerBody
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.testerResponse = data;
                    this.testerLoading = false;
                })
                .catch(err => {
                    this.testerResponse = {
                        status: 500,
                        time_ms: 0,
                        headers: {},
                        body: { error: 'Network error or internal proxy failure.', message: err.message }
                    };
                    this.testerLoading = false;
                });
            },

            resetTester() {
                const ep = this.selectedEndpoint();
                this.testerMethod = ep.method;
                this.testerEndpoint = ep.endpoint;
                this.testerHeaders = ep.headers;
                this.testerBody = ep.body || '{}';
                this.testerResponse = null;
            },

            testerResponseStatusClass() {
                if (!this.testerResponse) return '';
                const code = this.testerResponse.status;
                if (code >= 200 && code < 300) return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                if (code >= 300 && code < 400) return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
                if (code >= 400 && code < 500) return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
            },

            copyText(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            }
        };
    }
</script>
@endsection
