<?php

use App\Http\Controllers\CallerController;
use App\Providers\HitsCounter;
use Carbon\Carbon;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Route;

// Splash screen (entry point)
Route::get('/splash', function () {
    return view('splash');
})->name('splash');

// Individual caller registration (friendly URL)
// Displays countdown to Ramadan when registration is closed
Route::get('/', function () {
    $registrationOpenDate = env('REGISTERATION_OPEN', '2026-02-26');
    $ramadanDate = Carbon::parse($registrationOpenDate);
    
    // Format date in Arabic
    $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    $formattedDate = $ramadanDate->day . ' ' . $arabicMonths[$ramadanDate->month] . ' ' . $ramadanDate->year;
    
    return view('welcome', [
        'ramadanDate' => $formattedDate,
    ]);
})->name('home');

// Family caller registration (friendly URL)
Route::get('/family', function () {
    $registrationOpenDate = env('REGISTERATION_OPEN', '2026-02-26');
    $ramadanDate = Carbon::parse($registrationOpenDate);
    
    $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    $formattedDate = $ramadanDate->day . ' ' . $arabicMonths[$ramadanDate->month] . ' ' . $ramadanDate->year;
    
    return view('welcome', [
        'ramadanDate' => $formattedDate,
    ]);
})->name('family.registration');

// Welcome page (informational page with Ramadan countdown)
Route::get('/welcome', function () {
    $registrationOpenDate = env('REGISTERATION_OPEN', '2026-02-26');
    $ramadanDate = Carbon::parse($registrationOpenDate);
    
    $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    $formattedDate = $ramadanDate->day . ' ' . $arabicMonths[$ramadanDate->month] . ' ' . $ramadanDate->year;
    
    return view('welcome', [
        'ramadanDate' => $formattedDate,
    ]);
})->name('welcome');

// Protected routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function (): void {
    Route::get('/dashboard', [CallerController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->group(function (): void {
        Route::get('/winners', [CallerController::class, 'winners'])->name('winners');
        Route::get('/families', [CallerController::class, 'families'])->name('families');
    });
Route::get('/admin/callers', [\App\Filament\Resources\CallerResource\Pages\ListCallers::class, 'index'])->name('filament.admin.resources.callers.index');

Route::get('/admin/callers/winners', [\App\Filament\Resources\CallerResource\Pages\ListWinners::class, 'index'])->name('filament.admin.resources.callers.winners');
Route::get('/privacy', function () {
    return view('privacy', [
        'policy' => Markdown::parse(file_get_contents(resource_path('markdown/privacy.md'))),
    ]);
})->name('privacy');

// Caller registration routes
Route::prefix('callers')->name('callers.')->middleware(['web'])->group(function (): void {
    Route::get('/create', [CallerController::class, 'create'])->name('create');

    // Fix: Remove non-existent 'csrf' middleware and use properly named middleware
    Route::post('/', [CallerController::class, 'store'])
        ->name('store')
        ->middleware('throttle:10,1');

    // Success route with session check and 30 second countdown
    Route::get('/success', function () {
        // If someone tries to access this directly without the proper session data,
        // redirect them back to the homepage with error message
        if (! session()->has('name') && ! session()->has('full_name')) {
            return redirect('/')->with('error', 'Unauthorized access attempt');
        }

        $cpr = session('cpr');
        $isDirtyFile = \App\Services\DirtyFileManager::exists($cpr);

        // Use the user-specific hits passed from the controller
        // If not available, get default values
        $userHits = session(
            'userHits',
            HitsCounter::getUserHits(session('cpr') ?? null) ?? 1
        );
        $totalHits = session('totalHits', HitsCounter::getTotalHits());

        return view('callers.success', [
            'userHits' => $userHits,
            'totalHits' => $totalHits,
            'seconds' => session('seconds', 30),
            'isDirtyFile' => $isDirtyFile,
            'cpr' => $cpr,
        ]);
    })->name('success');
});

// Registration forms with toggle
Route::get('/register', function () {
    return view('calls.register');
})->name('registration.form');

// CSRF Test Routes
Route::get('/csrf-test', function () {
    return view('csrf-test');
})->name('csrf.test.page');

Route::post('/csrf-test', function () {
    return response()->json([
        'message' => 'CSRF token is valid! ✓',
        'timestamp' => now(),
        'session_id' => session()->getId(),
    ]);
})->name('csrf.test');
