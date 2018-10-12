<?php

namespace App\Http\Controllers;

use App\Company;
use App\Department;
use App\Office;
use App\Structure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class StructureController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }



    public function actionShowAll()
    {
        $structures = Structure::getAll();
        return view('structure.show-all', compact('structures'));
    }



    public function actionCreate(Request $request)
    {
        /* edit */
        if ($request->isMethod('put')) {
            /* validate */
            $request->validate([
                'company_id' => 'required|integer',
                'office_id' => 'required|integer',
                'department_id' => 'required|integer',
            ]);

            $structure = Structure::getByFields($request);

            /* if structure exists */
            if ( !$structure->isEmpty() ) {
                return redirect()->back()->withErrors('This structure already exists.')->withInput();
            }

            $structure = new Structure($request->all());
            $structure->save();

            return redirect()->back()->with('success', ' Structure successfully added');
        }
        /* show forms */
        else {
            $companies = Company::all();
            $offices = Office::All();
            $departments = Department::all();

            return view('structure.create', compact('companies', 'offices', 'departments', 'test'));
        }


    }




    public function actionEdit(Request $request, Structure $structure = null)
    {
        /* edit */
        if ( $request->isMethod('put') ) {
            /* validate */
            $request->validate([
                'structure_id' => 'required|integer|exists:structures,id',
                'company_id' => 'required|integer',
                'office_id' => 'required|integer',
                'department_id' => 'required|integer',
            ]);

            $structure = Structure::getByFields($request);

            /* if structure exists */
            if ( !$structure->isEmpty() ) {
                return redirect()->back()->withErrors('This structure already exists.')->withInput();
            }

            $structure = Structure::find($request->post('structure_id'));

            $structure->fill($request->all());
            $structure->save();
            return redirect()->back()->with('success', ' Structure successfully edited');
        }
        /* show forms */
        elseif ( $request->isMethod('get') && null != $structure ) {

            $companies = Company::all();
            $offices = Office::All();
            $departments = Department::all();

            return view('structure.edit', compact('structure', 'companies', 'offices', 'departments'));
        }


    }


    public function actionDelete(Request $request)
    {
        $request->validate([
            'structure_id' => 'required|integer|exists:structures,id',
        ]);
        try{
            Structure::destroy( $request->post('structure_id') );
        } catch (QueryException $e) {
            return redirect( route('structure.show-all') )->withErrors('Низя! В этой структуре есть сотрудники.');
        }
        return redirect( route('structure.show-all') )->with('success', ' Structure successfully deleted.');
    }




    public function actionTest(Request $request, Structure $structure)
    {
        var_dump($structure);
    }



}
