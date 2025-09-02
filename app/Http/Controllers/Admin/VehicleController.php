<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\VehicleExport;
use App\Imports\VehicleImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\Department;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'asc');
        $search = $request->get('search');

        $allowedColumns = ['id', 'model_name', 'vehicle_type', 'category', 'asset_number', 'manufacturing_year'];
        if (!in_array($sortColumn, $allowedColumns)) {
            $sortColumn = 'id';
        }

        $query = Vehicle::with('users');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                    ->orWhere('vehicle_type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('asset_number', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->orderBy($sortColumn, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return view('admin.vehicles.index', compact('vehicles', 'search'));
    }

    public function create()
    {
        $users = User::where('is_admin', false)->get()->groupBy('department.name');
        $departments = Department::orderBy('name')->get();
        return view('admin.vehicles.create', compact('users', 'departments'));
    }

    // ★★★★★ ここから修正 ★★★★★
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'category' => ['required', Rule::in(['車両', '重機'])],
            'asset_number' => 'nullable', // required を nullable に変更
            'manufacturing_year' => 'nullable|integer|min:1900', // ←【修正点】前の要素の末尾にカンマを追加
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $vehicle = Vehicle::create($validated);
        $vehicle->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機を登録しました。');
    }
    // ★★★★★ ここまで修正 ★★★★★

    public function edit(Vehicle $vehicle)
    {
        $users = User::where('is_admin', false)->get()->groupBy('department.name');
        $departments = Department::orderBy('name')->get();
        $assignedUserIds = $vehicle->users->pluck('id')->toArray();
        return view('admin.vehicles.edit', compact('vehicle', 'users', 'departments', 'assignedUserIds'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'category' => ['required', Rule::in(['車両', '重機'])],
            'asset_number' => 'nullable', // required を nullable に変更
            'manufacturing_year' => 'nullable|integer|min:1900',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $vehicle->update($validated);
        $vehicle->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機の情報を更新しました。');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機を削除しました。');
    }

    public function export()
    {
        return Excel::download(new VehicleExport, 'vehicles.csv');
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        try {
            Excel::import(new VehicleImport, $request->file('csv_file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = $failure->row() . '行目 ' . $failure->errors()[0];
            }
            return redirect()->back()->with('error', 'CSVインポートに失敗しました。<br>' . implode('<br>', $errors));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'CSVファイルのインポート中に予期せぬエラーが発生しました。<br><b>エラー内容:</b> ' . $e->getMessage());
        }
        return redirect()->route('admin.vehicles.index')->with('success', 'CSVファイルをインポートしました。');
    }
}
