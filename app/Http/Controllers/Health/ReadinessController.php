<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');

            return response()->json([
                'status' => 'ready',
                'application' => 'ok',
                'database' => 'ok',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'not_ready',
                'application' => 'ok',
                'database' => 'unavailable',
            ], 503);
        }
    }
}
