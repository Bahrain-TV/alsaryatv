<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCallerRequest;
use App\Http\Requests\UpdateCallerRequest;
use App\Models\Caller;
use App\Providers\HitsCounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class CallerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['store', 'create', 'success', 'checkCpr']);
    }

    public function index()
    {
        $callers = Caller::latest()->paginate(25);
        return view('callers.index', ['callers' => $callers]);
    }

    public function create()
    {
        return view('callers.create');
    }

    /**
     * Store a new caller or update existing caller record
     */
    public function store(StoreCallerRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cpr = $validated['cpr'];

        // Rate limiting by CPR and IP using Laravel's RateLimiter
        if (RateLimiter::tooManyAttempts('caller-registration:'.$cpr, 1)) {
            return back()->withErrors(['cpr' => 'You can only register once every 5 minutes.'])->withInput();
        }
        
        if (RateLimiter::tooManyAttempts('caller-registration-ip:'.$request->ip(), 10)) {
             return back()->withErrors(['general' => 'Too many registrations from your location.'])->withInput();
        }

        $caller = Caller::updateOrCreate(
            ['cpr' => $cpr],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone_number'],
                'ip_address' => $request->ip(),
                'status' => 'active',
            ]
        );

        $caller->incrementHits();

        // Record attempts
        RateLimiter::hit('caller-registration:'.$cpr, 300);
        RateLimiter::hit('caller-registration-ip:'.$request->ip(), 3600);

        session([
            'cpr' => $cpr,
            'userHits' => $caller->hits,
            'totalHits' => HitsCounter::getHits(),
        ]);

        return redirect()->route('callers.success');
    }

    public function edit(Caller $caller)
    {
        return view('callers.edit', compact('caller'));
    }

    public function update(UpdateCallerRequest $request, Caller $caller)
    {
        $validated = $request->validated();
        
        $caller->update([
            'name' => $validated['name'],
            'phone' => $validated['phone_number'],
            'cpr' => $validated['cpr'],
            'hits' => $validated['hits'] ?? $caller->hits,
            'is_winner' => $request->boolean('is_winner'),
            'notes' => $validated['notes'] ?? $caller->notes,
        ]);

        return redirect()->route('dashboard')->with('success', 'Caller updated successfully.');
    }

    public function winners()
    {
        $winners = Caller::winners()->latest()->paginate(25);
        return view('callers.winners', ['winners' => $winners]);
    }

    public function checkCpr(Request $request)
    {
        return response()->json([
            'exists' => Caller::where('cpr', $request->cpr)->exists()
        ]);
    }

    public function toggleWinner(Caller $caller)
    {
        $caller->update(['is_winner' => ! $caller->is_winner]);

        return response()->json([
            'success' => true,
            'is_winner' => $caller->is_winner,
            'message' => $caller->is_winner ? 'Caller marked as winner!' : 'Winner status removed.',
        ]);
    }

    public function destroy(Caller $caller)
    {
        $caller->delete();
        return redirect()->route('dashboard')->with('success', 'Caller deleted successfully.');
    }
}
