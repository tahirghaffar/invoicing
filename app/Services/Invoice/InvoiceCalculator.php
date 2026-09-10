<?php

namespace App\Services\Invoice;

class InvoiceCalculator
{
    public function calculate(array $items): array
    {
        $result = [];

        $subtotal = 0;
        $salesTax = 0;
        $furtherTax = 0;
        $extraTax = 0;
        $fed = 0;
        $discountTotal = 0;
        $grandTotal = 0;

        foreach ($items as $index => $item) {

            $quantity =
                (float)($item['quantity'] ?? 0);

            $unitPrice =
                (float)($item['unit_price'] ?? 0);

            $discount =
                (float)($item['discount'] ?? 0);

            $taxRate =
                (float)($item['tax_rate'] ?? 0);

            $extra =
                (float)($item['extra_tax'] ?? 0);

            $further =
                (float)($item['further_tax'] ?? 0);

            $fedPayable =
                (float)($item['fed_payable'] ?? 0);

            $gross =
                round($quantity * $unitPrice, 4);

            $taxable =
                max(0, $gross - $discount);

            $tax =
                round(
                    $taxable * $taxRate / 100,
                    4
                );

            $lineTotal =
                round(
                    $taxable +
                    $tax +
                    $extra +
                    $further +
                    $fedPayable,
                    4
                );

            $item['total_value'] = $gross;

            $item['value_sales_excluding_st'] =
                $taxable;

            $item['sales_tax_applicable'] =
                $tax;

            $item['line_total'] =
                $lineTotal;

            $result[] = $item;

            $subtotal += $taxable;
            $salesTax += $tax;
            $extraTax += $extra;
            $furtherTax += $further;
            $fed += $fedPayable;
            $discountTotal += $discount;
            $grandTotal += $lineTotal;
        }

        return [
            'items' => $result,

            'subtotal' =>
                round($subtotal, 4),

            'sales_tax' =>
                round($salesTax, 4),

            'further_tax' =>
                round($furtherTax, 4),

            'extra_tax' =>
                round($extraTax, 4),

            'fed' =>
                round($fed, 4),

            'discount' =>
                round($discountTotal, 4),

            'grand_total' =>
                round($grandTotal, 4),
        ];
    }
}
