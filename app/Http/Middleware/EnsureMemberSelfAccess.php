<?php

namespace App\Http\Middleware;

use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberSelfAccess
{
    /**
     * Allow admins/trainers, but limit members to their own resource by id.
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

        // Non-member principals (admin/trainer users) are not self-restricted here.
        if (!$actor instanceof Member) {
            return $next($request);
        }

        $routeMember = $request->route('member');

        if ($routeMember instanceof Member) {
            $targetId = (int) $routeMember->id;
        } else {
            $targetId = (int) $routeMember;
        }

        if ($targetId !== (int) $actor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: members can only access their own record',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
