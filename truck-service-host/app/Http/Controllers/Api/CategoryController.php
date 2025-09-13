<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
/**
 * @group Public Data
 * APIs for retrieving public data like categories.
 */

class CategoryController extends Controller
{

/**
 * @OA\Schema(
 * schema="SubCategory",
 * title="SubCategory",
 * description="A sub-category object",
 * required={"id", "name"},
 * @OA\Property(
 * property="id",
 * type="integer",
 * format="int64",
 * description="The unique ID of the sub-category"
 * ),
 * @OA\Property(
 * property="name",
 * type="string",
 * description="The name of the sub-category"
 * ),
 * @OA\Property(
 * property="category_id",
 * type="integer",
 * format="int64",
 * description="The ID of the parent category"
 * )
 * )
 */
    public function index()
    {
        $categories = Category::with('subCategories')->latest()->get();
    return CategoryResource::collection($categories);
    }
}