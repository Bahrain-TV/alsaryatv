<?php

use App\Http\Controllers\CallerController;
use App\Providers\HitsCounter;
use Carbon\Carbon;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Route;

// Shared Ramadan date logic
$getRamadanContext = function () {
    $ramadanStartDate = config('ramadan.start_date', '2026-02-18');
    $hijriDate = config('ramadan.hijri_date', '1 رمضان 1447 هـ');
    $registrationOpenDate = config('ramadan.start_date', '2026-02-18');

    return [
        'ramadanDate' => Carbon::parse($ramadanStartDate)->locale('ar')->translatedFormat('j F Y'),
        'ramadanStartISO' => $ramadanStartDate,
        'ramadanHijri' => $hijriDate,
        'registrationOpenISO' => $registrationOpenDate,
        'totalHits' => HitsCounter::getHits(),
        'appVersion' => trim(file_exists(base_path('VERSION')) ? file_get_contents(base_path('VERSION')) : '1.0.0'),
    ];
};

// Main Entry Routes
Route::get('/splash', fn () => view('splash', $getRamadanContext()))->name('splash');

Route::get('/', fn () => view('welcome', $getRamadanContext()))->name('home');
Route::get('/welcome', fn () => view('welcome', $getRamadanContext()))->name('welcome');
Route::get('/family', fn () => view('welcome', $getRamadanContext()))->name('family.registration');

// Public OBS overlay — accessible without authentication for OBS Browser Source
Route::get('/obs-overlay', fn () => view('obs.overlay'))->name('obs.overlay');

// Protected routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function (): void {
    Route::get('/dashboard', [CallerController::class, 'index'])->name('dashboard');
    Route::get('/winners', [CallerController::class, 'winners'])->name('winners');
    Route::get('/families', [CallerController::class, 'families'])->name('families');

    // Filament shortcuts (if needed)
    Route::get('/admin/callers', [\App\Filament\Resources\CallerResource\Pages\ListCallers::class, 'index'])->name('filament.admin.resources.callers.index');
    Route::get('/admin/callers/winners', [\App\Filament\Resources\CallerResource\Pages\ListWinners::class, 'index'])->name('filament.admin.resources.callers.winners');
});

Route::get('/privacy', fn () => view('privacy', [
    'policy' => Markdown::parse(file_get_contents(resource_path('markdown/privacy.md'))),
]))->name('privacy');

Route::post('/locale/{locale}', function (string $locale) {
    $supported = ['ar', 'en'];

    if (! in_array($locale, $supported, true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

// Caller registration routes
Route::prefix('callers')->name('callers.')->group(function (): void {
    Route::get('/create', [CallerController::class, 'create'])->name('create');
    Route::post('/', [CallerController::class, 'store'])->name('store')->middleware('throttle:10,1');

    Route::get('/success', function () {
        if (! session()->has('cpr')) {
            return redirect('/')->with('error', 'Unauthorized access attempt');
        }

        $cpr = session('cpr');

        return view('callers.success', [
            'userHits' => session('userHits', HitsCounter::getUserHits($cpr)),
            'totalHits' => session('totalHits', HitsCounter::getTotalHits()),
            'seconds' => session('seconds', 30),
            'cpr' => $cpr,
            'isDirtyFile' => true, // Registration successful
        ]);
    })->name('success');

    // Action routes
    Route::middleware('auth')->group(function (): void {
        Route::post('/{caller}/toggle-winner', [CallerController::class, 'toggleWinner'])->name('toggle-winner');
        Route::post('/random-winner', [CallerController::class, 'randomWinner'])->name('random-winner');
        Route::delete('/{caller}', [CallerController::class, 'destroy'])->name('destroy');
        Route::put('/{caller}', [CallerController::class, 'update'])->name('update');
        Route::get('/{caller}/edit', [CallerController::class, 'edit'])->name('edit');
    });
});

// Registration forms with toggle
Route::get('/register', fn () => view('calls.register'))->name('registration.form');

// CSRF Test Routes
Route::get('/csrf-test', fn () => view('csrf-test'))->name('csrf.test.page');
Route::post('/csrf-test', fn () => response()->json([
    'message' => 'CSRF token is valid! ✓',
    'timestamp' => now(),
    'session_id' => session()->getId(),
]))->name('csrf.test');
