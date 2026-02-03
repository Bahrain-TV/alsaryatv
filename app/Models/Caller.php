<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Exception\DceSecurityException;

class Caller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'cpr',
        'is_family',
        'is_winner',
        'status',
        'ip_address',
        'hits',
        'last_hit',
        'notes',
        'level',
    ];

    protected $casts = [
        'is_family' => 'boolean',
        'is_winner' => 'boolean',
    ];

    /**
     * Boot method to add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // This is the likely cause of our issue - it's throwing the exception when updating
        static::updating(function ($caller) {
            // Allow updates if any of these conditions are met:
            // 1. User is authenticated (admin)
            // 2. Request has specific flags that indicate this is an allowed update
            // 3. The only thing being updated is the 'hits' counter

            // Check for our special flags first
            $request = request();
            if ($request->has('increment_if_exists') || $request->has('increment_hits')) {
                return true; // Allow the update to proceed
            }

            // Check if it's an admin
            if (Auth::check()) {
                return true; // Allow the update to proceed
            }

            // Check if only the hits field is being updated
            $dirty = $caller->getDirty();
            if (count($dirty) === 1 && array_key_exists('hits', $dirty)) {
                return true; // Allow hits-only updates
            }

            // If we get here, it's an unauthorized update attempt
            throw new DceSecurityException('Unauthorized caller update attempt');
        });
    }

    /**
     * Gets the winners only
     *
     * @param  $query
     * @return mixed
     *
     * @throws DceSecurityException
     * @throws \Exception
     */
    public static function winners()
    {
        return self::where('is_winner', true);
    }

    /**
     * Gets the hits of a user
     */
    public function getHits(): int|false
    {
        $hits = $this->hits;
        if ($hits === null) {
            return false;
        }

        return $hits;
    }

    /**
     * Gets the hits of a user
     */
    public function incrementHits(): int|false
    {
        $hits = $this->hits;
        if ($hits === null) {
            return false;
        }

        $this->hits = $hits + 1;
        $this->save();

        return $this->hits;
    }

    /**
     * Assigns an IP address if null then return the last known IP
     *
     * @throws DceSecurityException
     * @throws \Exception
     */
    public function assignIpAddress(?string $ip = null): string|false
    {
        if ($ip === null) {
            return $this->ip_address;
        }

        if ($this->ip_address === null) {
            $this->ip_address = $ip;
            $this->save();
        }

        return $this->ip_address;
    }

    /**
     * Gets the IP address of a user
     *
     * @throws DceSecurityException
     * @throws \Exception
     */
    public function getIpAddress(): string|false
    {
        return $this->ip_address;
    }

    /**
     * Gets the hits of a user
     *
     * @throws DceSecurityException
     * @throws \Exception
     * */
    public function getHitsCount(): int|false
    {
        return $this->hits;
    }

    /**
     * Gets the hits of a user
     *
     * @throws DceSecurityException
     * @throws \Exception
     * */
    public function incrementHitsCount(): int|false
    {
        $this->hits += 1;
        $this->save();

        return $this->hits;
    }

    /**
     * LEVELS Up a Caller by highlighting the name
     * if passed a given hits
     *
     * @throws DceSecurityException
     * @throws \Exception
     */
    public function levelUp(int $hits = 5): void
    {
        if ($this->hits >= $hits) {
            $this->name = "<span class='text-success'>{$this->name}</span>";
            $this->save();
        }
    }

    /**
     * LEVELS Up a Caller by highlighting the name
     * if passed a given hits
     *
     * @throws DceSecurityException
     * @throws \Exception
     */
    public function levelDown(int $hits = 5): void
    {
        if ($this->hits >= $hits) {
            $this->name = "<span class='text-danger'>{$this->name}</span>";
            $this->save();
        }
    }
}
