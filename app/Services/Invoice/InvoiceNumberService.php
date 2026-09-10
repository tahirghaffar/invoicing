<?php

namespace App\Services\Invoice;

use App\Models\InvoiceSequence;

class InvoiceNumberService
{
    public function next(
        int $businessId,
        int $year
    ): string {

        $sequence = InvoiceSequence::where(
            'business_id',
            $businessId
        )
            ->where('prefix', 'INV')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (!$sequence) {

            InvoiceSequence::create([
                'business_id' => $businessId,
                'prefix' => 'INV',
                'year' => $year,
                'current_number' => 0,
                'padding' => 6,
            ]);

            $sequence = InvoiceSequence::where(
                'business_id',
                $businessId
            )
                ->where('prefix', 'INV')
                ->where('year', $year)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $sequence->increment('current_number');

        $sequence->refresh();

        return sprintf(
            '%s-%d-%0' . $sequence->padding . 'd',
            $sequence->prefix,
            $year,
            $sequence->current_number
        );
    }
}
