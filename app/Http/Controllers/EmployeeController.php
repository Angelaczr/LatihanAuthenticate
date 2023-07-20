<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

use Illuminate\Support\Facades\DB;
use PDF;





class EmployeeController extends Controller
{

    public function index()
    {
        $pageTitle = 'Employee List';

        return view('employee.index', compact('pageTitle'));

        // $pageTitle = 'Employee List';

        // // ELOQUENT
        // $employees = Employee::all();
        // return view('employee.index', [
        //     'pageTitle' => $pageTitle,
        //     'employees' => $employees
        // ]);

    }


    public function create()
    {
        $pageTitle = 'Create Employee';
        // ELOQUENT
        $positions = Position::all();
        return view('employee.create', compact('pageTitle', 'positions'));
    }



    public function store(Request $request)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get File
        $file = $request->file('cv');

        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');
        }

        // ELOQUENT
        $employee = new Employee;
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }

        $employee->save();
        Alert::success('Added Successfully', 'Employee Data Added Successfully.');

        return redirect()->route('employees.index');

    }

    public function show(string $id)
    {
        $pageTitle = 'Employee Detail';

        //ELLOQUENT
        $employee = Employee::find($id);

        //tampilan view
        return view('employee.show', compact('pageTitle', 'employee'));
    }


    public function edit(string $id)
    {
        //
        $pageTitle = 'Edit Employee';
        // ELOQUENT
        $positions = Position::all();
        $employee = Employee::find($id);
        return view(
            'employee.edit',
            compact(
                'pageTitle',
                'positions',
                'employee'
            )
        );
    }


    public function update(Request $request, $id)
    {
        //
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get File
        $file = $request->file('cv');

        if ($file != null) {
            $employee = Employee::find($id);
            $encryptedFilename = 'public/files/' . $employee->encrypted_filename;
            Storage::delete($encryptedFilename);
        }

        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // store
            $file->store('public/files');
        }

        $employee->save();
        Alert::success('Changed Successfully', 'Employee Data Changed Successfully.');

        return redirect()->route('employees.index');

        // }
        // // ELOQUENT
        // $employee = Employee::find($id);
        // $employee->firstname = $request->input('firstName');
        // $employee->lastname = $request->input('lastName');
        // $employee->email = $request->input('email');
        // $employee->age = $request->input('age');
        // $employee->position_id = $request->input('position');

        // if ($file != null) {
        //     $employee->original_filename = $originalFilename;
        //     $employee->encrypted_filename = $encryptedFilename;
        // }
    }



    public function destroy(string $id)
    {
        // ELOQUENT
        $employee = Employee::find($id);
        $encryptedFilename = 'public/files/' . $employee->encrypted_filename;
        Storage::delete($encryptedFilename);

        // Model
        Employee::find($id)->delete();
        Alert::success('Deleted Successfully', 'Employee Data Deleted Successfully.');

        return redirect()->route('employees.index');
    }



    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/' . $employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname . '_' . $employee->lastname . '_cv.pdf');

        if (Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }


    public function getData(Request $request)
    {
        $employees = Employee::with('position');

        if ($request->ajax()) {
            return datatables()->of($employees)
                ->addIndexColumn()
                ->addColumn('actions', function ($employee) {
                    return view('employee.actions', compact('employee'));
                })
                ->toJson();
        }
    }


}
