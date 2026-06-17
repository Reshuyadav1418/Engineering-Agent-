<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository contracts & implementations
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\TaskRepository;

// Service contracts & implementations
use App\Services\Contracts\EmployeeServiceInterface;
use App\Services\EmployeeService;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\TaskService;
use App\Repositories\Contracts\ProductivityScoreRepositoryInterface;
use App\Repositories\Eloquent\ProductivityScoreRepository;
use App\Repositories\Contracts\LeadershipScoreRepositoryInterface;
use App\Repositories\Eloquent\LeadershipScoreRepository;
use App\Repositories\Contracts\AIReportRepositoryInterface;
use App\Repositories\Eloquent\AIReportRepository;
use App\Services\Contracts\MetricsServiceInterface;
use App\Services\MetricsService;
use App\Services\Contracts\LeadershipScoreServiceInterface;
use App\Services\LeadershipScoreService;
use App\Services\Contracts\AIAnalysisServiceInterface;
use App\Services\AIAnalysisService;
use App\Services\Contracts\TeamServiceInterface;
use App\Services\TeamService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        $this->app->bind(ProductivityScoreRepositoryInterface::class, ProductivityScoreRepository::class);
        $this->app->bind(LeadershipScoreRepositoryInterface::class, LeadershipScoreRepository::class);
        $this->app->bind(AIReportRepositoryInterface::class, AIReportRepository::class);
        $this->app->bind(MetricsServiceInterface::class, MetricsService::class);
        $this->app->bind(LeadershipScoreServiceInterface::class, LeadershipScoreService::class);
        $this->app->bind(AIAnalysisServiceInterface::class, AIAnalysisService::class);
        $this->app->bind(TeamServiceInterface::class, TeamService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
