<?php

namespace App\Services;

use App\Models\Caller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CallerCsvExporter
{
    /**
     * Standard CSV headers for caller exports.
     */
    public const HEADERS = [
        'ID',
        'Name',
        'Phone',
        'CPR',
        'Hits',
        'Status',
        'Is Winner',
        'IP Address',
        'Last Hit',
        'Notes',
        'Created At',
        'Updated At',
    ];

    /**
     * Convert a single Caller model to a CSV row array matching HEADERS order.
     *
     * @param  Caller|object  $caller  A Caller model or stdClass row
     */
    public static function toRow(object $caller): array
    {
        return [
            $caller->id ?? '',
            $caller->name ?? '',
            $caller->phone ?? '',
            $caller->cpr ?? '',
            $caller->hits ?? 0,
            $caller->status ?? 'active',
            self::formatBoolean($caller->is_winner ?? false),
            $caller->ip_address ?? '',
            self::formatDate($caller->last_hit ?? null),
            $caller->notes ?? '',
            self::formatDate($caller->created_at ?? null),
            self::formatDate($caller->updated_at ?? null),
        ];
    }

    /**
     * Write callers to a CSV file handle (headers + rows).
     *
     * @param  resource  $handle  An open file handle from fopen()
     * @param  iterable<Caller|object>  $callers
     * @return int Number of rows written
     */
    public static function writeTo($handle, iterable $callers): int
    {
        fputcsv($handle, self::HEADERS);

        $count = 0;
        foreach ($callers as $caller) {
            fputcsv($handle, self::toRow($caller));
            $count++;
        }

        return $count;
    }

    /**
     * Write callers from a query in chunks to avoid memory issues.
     *
     * @param  resource  $handle
     * @return int Number of rows written
     */
    public static function writeChunked($handle, Builder $query, int $chunkSize = 500): int
    {
        fputcsv($handle, self::HEADERS);

        $count = 0;
        $query->chunk($chunkSize, function (Collection $callers) use ($handle, &$count): void {
            foreach ($callers as $caller) {
                fputcsv($handle, self::toRow($caller));
                $count++;
            }
        });

        return $count;
    }

    private static function formatBoolean(mixed $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    private static function formatDate(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
