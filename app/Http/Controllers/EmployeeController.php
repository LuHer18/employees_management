<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Info(
 *     title="API de Gestión de Empleados",
 *     version="1.0.0",
 *     description="API para la gestión de empleados"
 * )
 *
 */



class EmployeeController extends Controller
{


    /**
     * @OA\Get(
     *     path="/api/employees",
     *     tags={"Empleados"},
     *     summary="Obtener la lista de empleados",
     *     description="Obtiene la lista de empleados, permite filtrar por nombre y departamento.",
     *     operationId="getEmployees",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filtrar empleados por nombre",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="John"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="department_id",
     *         in="query",
     *         description="Filtrar empleados por ID de departamento",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de empleados obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="position", type="string", example="Desarrollador"),
     *                     @OA\Property(property="salary", type="number", format="float", example=50000.00),
     *                     @OA\Property(property="hire_date", type="string", format="date", example="2023-09-01"),
     *                     @OA\Property(property="department", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Recursos Humanos")
     *                     ),
     *                     @OA\Property(property="role", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Manager")
     *                     ),
     *                     @OA\Property(property="is_above_average", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost:8000/api/employees?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost:8000/api/employees?page=5"),
     *                 @OA\Property(property="prev", type="string", example=null),
     *                 @OA\Property(property="next", type="string", example="http://localhost:8000/api/employees?page=2")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */


    public function index(Request $request)
    {
        $query = Employee::selectRaw('employees.*,
                CASE
                WHEN department_id IS NULL THEN NULL
                WHEN salary > (SELECT AVG(salary) FROM employees WHERE department_id = employees.department_id)
                THEN true
                ELSE false
                END AS is_above_average,
                departments.name AS department_name,
                roles.role_name AS role_name')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id');

        if ($request->filled('name')) {
            $name = $request->input('name');

            $query->where('employees.name', 'like', '%' . addcslashes($name, '%_') . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('employees.department_id', $request->department_id);
        }

        $employees = $query->paginate(10);

        $employees->transform(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'position' => $employee->position,
                'salary' => $employee->salary,
                'hire_date' => Carbon::parse($employee->hire_date)->format('Y-m-d'), // Solo año, mes, día
                'department_id' => $employee->department_id,
                'role_id' => $employee->role_id,
                'is_above_average' => $employee->is_above_average,
                'department_name' => $employee->department_name,
                'role_name' => $employee->role_name,
            ];
        });

        return response()->json($employees, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/employees",
     *     summary="Crear un nuevo empleado",
     *     tags={"Empleados"},
     *     description="Crear un empleado con los datos proporcionados.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "position", "salary", "hire_date"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="position", type="string", example="Desarrollador Senior"),
     *             @OA\Property(property="salary", type="number", example=50000.50),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2024-09-15"),
     *             @OA\Property(property="department_id", type="integer", nullable=true, example=3),
     *             @OA\Property(property="role_id", type="integer", nullable=true, example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empleado creado con éxito",
     *         @OA\JsonContent(
     * type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="position", type="string", example="Desarrollador Senior"),
     *             @OA\Property(property="salary", type="number", example=50000.50),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2024-09-15"),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="role_id", type="integer", example=2))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos"),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */

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

    /**
     * @OA\Get(
     *     path="/api/employees/{id}",
     *     operationId="getEmployeeById",
     *     tags={"Empleados"},
     *     summary="Obtener un empleado por ID",
     *     description="Devuelve un empleado y sus detalles de departamento y rol.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del empleado",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empleado encontrado",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="id", type="integer", example=4),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="position", type="string", example="Desarrollador Senior"),
     *             @OA\Property(property="salary", type="number", example=50000.5),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2024-09-15"),
     *             @OA\Property(property="department_id", type="integer", example=3),
     *             @OA\Property(property="role_id", type="integer", example=2),
     *             @OA\Property(property="is_above_average", type="boolean", example=true),
     *             @OA\Property(property="department_name", type="string", example="Sales"),
     *             @OA\Property(property="role_name", type="string", example="Employee")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */


    public function show($id)
    {
        $employee = Employee::selectRaw('employees.*,
            CASE
            WHEN department_id IS NULL THEN NULL
            WHEN salary > (SELECT AVG(salary) FROM employees WHERE department_id = employees.department_id)
            THEN true
            ELSE false
            END AS is_above_average,
            departments.name AS department_name,
            roles.role_name AS role_name')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('roles', 'employees.role_id', '=', 'roles.id')->find($id);

        if (!$employee) {
            $data = [
                'message' => 'Empleado no encontrado',
                'status' => 404
            ];

            return response()->json($data, 404);
        }
        $response = [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'position' => $employee->position,
            'salary' => $employee->salary,
            'hire_date' => Carbon::parse($employee->hire_date)->format('Y-m-d'), // Solo año, mes, día
            'department_id' => $employee->department_id,
            'role_id' => $employee->role_id,
            'is_above_average' => $employee->is_above_average,
            'department_name' => $employee->department_name,
            'role_name' => $employee->role_name,
        ];

        return response()->json($response, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/employees/{id}",
     *     tags={"Empleados"},
     *     summary="Actualizar un empleado",
     *     description="Actualiza los detalles de un empleado existente.",
     *     operationId="updateEmployee",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del empleado a actualizar",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", example="juan.perez@example.com"),
     *             @OA\Property(property="position", type="string", example="Gerente"),
     *             @OA\Property(property="salary", type="number", example="55000"),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2023-08-01"),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empleado actualizado con éxito",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", example="juan.perez@example.com"),
     *             @OA\Property(property="position", type="string", example="Gerente"),
     *             @OA\Property(property="salary", type="number", example=55000),
     *             @OA\Property(property="hire_date", type="string", format="date", example="2023-08-01"),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="role_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos"),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */

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
        return response()->json($employee, 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     tags={"Empleados"},
     *     summary="Eliminar un empleado",
     *     description="Elimina un empleado basado en su ID.",
     *     operationId="deleteEmployee",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del empleado a eliminar",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empleado eliminado con éxito",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Empleado eliminado"),
     *             @OA\Property(property="status", type="string", example="200")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empleado no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Empleado no encontrado"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            $data = [
                'message' => 'Empleado no encontrado',
                'status' => 404
            ];

            return response()->json($data, 404);
        }

        $employee->delete();
        $data = [
            'message' => 'Empleado eliminado',
            'status' => '200'
        ];

        return response()->json($data, 200);
    }
}
