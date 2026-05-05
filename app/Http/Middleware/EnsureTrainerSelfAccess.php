<?php

namespace App\Http\Middleware;

use App\Models\Trainer;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrainerSelfAccess
{
    /**
     * Allow admins unrestricted access, but limit trainers to their own trainer resource.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();

        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        if (!$actor instanceof User || $actor->role !== 'trainer') {
            return $next($request);
        }

        $actorTrainer = $actor->trainer;

        if (!$actorTrainer) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: trainer profile not found',
                'data' => null,
            ], 403);
        }

        $routeTrainer = $request->route('trainer');

        if ($routeTrainer instanceof Trainer) {
            $targetId = (int) $routeTrainer->id;
        } else {
            $targetId = (int) $routeTrainer;
        }

        if ($targetId !== (int) $actorTrainer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: trainers can only access their own trainer record',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}