<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class EvaluacionPdfService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'evaluacionPdfService';
    }
}