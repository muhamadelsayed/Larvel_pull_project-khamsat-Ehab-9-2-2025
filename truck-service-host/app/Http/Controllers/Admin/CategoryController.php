<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'map_icon' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:1024', 
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('categories', 'public');
        }
        // الحفظ
        if ($request->hasFile('map_icon')) {
            $validated['map_icon'] = $request->file('map_icon')->store('categories/map_icons', 'public');
        }

        Category::create($validated);
        return redirect()->route('admin.categories.index')->with('success', 'تم إنشاء التصنيف بنجاح.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'map_icon' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:1024', 
        ]);

        if ($request->hasFile('icon')) {
            // حذف الأيقونة القديمة إذا تم رفع واحدة جديدة
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $request->file('icon')->store('categories', 'public');
        }
                // الحفظ
        if ($request->hasFile('map_icon')) {
            // حذف الأيقونة القديمة إذا وجدت
            if ($category->map_icon) {
                Storage::disk('public')->delete($category->map_icon);
            }
            $category->map_icon = $request->file('map_icon')->store('categories/map_icons', 'public');
        }

        $category->update($validated);
        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroy(Category $category)
    {
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }
        $category->delete();
        return back()->with('success', 'تم حذف التصنيف بنجاح.');
    }
}