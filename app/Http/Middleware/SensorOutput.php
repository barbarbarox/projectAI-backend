<?php

namespace App\Http\Middleware;

use App\Services\InformationControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SensorOutput
{
    protected InformationControlService $infoControl;

    public function __construct(InformationControlService $infoControl)
    {
        $this->infoControl = $infoControl;
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
