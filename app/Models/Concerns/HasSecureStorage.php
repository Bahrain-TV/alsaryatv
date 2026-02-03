<?php

namespace App\Models\Concerns;

use App\Services\CprHashingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasSecureStorage
{
    protected static function bootHasSecureStorage()
    {
        static::creating(function ($model): void {
            if (isset($model->cpr)) {
                try {
                    $model->cpr = app(CprHashingService::class)->hashCpr($model->cpr);
                } catch (\Exception $e) {
                    Log::error('Failed to hash CPR', [
                        'error' => $e->getMessage(),
                        'model' => get_class($model),
                    ]);
                    throw $e;
                }
            }
            // $model->created_by = auth()->id();
        });

        static::updating(function ($model): void {
            $model->updated_by = Auth::id();
        });
    }

    public function initializeHasSecureStorage()
    {
        $this->hidden[] = 'cpr';
        $this->fillable = array_merge($this->fillable, [
            'created_by',
            'updated_by',
        ]);
    }
}
