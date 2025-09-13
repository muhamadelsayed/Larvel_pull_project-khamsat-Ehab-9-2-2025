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
 * @OA\Schema(
 * schema="UserTruck",
 * title="User Truck",
 * description="Details of a truck owned by a user",
 * required={"id", "user_id", "make", "model"},
 * @OA\Property(
 * property="id",
 * type="integer",
 * format="int64",
 * description="The unique ID of the truck"
 * ),
 * @OA\Property(
 * property="user_id",
 * type="integer",
 * format="int64",
 * description="The ID of the user who owns the truck"
 * ),
 * @OA\Property(
 * property="make",
 * type="string",
 * description="The truck's manufacturer"
 * ),
 * @OA\Property(
 * property="model",
 * type="string",
 * description="The truck's model"
 * ),
 * @OA\Property(
 * property="year",
 * type="integer",
 * description="The year the truck was made"
 * ),
 * @OA\Property(
 * property="license_plate",
 * type="string",
 * description="The truck's license plate number"
 * ),
 * @OA\Property(
 * property="is_active",
 * type="boolean",
 * description="Indicates if the truck is active and visible to others"
 * ),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the truck record was created"
 * ),
 * @OA\Property(
 * property="updated_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the truck record was last updated"
 * )
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
