<?php

namespace App\Services\FBR;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class FbrQrCodeService
{
    public function generate(
        string $fbrInvoiceNumber
    ): string {

        $builder = new Builder(
            writer: new PngWriter(),

            data: $fbrInvoiceNumber,

            encoding:
            new Encoding('UTF-8'),

            errorCorrectionLevel:
            ErrorCorrectionLevel::Low,

            size: 300,

            margin: 10,

            roundBlockSizeMode:
            RoundBlockSizeMode::Margin
        );

        return $builder
            ->build()
            ->getDataUri();
    }
}
