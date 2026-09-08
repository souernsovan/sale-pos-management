<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\Audit;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create categories'), 403);

        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        abort_unless($request->user()->can('create categories'), 403);

        Category::create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, Category $category)
    {
        abort_unless($request->user()->can('edit categories'), 403);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        abort_unless($request->user()->can('edit categories'), 403);

        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()->can('delete categories'), 403);

        Audit::log('categories', "Deleted category \"{$category->name}\"", $category, event: 'deleted');

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category deleted.');
    }
}
