<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminTodo;
use App\Models\AdminTodoCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    //  TASKS
    // ═══════════════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $status      = $request->get('status', 'all');
        $priority    = $request->get('priority', 'all');
        $search      = $request->get('search');
        $categoryId  = $request->get('category', 'all');

        // All categories ordered by position for the sidebar
        $categories = AdminTodoCategory::orderBy('position')->orderBy('id')->get();

        // Build task query
        $query = AdminTodo::with('category')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($categoryId === 'none') {
            $query->whereNull('category_id');
        } elseif ($categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $todos = $query->paginate(config('default_pagination', 15))->appends($request->all());

        // Global counts (unfiltered by category)
        $counts = [
            'all'         => AdminTodo::count(),
            'pending'     => AdminTodo::pending()->count(),
            'in_progress' => AdminTodo::inProgress()->count(),
            'completed'   => AdminTodo::completed()->count(),
        ];

        // Uncategorized count for the sidebar link
        $uncategorizedCount = AdminTodo::whereNull('category_id')->count();

        // Active category object (if filtering by one)
        $activeCategory = ($categoryId !== 'all' && $categoryId !== 'none')
            ? $categories->firstWhere('id', $categoryId)
            : null;

        return view('admin-views.business-settings.todo.index', compact(
            'todos', 'counts', 'categories', 'status',
            'priority', 'search', 'categoryId', 'activeCategory',
            'uncategorizedCount'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high',
            'status'      => 'required|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
            'category_id' => 'nullable|exists:admin_todo_categories,id',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate('messages.validation_failed'));
            return back()->withErrors($validator)->withInput();
        }

        AdminTodo::create([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'status'      => $request->status,
            'due_date'    => $request->due_date,
            'category_id' => $request->category_id ?: null,
            'created_by'  => Auth::id(),
        ]);

        Toastr::success('Tarea creada exitosamente.');
        return back();
    }

    public function update(Request $request, AdminTodo $todo)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high',
            'status'      => 'required|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
            'category_id' => 'nullable|exists:admin_todo_categories,id',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate('messages.validation_failed'));
            return back()->withErrors($validator)->withInput();
        }

        $todo->update([
            'title'       => $request->title,
            'description' => $request->description,
            'priority'    => $request->priority,
            'status'      => $request->status,
            'due_date'    => $request->due_date,
            'category_id' => $request->category_id ?: null,
        ]);

        Toastr::success('Tarea actualizada exitosamente.');
        return back();
    }

    public function destroy(AdminTodo $todo)
    {
        $todo->delete();
        Toastr::success('Tarea eliminada exitosamente.');
        return back();
    }

    public function updateStatus(Request $request, AdminTodo $todo)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $todo->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado.']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CATEGORIES
    // ═══════════════════════════════════════════════════════════════════════════

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:80',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'  => 'required|string|max:80',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate('messages.validation_failed'));
            return back()->withErrors($validator, 'category')->withInput();
        }

        $maxPos = AdminTodoCategory::max('position') ?? 0;

        AdminTodoCategory::create([
            'name'     => $request->name,
            'color'    => $request->color,
            'icon'     => $request->icon,
            'position' => $maxPos + 1,
        ]);

        Toastr::success('Carpeta creada exitosamente.');
        return back();
    }

    public function updateCategory(Request $request, AdminTodoCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:80',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'  => 'required|string|max:80',
        ]);

        if ($validator->fails()) {
            Toastr::error(translate('messages.validation_failed'));
            return back()->withErrors($validator, 'category')->withInput();
        }

        $category->update([
            'name'  => $request->name,
            'color' => $request->color,
            'icon'  => $request->icon,
        ]);

        Toastr::success('Carpeta actualizada exitosamente.');
        return back();
    }

    public function destroyCategory(AdminTodoCategory $category)
    {
        // Tasks become uncategorized (FK is set null by DB constraint)
        $category->delete();
        Toastr::success('Carpeta eliminada. Las tareas quedaron sin categoría.');
        return redirect(url('admin/business-settings/to-do-list'));
    }

    public function reorderCategories(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $position => $id) {
            AdminTodoCategory::where('id', $id)->update(['position' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
