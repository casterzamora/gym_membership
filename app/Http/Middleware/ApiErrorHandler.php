<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiErrorHandler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'data' => null,
            ], 404);
        } catch (HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'HTTP error',
                'data' => null,
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => null,
            ], 500);
        }
    }
}
