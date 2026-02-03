<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallerRequest;
use App\Http\Requests\UpdateCallerRequest;
use App\Models\Caller;
use App\Traits\SecureOperations;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Exception\DceSecurityException;

class CallerController extends Controller
{
    use AuthorizesRequests, SecureOperations;

    public function __construct()
    {
        $this->middleware('throttle:60,1');
        // Laravel's built-in CSRF protection is included in the 'web' middleware group
        $this->middleware('web');
        $this->middleware('auth')->except(['store']);
    }

    public function index()
    {
        $callers = Caller::all();

        return view('callers.index', compact('callers'));
    }

    public function create()
    {
        return view('callers.create');
    }

    /**
     * Store a new caller or update existing caller record
     *
     * @return RedirectResponse
     *
     * @throws DceSecurityException
     */
    public function store(StoreCallerRequest $request)
    {
        // Get validated data early to use throughout method
        $validated = $request->validated();

        $isNewSubmission = $request->boolean('is_new_caller') ||
                          $request->input('action_type') === 'create';

        $incrementIfExists = $request->boolean('increment_if_exists') ||
                           $request->boolean('increment_hits');

        if (! $isNewSubmission) {
            $this->authorize('create', Caller::class);
        }

        // Rate limiting checks - by CPR and by IP
        $this->checkRateLimitOrFail($validated['cpr']);
        $this->checkIpRateLimitOrFail();

        // Log attempt
        $this->logRegistrationAttempt($isNewSubmission, $validated, $request);

        try {
            $existingCaller = $this->findExistingCaller($validated['cpr']);

            // Generate and store validation token
            session(['caller_token' => Str::uuid()]);

            $caller = $this->createOrUpdateCaller(
                $existingCaller,
                $validated,
                $isNewSubmission,
                $incrementIfExists,
                $request
            );

        } catch (\Exception $e) {
            $this->logSecurityEvent('caller.lookup.failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            throw new DceSecurityException('Invalid lookup attempt');
        }

        // Store session data
        $this->storeSessionData($validated);

        // Get statistics
        $stats = $this->getCallerStatistics($validated['cpr']);

        $this->logSecurityEvent('caller.registration.success', [
            'name' => $validated['name'],
            'hits' => $stats['userHits'],
            'caller_type' => $validated['caller_type'] ?? 'individual',
        ]);

        // Mark success with dirty file flag - indicates successful registration
        \App\Services\DirtyFileManager::markSuccessful($validated['cpr']);

        return redirect()->route('callers.success')->with([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'caller_type' => $validated['caller_type'] ?? 'individual',
            'cpr' => $validated['cpr'],
            'userHits' => $stats['userHits'],
            'totalHits' => $stats['totalHits'],
            'seconds' => 30,
            'isDirtyFile' => true,
        ]);
    }

    /**
     * Check rate limit for caller creation
     * Prevents users from registering more than once every 5 minutes (300 seconds) per CPR
     *
     * @throws DceSecurityException
     */
    private function checkRateLimitOrFail(string $cpr): void
    {
        // Rate limit: 1 registration per 5 minutes (300 seconds) per CPR
        if (! $this->checkRateLimit('caller_creation:'.$cpr, 1, 300)) {
            $this->logSecurityEvent('caller_registration.rate_limit_exceeded', [
                'cpr' => substr($cpr, 0, 3) . '***', // Log partial CPR for security
                'ip' => request()->ip(),
            ]);
            throw new DceSecurityException('You can only register once every 5 minutes. Please try again later.');
        }
    }

    /**
     * Check IP-based rate limit to prevent bulk abuse
     * Prevents registering more than 10 records from the same IP within 1 hour
     *
     * @throws DceSecurityException
     */
    private function checkIpRateLimitOrFail(): void
    {
        $ip = request()->ip();
        $key = 'caller_creation_ip:'.$ip;

        // Rate limit: Maximum 10 registrations per IP per hour
        if (! $this->checkRateLimit($key, 10, 3600)) {
            $this->logSecurityEvent('caller_registration.ip_rate_limit_exceeded', [
                'ip' => $ip,
            ]);
            throw new DceSecurityException('Too many registrations from your location. Please try again later.');
        }
    }

    /**
     * Log registration attempt
     */
    private function logRegistrationAttempt(bool $isNewSubmission, array $validated, Request $request): void
    {
        $this->logSecurityEvent('caller.registration.attempt', [
            'is_new_caller' => $isNewSubmission ? 'yes' : 'no',
            'caller_type' => $validated['caller_type'] ?? 'individual',
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Find existing caller by CPR
     */
    private function findExistingCaller(string $cpr): ?Caller
    {
        $existingCaller = Caller::where('cpr', $cpr)->first();

        $this->logSecurityEvent('caller.lookup', [
            'cpr' => $cpr,
            'ip' => request()->ip(),
            'caller_exists' => $existingCaller ? 'yes' : 'no',
        ]);

        return $existingCaller;
    }

    /**
     * Create new or update existing caller
     */
    private function createOrUpdateCaller(
        ?Caller $existingCaller,
        array $validated,
        bool $isNewSubmission,
        bool $incrementIfExists,
        Request $request
    ): Caller {
        if ($existingCaller && (! $isNewSubmission || $incrementIfExists)) {
            $caller = $this->updateExistingCaller($existingCaller, $validated, $request);
        } else {
            $caller = $this->createNewCaller($validated, $request);
        }

        return $caller;
    }

    /**
     * Update existing caller record
     */
    private function updateExistingCaller(Caller $caller, array $validated, Request $request): Caller
    {
        $caller->update([
            'name' => $validated['name'],
            'phone' => $validated['phone_number'],
            'ip_address' => $request->ip(),
            'hits' => $caller->hits + 1,
        ]);

        $this->logSecurityEvent('caller.update', [
            'name' => $validated['name'],
            'type' => $validated['caller_type'] ?? 'individual',
            'ip' => $request->ip(),
            'hits' => $caller->hits,
        ]);

        return $caller;
    }

    /**
     * Create new caller record
     */
    private function createNewCaller(array $validated, Request $request): Caller
    {
        $caller = Caller::create([
            'name' => $validated['name'],
            'phone' => $validated['phone_number'],
            'cpr' => $validated['cpr'],
            'is_family' => ($validated['caller_type'] ?? 'individual') === 'family',
            'ip_address' => $request->ip(),
            'status' => 'active',
            'hits' => 1,
        ]);

        $this->logSecurityEvent('caller.created', [
            'name' => $validated['name'],
            'type' => $validated['caller_type'] ?? 'individual',
            'ip' => $request->ip(),
        ]);

        return $caller;
    }

    /**
     * Store caller data in session
     */
    private function storeSessionData(array $validated): void
    {
        session([
            'name' => $validated['name'],
            'cpr' => $validated['cpr'],
        ]);
    }

    /**
     * Get caller statistics
     */
    private function getCallerStatistics(string $cpr): array
    {
        return [
            'userHits' => Caller::where('cpr', $cpr)->sum('hits'),
            'totalHits' => Caller::sum('hits'),
        ];
    }

    public function edit(Caller $caller)
    {
        $callers = Caller::all();

        return view('callers.edit', compact('caller', 'callers'));
    }

    public function update(UpdateCallerRequest $request, Caller $caller)
    {
        if (! Gate::allows('update', $caller)) {
            throw new DceSecurityException('Unauthorized access attempt.');
        }

        // Use validated data from the request class
        $validatedData = $request->validated();

        // Update the call associated with this caller
        $caller->calls()->where('id', $validatedData['caller_id'])->update($validatedData);

        return redirect()->route('dashboard')->with('success', 'Call updated successfully.');
    }

    /**
     * Gets the last 100 callers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCallers()
    {
        $callers = Caller::with('calls')->latest()->take(100)->get();

        return response()->json($callers);
    }

    /**
     * Display a listing of winners.
     *
     * @return \Illuminate\View\View
     */
    public function winners()
    {
        $winners = Caller::where('is_winner', true)->latest()->get();

        return view('callers.winners', compact('winners'));
    }

    /**
     * Display a listing of family entries.
     *
     * @return \Illuminate\View\View
     */
    public function families()
    {
        $families = Caller::where('is_family', true)->latest()->get();

        return view('callers.families', compact('families'));
    }

    /**
     * Check if a CPR already exists in the database
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCpr(Request $request)
    {
        $cpr = $request->cpr;

        // Check if the CPR exists
        $exists = Caller::where('cpr', $cpr)->exists();

        return response()->json([
            'exists' => $exists,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function success()
    {
        return view('calls.success');
    }
}
