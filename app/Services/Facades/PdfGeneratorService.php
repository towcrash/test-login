<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class PdfGeneratorService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'pdfGeneratorService';
    }
}