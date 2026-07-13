<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }
        return $query
            ->orderByDesc('id')
            ->paginate(min((int) $request->input('per_page', 10), 100));
    }
    public function select(Request $request)
    {
        $query = Unit::query();

        if ($request->boolean('active_only')) {
            $query->where('status', 'active');
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'symbol' => 'required|max:50',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Vui lòng nhập tên đơn vị',
            'symbol.required' => 'Vui lòng nhập ký hiệu',
        ]);
        return Unit::create($request->all());
    }

    public function show($id)
    {
        return Unit::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'symbol' => 'required|max:50',
        ], [
            'name.required' => 'Vui lòng nhập tên đơn vị',
            'symbol.required' => 'Vui lòng nhập ký hiệu',
        ]);

        $unit->update($request->only([
            'name',
            'symbol',
            'status'
        ]));

        return response()->json([
            'message' => 'Cập nhật thành công'
        ]);
    }
    public function toggleStatus($id)
    {
        $unit = Unit::findOrFail($id);

        $unit->status =
            $unit->status === 'active'
            ? 'inactive'
            : 'active';

        $unit->save();

        return response()->json([
            'status' => $unit->status
        ]);
    }
}
