<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Services\ResponseService;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        ResponseService::noAnyPermissionThenRedirect(['role-list', 'role-create', 'role-edit', 'role-delete']);
        $roles = Role::where('name', '!=', 'Admin')->get();
        $permissions = Permission::all();
        return view('role.index', compact('roles', 'permissions'));
    }
    

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        ResponseService::noPermissionThenRedirect('role-create');
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);
    
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
            ];
            return response()->json($response);
        }
    
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'admin'
            ]);
    
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
    
            $response = [
                'error' => false,
                'message' => __('Role created successfully.'),
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Show the permissions for the specified role.
     */
    public function show($id)
    {
        ResponseService::noPermissionThenRedirect('role-view');
        $role = Role::findOrFail($id);
        
        $permissions = Permission::all()->groupBy('group_name');
        
        $users = Admin::role($role->name)->get();
        
        return view('role.show', compact('role', 'permissions', 'users'));
    }

     /**
     * Show the form for editing a role.
     */
    public function edit($id)
    {
        ResponseService::noPermissionThenRedirect('role-edit');
        $role = Role::with('permissions')->findOrFail($id);
    
        $permissions = Permission::all();
    
        return view('role.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        ResponseService::noPermissionThenRedirect('role-edit');
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $id],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $request->name,
            ]);

            $permissions = Permission::whereIn('id', $request->permissions)->get();
            
            $role->syncPermissions($permissions);

            DB::commit();

            return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        ResponseService::noPermissionThenRedirect('role-delete');
        try {
            $role = Role::findOrFail($id);
            
            if ($role->users()->count() > 0) {
                $response = [
                    'error' => true,
                    'message' => __('This role is assigned to staff members. Remove the role from staff first.'),
                ];
                return response()->json($response, 400);
            }
            
            $role->delete();
    
            $response = [
                'error' => false,
                'message' => __('Role deleted successfully.'),
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }
    
    /**
     * Get role list for datatable.
     */
    public function list(Request $request)
    {
        ResponseService::noPermissionThenRedirect('role-list');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $search = $request->input('search', '');

        $query = Role::where('name', '!=', 'Admin');
            
        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }
        
        // Get total count before pagination
        $total = $query->count();
        
        // Get paginated results
        $roles = $query->orderBy($sort, $order)
                     ->skip($offset)
                     ->take($limit)
                     ->get();
        
        $rows = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users->count()
            ];
        });
        
        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }
}
