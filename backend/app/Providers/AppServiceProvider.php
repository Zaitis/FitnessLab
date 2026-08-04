<?php

namespace App\Providers;

use App\Application\Nutrition\Actions\GenerateNutritionPlanAction;
use App\Application\Workouts\Actions\GenerateWorkoutPlanAction;
use App\Domain\Nutrition\Contracts\MealTemplateCatalogue;
use App\Domain\Nutrition\Strategies\FatLossNutritionPlanStrategy;
use App\Domain\Nutrition\Strategies\MaintenanceNutritionPlanStrategy;
use App\Domain\Nutrition\Strategies\MuscleGainNutritionPlanStrategy;
use App\Domain\Workouts\Contracts\ExerciseCatalogue;
use App\Domain\Workouts\Strategies\FatLossWorkoutPlanStrategy;
use App\Domain\Workouts\Strategies\MaintenanceWorkoutPlanStrategy;
use App\Domain\Workouts\Strategies\MuscleGainWorkoutPlanStrategy;
use App\Infrastructure\Persistence\EloquentExerciseCatalogue;
use App\Infrastructure\Persistence\EloquentMealTemplateCatalogue;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExerciseCatalogue::class, EloquentExerciseCatalogue::class);

        $this->app->tag([
            FatLossWorkoutPlanStrategy::class,
            MuscleGainWorkoutPlanStrategy::class,
            MaintenanceWorkoutPlanStrategy::class,
        ], 'workout-plan-strategies');

        $this->app->when(GenerateWorkoutPlanAction::class)
            ->needs('$strategies')
            ->give(fn ($app) => $app->tagged('workout-plan-strategies'));

        $this->app->bind(MealTemplateCatalogue::class, EloquentMealTemplateCatalogue::class);

        $this->app->tag([
            FatLossNutritionPlanStrategy::class,
            MuscleGainNutritionPlanStrategy::class,
            MaintenanceNutritionPlanStrategy::class,
        ], 'nutrition-plan-strategies');

        $this->app->when(GenerateNutritionPlanAction::class)
            ->needs('$strategies')
            ->give(fn ($app) => $app->tagged('nutrition-plan-strategies'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every other endpoint in this API returns a flat JSON body with no
        // envelope (see MeasurementController, BmiController) — matching
        // that here means a JsonResource response doesn't stand out as the
        // one endpoint wrapped in {"data": ...}. Paginated collections are
        // unaffected: PaginatedResourceResponse always includes data/links
        // /meta regardless of this setting.
        JsonResource::withoutWrapping();

        // An N+1 that only shows up under production data volumes should fail
        // loudly here and in CI instead (.ai/laravel.md).
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('bmi', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Unauthenticated write endpoints that cost real money or reputation
        // if abused. `forgot-password` is the sharpest of the three: without
        // a limit it is an open relay for mailing arbitrary addresses through
        // fitnesslab@zaitis.dev, which burns the domain's sending reputation
        // that M0 deliberately set up SPF/DKIM/DMARC to protect.
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(5)->by($request->ip()));

        // Shared by login, forgot-password and reset-password: a per-IP limit
        // that a credential-stuffing run across many accounts can't walk past,
        // plus a tighter per-email limit so one address can't be targeted or
        // mail-bombed from a rotating IP pool.
        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip()),
            Limit::perHour(5)->by(Str::lower($request->string('email')->value())),
        ]);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
