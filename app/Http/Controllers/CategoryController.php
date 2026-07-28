<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ActivityLog;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('inventoryItems')->orderBy('name')->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:10|unique:categories',
            'description' => 'nullable|string',
        ]);
        $data['is_active'] = true;
        $cat = Category::create($data);
        ActivityLog::log('created', 'category', "Added category: {$cat->name}", $cat);
        return back()->with('success', "Category \"{$cat->name}\" added.");
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => "required|string|max:10|unique:categories,code,{$category->id}",
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $category->update($data);
        ActivityLog::log('updated', 'category', "Updated category: {$category->name}", $category);
        return back()->with('success', "Category updated.");
    }

    public function destroy(Category $category)
    {
        if ($category->inventoryItems()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated items.');
        }
        ActivityLog::log('deleted', 'category', "Deleted category: {$category->name}", $category);
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
