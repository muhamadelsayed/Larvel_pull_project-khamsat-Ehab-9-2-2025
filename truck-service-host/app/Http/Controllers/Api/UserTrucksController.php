<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserTruckResource;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @group User Profile
 * APIs for viewing other users' public data.
 */
class UserTrucksController extends Controller
{
     /**
     * @OA\PathItem(
     *      path="/api/users/{user}/trucks",
     *      @OA\Get(
     *          operationId="getUserTrucks",
     *          tags={"User Profile"},
     *          summary="Get a specific user's active trucks",
     *          description="Requires authentication. Returns a list of a user's publicly active trucks.",
     *          security={{"bearerAuth":{}}},
     *          @OA\Parameter(name="user", in="path", required=true, description="ID of the user", @OA\Schema(type="integer")),
     *          @OA\Response(response=200, description="Successful operation"),
     *          @OA\Response(response=401, description="Unauthenticated"),
     *      )
     * )
     */
    public function index(User $user)
    {
        // جلب الشاحنات النشطة فقط لهذا المستخدم مع تحميل العلاقات اللازمة
        $trucks = $user->trucks()
                       ->where('status', 'active')
                       ->with(['category', 'subCategory', 'images'])
                       ->latest()
                       ->get();
        
        return UserTruckResource::collection($trucks);
    }
}
