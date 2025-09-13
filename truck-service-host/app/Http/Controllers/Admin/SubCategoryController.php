<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    // عرض جميع التصنيفات الفرعية لتصنيف رئيسي معين
    public function index(Category $category)
    {
        $subCategories = $category->subCategories()->latest()->paginate(10);
        return view('admin.sub_categories.index', compact('category', 'subCategories'));
    }

    // عرض فورم إنشاء تصنيف فرعي جديد
    public function create(Category $category)
    {
        return view('admin.sub_categories.create', compact('category'));
    }

    // تخزين التصنيف الفرعي الجديد
    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('sub_categories', 'public');
        }

        $category->subCategories()->create($validated);
        return redirect()->route('admin.sub_categories.index', $category)->with('success', 'تم إنشاء التصنيف الفرعي بنجاح.');
    }
    
    // عرض فورم تعديل التصنيف الفرعي
    public function edit(Category $category, SubCategory $subCategory)
    {
        return view('admin.sub_categories.edit', compact('category', 'subCategory'));
    }

    // تحديث التصنيف الفرعي
    public function update(Request $request, Category $category, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            if ($subCategory->icon) {
                Storage::disk('public')->delete($subCategory->icon);
            }
            $validated['icon'] = $request->file('icon')->store('sub_categories', 'public');
        }
        
        $subCategory->update($validated);
        return redirect()->route('admin.sub_categories.index', $category)->with('success', 'تم تحديث التصنيف الفرعي بنجاح.');
    }

    // حذف التصنيف الفرعي
    public function destroy(Category $category, SubCategory $subCategory)
    {
        if ($subCategory->icon) {
            Storage::disk('public')->delete($subCategory->icon);
        }
        $subCategory->delete();
        return back()->with('success', 'تم حذف التصنيف الفرعي بنجاح.');
    }
}