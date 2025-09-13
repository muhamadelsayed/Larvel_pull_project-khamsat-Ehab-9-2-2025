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
     * @OA\PathItem(
     *      path="/api/categories",
     *      @OA\Get(
     *          operationId="getCategories",
     *          tags={"Public Data"},
     *          summary="Get all categories with their sub-categories",
     *          description="Returns a list of main categories, each containing its sub-categories.",
     *          @OA\Response(
     *              response=200,
     *              description="Successful operation"
     *          )
     *      )
     * )
     */
    public function index()
    {
        $categories = Category::with('subCategories')->latest()->get();
    return CategoryResource::collection($categories);
    }
}