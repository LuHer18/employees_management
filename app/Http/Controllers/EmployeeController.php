<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{

    //getAll() and filter by name or department
    public function index(Request $request)
    {
        $query = Employee::with('department', 'role');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->paginate(10);

        return response()->json($employees, 200);
    }

    //create employee
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric',
            'hire_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'message' => "Error en la validación de datos",
                'errors' => $validator->errors(),
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        $employee = Employee::create($request->all());
        return response()->json($employee, 201);
    }

    public function show($id)
    {
        $employee = Employee::with('department', 'role')->find($id);
        if (!$employee) {
            $data = [
                'message' => 'Empleado no encontrado',
                'status' => 404
            ];

            return response()->json($data, 404);
        }

        return response()->json($employee, 200);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            $data = [
                'message' => 'Empleado no encontrado',
                'status' => 404
            ];

            return response()->json($data, 404);
        }

        //TODO refactorizar
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric',
            'hire_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'message' => "Error en la validación de datos",
                'errors' => $validator->errors(),
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        $employee->update($request->all());
        return response()->json($employee);
    }
}
