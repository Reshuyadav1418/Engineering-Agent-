<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AIReportController;
use App\Http\Controllers\VCSController;
use App\Http\Controllers\TeamController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Employee CRUD
Route::resource('employees', EmployeeController::class);
Route::get('api/employees/{employee}/attendance-hours', [EmployeeController::class, 'getAttendanceHours'])->name('api.employees.attendance_hours');

// Team CRUD
Route::resource('teams', TeamController::class);

// Task CRUD
Route::resource('tasks', TaskController::class);

// Leaderboard
Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('leaderboard/filter', [LeaderboardController::class, 'filtered'])->name('leaderboard.filtered');
Route::get('leaderboard/export', [LeaderboardController::class, 'export'])->name('leaderboard.export');

// AI reports
// AI reports
Route::get('engineering-agent/reports', [AIReportController::class, 'index'])->name('ai.report.index');
Route::get('engineering-agent/report/{employee}', [AIReportController::class, 'show'])->name('ai.report.show');
Route::post('engineering-agent/report/{employee}/generate', [AIReportController::class, 'generate'])->name('ai.report.generate');
Route::get('engineering-agent/team-report/{team}', [AIReportController::class, 'showTeam'])->name('ai.report.show_team');
Route::post('engineering-agent/team-report/{team}/generate', [AIReportController::class, 'generateTeam'])->name('ai.report.generate_team');
Route::delete('engineering-agent/reports/{report}', [AIReportController::class, 'destroy'])->name('ai.report.destroy');

// AI Chatbot
use App\Http\Controllers\ChatController;
Route::post('engineering-agent/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('engineering-agent/chat/history', [ChatController::class, 'getHistory'])->name('chat.history');

// VCS Integration
use App\Http\Controllers\VcsReportController;
Route::get('engineering-agent/vcs', [VCSController::class, 'index'])->name('vcs.index');
Route::post('engineering-agent/vcs/sync', [VCSController::class, 'sync'])->name('vcs.sync');
Route::get('engineering-agent/vcs/{employee}/analysis', [VcsReportController::class, 'show'])->name('vcs.analysis.show');
Route::post('engineering-agent/vcs/{employee}/analysis/generate', [VcsReportController::class, 'generate'])->name('vcs.analysis.generate');

// Developer Tools & Sandbox
use App\Http\Controllers\DeveloperToolsController;
Route::get('developer-tools', [DeveloperToolsController::class, 'index'])->name('developer.tools');
Route::post('developer-tools/employee', [DeveloperToolsController::class, 'submitEmployee'])->name('developer.tools.employee');
Route::post('developer-tools/task', [DeveloperToolsController::class, 'submitTask'])->name('developer.tools.task');
Route::post('developer-tools/working-hours', [DeveloperToolsController::class, 'submitWorkingHours'])->name('developer.tools.working_hours');
Route::post('developer-tools/attendance', [DeveloperToolsController::class, 'submitAttendance'])->name('developer.tools.attendance');
Route::post('developer-tools/test-api', [DeveloperToolsController::class, 'testApi'])->name('developer.tools.test_api');

// Developer Mock API endpoints (Simulated postman/swagger backend)
use App\Http\Middleware\ForceJsonResponse;

Route::prefix('api/developer')
    ->middleware(ForceJsonResponse::class)
    ->group(function() {
        Route::get('employees', [DeveloperToolsController::class, 'apiGetEmployees']);
        Route::post('employees', [DeveloperToolsController::class, 'apiPostEmployee']);
        Route::get('tasks', [DeveloperToolsController::class, 'apiGetTasks']);
        Route::post('tasks', [DeveloperToolsController::class, 'apiPostTask']);
        Route::get('working-hours', [DeveloperToolsController::class, 'apiGetWorkingHours']);
        Route::post('working-hours', [DeveloperToolsController::class, 'apiPostWorkingHours']);
        Route::get('attendance', [DeveloperToolsController::class, 'apiGetAttendance']);
        Route::post('attendance', [DeveloperToolsController::class, 'apiPostAttendance']);
        Route::get('metrics', [DeveloperToolsController::class, 'apiGetMetrics']);
    });

