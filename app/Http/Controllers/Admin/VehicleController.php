<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Exports\VehicleExport;
use App\Imports\VehicleImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->query('filters', []);

        $query = Vehicle::with('users.department');

        if (!empty($filters['vehicle_type'])) {
            $query->whereIn('vehicle_type', $filters['vehicle_type']);
        }
        if (!empty($filters['maker'])) {
            $query->whereIn('maker', $filters['maker']);
        }
        if (!empty($filters['category'])) {
            $query->whereIn('category', $filters['category']);
        }

        if (!empty($filters['department'])) {
            $query->whereHas('users', function ($q) use ($filters) {
                $q->whereIn('department_id', $filters['department']);
            });
        }

        $vehicles = $query->get();

        $filterOptions = [
            'vehicle_types' => Vehicle::whereNotNull('vehicle_type')->distinct()->pluck('vehicle_type'),
            'makers' => Vehicle::whereNotNull('maker')->distinct()->pluck('maker'),
            'categories' => Vehicle::whereNotNull('category')->distinct()->pluck('category'),
            'departments' => Department::has('users.vehicles')->get(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'filters', 'filterOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::with('users')->whereHas('users')->get();
        $usersWithoutDepartment = User::whereNull('department_id')->get();
        $vehicle = new Vehicle();
        return view('admin.vehicles.create', compact('departments', 'usersWithoutDepartment', 'vehicle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255',
            'maker' => 'nullable|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'category' => 'required|string',
            'asset_number' => 'nullable|numeric',
            'manufacturing_year' => 'nullable|numeric|digits:4',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $vehicle = Vehicle::create($validated);
        $vehicle->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機を登録しました。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $departments = Department::with('users')->whereHas('users')->get();
        $usersWithoutDepartment = User::whereNull('department_id')->get();

        $allUsersForFiltering = User::orderBy('name')->get(['id', 'name', 'department_id']);

        return view('admin.vehicles.edit', compact('vehicle', 'departments', 'usersWithoutDepartment', 'allUsersForFiltering'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255',
            'maker' => 'nullable|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'category' => 'required|string',
            'asset_number' => 'nullable|numeric',
            'manufacturing_year' => 'nullable|numeric|digits:4',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $vehicle->update($validated);
        $vehicle->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('success', '車両・重機を削除しました。');
    }

    public function export()
    {
        return Excel::download(new VehicleExport, 'vehicles.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = $request->file('file');

        $sjis_content = file_get_contents($file->getRealPath());
        $utf8_content = mb_convert_encoding($sjis_content, 'UTF-8', 'SJIS-win');

        $temp_file_path = tempnam(sys_get_temp_dir(), 'csv_import_');
        file_put_contents($temp_file_path, $utf8_content);

        try {
            Excel::import(new VehicleImport, $file, \Maatwebsite\Excel\Excel::CSV);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "{$failure->row()}行目: " . implode(', ', $failure->errors());
            }
            return redirect()->back()->withErrors($errorMessages);
        } catch (\Exception $e) {
            Log::error('CSV Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'CSVファイルのインポート中に予期せぬエラーが発生しました。ファイル形式や内容を確認してください。');
        } finally {
            if (file_exists($temp_file_path)) {
                unlink($temp_file_path);
            }
        }

        return redirect()->route('admin.vehicles.index')->with('success', 'CSVファイルをインポートしました。');
    }
}
