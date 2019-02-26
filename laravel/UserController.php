<?php

namespace App\Http\Controllers;

use App\Exceptions\WarningException;
use App\Models\HumanResource\UserInfo;
use App\Models\Test;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\Structure\Company;
use App\Models\Structure\Office;
use App\Models\Structure\Structure;
use App\Models\HumanResource\Vacancy;
use App\Models\HumanResource\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Classes\Helpers\CustomHelper;
use App\Exceptions\UserException;



class UserController extends Controller
{

    protected $styles = ['user.css'];


    /**
     * View names for actionAjaxGetUsers()
     * @var array
     */
    protected static $ajaxViews = [
        'checkboxes' => 'ajax.show-users-checkboxes',
        'radio-buttons' => 'ajax.show-users-radio-buttons',
    ];


    /**
     * Check ajax view
     * @param string $view_name
     * @return bool
     */
    protected static function checkAjaxView($view_name) {
        if ( empty($view_name) || !array_key_exists($view_name, self::$ajaxViews )) {
            return false;
        }
        return true;
    }



    /**
     * Show all users
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowAll()
    {
        /* set back url */
        CustomHelper::setCurrentBackUrl();
        /* add JS file */
        $this->addScripts(['user-select.js']);
        /* counters for menu */
        $counters = CustomHelper::hrCountersForMenu();

        /* Select all users with relations are roles, structures and candidates */
        $users = User::with('role', 'roles', 'structures', 'candidate', 'info')->get();
        $roles = Role::all(); // Select all roles
        $companies = Company::all(); // Select all companies
        $offices = Office::all(); // Select all offices

        return view('user.show-all', compact('counters','users', 'roles', 'companies', 'offices'));
    }



    /**
     * Show all users from structures
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowAllFromStructures()
    {
        /* add JS file */
        $this->addScripts(['user-select.js']);
        /* counters for menu */
        $counters = CustomHelper::hrCountersForMenu();

        /* Select all users with relations are roles and structures */
        $users = User::with('role', 'roles', 'structures')->get();
        $roles = Role::all(); // Select all roles
        $companies = Company::all(); // Select all Companies
        $offices = Office::all(); // Select all offices

        return view('user.show-all-from-structure', compact('counters','users', 'roles', 'companies', 'offices'));
    }



    /**
     * show one user
     *
     * @param User $user
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowOne(User $user)
    {
        return view('user.show-one', compact('user'));
    }



    /**
     * show one user
     *
     * @param User $user
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionHrShowOne(User $user)
    {
        return view('user.show-one-hr', compact('user'));
    }



    /**
     * Show roles
     *
     * @param User $user
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowRoles(User $user)
    {
        CustomHelper::setCurrentBackUrl(); // Set current back url for a "back" button
        return view('user.show-roles', compact('user'));
    }



    /**
     * Change role
     *
     * @param User $user
     * @param Request $req
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionChangeRole(User $user, Request $req)
    {
        CustomHelper::setCurrentBackUrl(); // Set current back url for a "back" button

        /* If method is PUT */
        if ($req->isMethod('put')) {
            $user_id = $req->input('user'); // Get user id
            $role = $req->input('role'); // Get role
            $role_old = $req->input('role_old'); // Get role old

            /* Find all in role_user with that parametrs */
            $findUser = DB::select("SELECT * FROM role_user WHERE role_id='$role' AND user_id='$user_id'");

            /* If finded user is null */
            if (count($findUser) < 1) {
                /* Update role_user table and set our parametrs */
                DB::update("UPDATE role_user SET role_id='$role' WHERE user_id='$user_id' AND role_id='$role_old'");

                return redirect()->to('user/show-roles/' . $user_id)->with('success', 'User changed role successfully');
            } else {
                /* If do we find users, return error */
                return redirect()->to('user/change-role/'.$user_id.'?error=User has the role');
            }
        } else {
            $roles = Role::all(); // Select all roles
            return view('user.change-role', compact('user', 'roles'));
        }
    }



    /**
     * Add role
     *
     * @param User $user
     * @param Request $req
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionAddRole(User $user, Request $req)
    {
        CustomHelper::setCurrentBackUrl(); // Set current back url for a "back" button

        /* If method is PUT */
        if ($req->isMethod('put')) {
            $user_id = $req->input('user'); // Get user id
            $role = $req->input('role'); // Get role
            $status = $req->input('status'); // Get status

            /* Find all in role_user with that parametrs */
            $userFind = DB::select("SELECT * FROM role_user WHERE user_id='$user_id' AND role_id='$role'");

            /* If finded user is null */
            if (count($userFind) < 1) {
                /* Insert in role_user table our parametr */
                DB::insert("INSERT INTO role_user (user_id, role_id, status) VALUES('$user_id', '$role', '$status')");

                return redirect()->to('user/show-roles/'.$user_id)->with('success', 'User added role successfully');
            } else {
                /* If do we find users, return error */
                return redirect()->to('user/add-role/'.$user_id.'?error=User has the role');
            }
        } else {
            $roles = Role::all(); // Select all roles
            return view('user.add-role', compact('user', 'roles'));
        }
    }



    /**
     * Show role
     *
     * @param Role $role
     * @param User $user
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowRole(Role $role, User $user)
    {
        return view('user.show-role', compact('role', 'user'));
    }



    /**
     * Delete role
     *
     * @param Request $req
     *
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionDeleteRole(Request $req)
    {
        $user = $req->input('user'); // Get user
        $role = $req->input('role'); // Get role

        /* Select all in role_user with out parametrs and delete it */
        DB::table('role_user')->
            where('user_id', '=', $user)
            ->where('role_id', '=', $role)
            ->delete();
        /* Redirect with success */
        return redirect(url()->previous())->with('success', 'Role is delete successfully.');
    }




    /**
     * Ajax Get Users
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionAjaxGetUsers(Request $request)
    {

        $request->validate([
            'csrf' => 'nullable|string',
            'user_role' => 'nullable|string',
            'company_id' => 'nullable|integer',
            'office_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'view_name' => 'nullable|string',
            'excluded_users_ids' => 'nullable|string',
        ]);
        /* If do not find token */
        if ( $request->get('csrf') != Session::token() ) {
            /* Return error */
            return ['status' => 'error', 'message' => 'Token error'];
        }

        /* Get all user_id for structure roles */
        $user_role = ( empty($request->get('user_role')) ) ? '' : $request->get('user_role');
        $ids = User::getIdsByRoleStructure( $user_role, true, $request->get('company_id'), $request->get('office_id'), $request->get('office_id') );

        /* exclude users ids */
        if ( $request->filled('excluded_users_ids') ) {
            $excluded_users_ids = $request->get('excluded_users_ids');
            foreach($ids as $key => $id) {
                if (in_array($id, $excluded_users_ids)) {
                    unset($ids[$key]);
                }
            }
        }
        $users = User::find($ids);

        /* if view_name is empty - return JSON (into Header) */
        $view_name = $request->get('view_name');
        if ( !self::checkAjaxView( $view_name ) ) {
            return response()->json($users);
        }

        return view( self::$ajaxViews[$view_name], compact('users') );
    }






    /**
     * Ajax role sort
     *
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionAjaxRoleSort(Request $req)
    {
        $getRole = $req->input('role'); // Get role
        $getRole = !$getRole ? 'all' : $getRole; // If not role, get all

        $getCompany = $req->input('company'); // Get company
        $getCompany = !$getCompany ? 'all' : $getCompany; // If not company, get all

        $getOffice = $req->input('office'); // Get office
        $getOffice = !$getOffice ? 'all' : $getOffice; // If not office, get all

        $getDepartment = $req->input('department'); // Get department
        $getDepartment = !$getDepartment ? 'all' : $getDepartment; // If not department, get all

        /*
         * Select all users with candidates, roles and structures
         * where returned structures with companies, offices and departments
         */
        $getUsers = User::with(['candidate', 'info', 'role', 'roles', 'structures' => function($query) {
            return $query->with('company', 'office', 'department')->get();
        }])->get();

        $users = []; // Create array is empty

        // Get all users
        foreach($getUsers as $user) {
            $findRole = 0; // Role count
            $findCompany = 0; // Company count
            $findOffice = 0; // Office count
            $findDepartment = 0; // Department count

            /* If selected is all roles, findRole=0 */
            if ($getRole == "all") {
                $findRole = 0;
            } else {
                /* Select all roles from user */
                foreach($user->roles as $role) {
                    // if findRole is not null, continue
                    if ($findRole > 0) continue;

                    // If role id == getRole
                    if ($role->id == $getRole) {
                        $findRole = 1;
                    }
                }
            }

            /* If selected is all companies, findCompany=0 */
            if ($getCompany == "all") {
                $findCompany = 0;
            } else {
                /* Select all structures from user */
                foreach($user->structures as $structure) {
                    // if findCompany is not null, continue
                    if ($findCompany > 0) continue;

                    // If company id from structure == getCompany
                    if ($structure->company->id == $getCompany) {
                        $findCompany = 1;
                    }
                }
            }

            /* If selected is all offices, findCompany=0 */
            if ($getOffice == "all") {
                $findOffice = 0;
            } else {
                /* Get key, structures from user */
                foreach($user->structures as $key=>$structure) {
                    // if findOffice is not null, continue
                    if ($findOffice > 0) continue;

                    // If company id from structure == getCompany and office id from structure == getOffice
                    if ($structure->company->id == $getCompany && $structure->office->id == $getOffice) {
                        $findOffice = 1;
                    }
                }
            }

            /* If selected is all departments, findDepartment=0 */
            if ($getDepartment == "all") {
                $findDepartment = 0;
            } else {
                /* Get structures from user */
                foreach($user->structures as $structure) {
                    // if findDepartment is not null, continue
                    if ($findDepartment > 0) continue;

                    // If company id from structure == getCompany
                    // and if office id from structure == getOffice
                    // and if department id from structure == getDepartment
                    // findDepartment=1
                    if ($structure->company->id == $getCompany &&
                        $structure->office->id == $getOffice &&
                        $structure->department->id == $getDepartment) {
                        $findDepartment = 1;
                    }
                }
            }

            if (
                ($findRole || $getRole == "all")
                && ($findCompany || $getCompany == "all")
                && ($findOffice || $getOffice == "all")
                && ($findDepartment || $getDepartment == "all")
            ) {
                $users[$user->id]['id'] = $user->id;
                $users[$user->id]['name'] = "$user->name $user->last_name";
                $users[$user->id]['email'] = $user->email;
                $users[$user->id]['structures'] = $user->structures;
                $users[$user->id]['roles'] = $user->roles;
                $users[$user->id]['role'] = $user->role;
                $users[$user->id]['info'] = $user->info;
                $users[$user->id]['candidate'] = $user->candidate;
            }
        }

        return view('ajax.user-role-sort')->with('users', $users);
    }



    /**
     * Ajax role sort
     *
     * @param Request $req
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionAjaxRoleSortFromStructure(Request $req)
    {
        $getRole = $req->input('role'); // Get role
        $getRole = !$getRole ? 'all' : $getRole; // If not role, get all

        $getCompany = $req->input('company'); // Get company
        $getCompany = !$getCompany ? 'all' : $getCompany; // If not company, get all

        $getOffice = $req->input('office'); // Get office
        $getOffice = !$getOffice ? 'all' : $getOffice; // If not office, get all

        $getDepartment = $req->input('department'); // Get department
        $getDepartment = !$getDepartment ? 'all' : $getDepartment; // If not department, get all

        /*
         * Select all users with candidates, roles and structures
         * where returned structures with companies, offices and departments
         */
        $getUsers = User::with(['candidate', 'role', 'roles', 'structures' => function($query) {
            return $query->with('company', 'office', 'department')->get();
        }])->get();

        $users = []; // Create array is empty

        // Get all users
        foreach($getUsers as $user) {
            $findRole = 0; // Role count
            $findCompany = 0; // Company count
            $findOffice = 0; // Office count
            $findDepartment = 0; // Department count

            /* If selected is all roles, findRole=0 */
            if ($getRole == "all") {
                $findRole = 0;
            } else {
                /* Select all roles from user */
                foreach($user->roles as $role) {
                    // if findRole is not null, continue
                    if ($findRole > 0) continue;

                    // If role id == getRole
                    if ($role->id == $getRole) {
                        $findRole = 1;
                    }
                }
            }

            /* If selected is all companies, findCompany=0 */
            if ($getCompany == "all") {
                $findCompany = 0;
            } else {
                /* Select all structures from user */
                foreach($user->structures as $structure) {
                    // if findCompany is not null, continue
                    if ($findCompany > 0) continue;

                    // If company id from structure == getCompany
                    if ($structure->company->id == $getCompany) {
                        $findCompany = 1;
                    }
                }
            }

            /* If selected is all offices, findCompany=0 */
            if ($getOffice == "all") {
                $findOffice = 0;
            } else {
                /* Get key, structures from user */
                foreach($user->structures as $key=>$structure) {
                    // if findOffice is not null, continue
                    if ($findOffice > 0) continue;

                    // If company id from structure == getCompany and office id from structure == getOffice
                    if ($structure->company->id == $getCompany && $structure->office->id == $getOffice) {
                        $findOffice = 1;
                    }
                }
            }

            /* If selected is all departments, findDepartment=0 */
            if ($getDepartment == "all") {
                $findDepartment = 0;
            } else {
                /* Get structures from user */
                foreach($user->structures as $structure) {
                    // if findDepartment is not null, continue
                    if ($findDepartment > 0) continue;

                    // If company id from structure == getCompany
                    // and if office id from structure == getOffice
                    // and if department id from structure == getDepartment
                    // findDepartment=1
                    if ($structure->company->id == $getCompany &&
                        $structure->office->id == $getOffice &&
                        $structure->department->id == $getDepartment) {
                        $findDepartment = 1;
                    }
                }
            }

            if (
                ($findRole || $getRole == "all")
                && ($findCompany || $getCompany == "all")
                && ($findOffice || $getOffice == "all")
                && ($findDepartment || $getDepartment == "all")
            ) {
                $users[$user->id]['id'] = $user->id;
                $users[$user->id]['name'] = "$user->name $user->last_name";
                $users[$user->id]['email'] = $user->email;
                $users[$user->id]['structures'] = $user->structures;
                $users[$user->id]['roles'] = $user->roles;
                $users[$user->id]['role'] = $user->role;
                $users[$user->id]['candidate'] = $user->candidate;
            }
        }

        return view('ajax.user-role-sort-from-structure')->with('users', $users);
    }



    /**
     * Ajax office sort
     *
     * @param Request $req
     *
     * @return json
     */
    public function actionAjaxOfficeSort(Request $req)
    {
        $company = $req->input('company'); // Get company

        /* Select all structures with companies and offices where company_id=$company */
        $structures = Structure::with('company', 'office')
            ->where('company_id', '=', $company)
            ->get();

        $offices = [];

        foreach($structures AS $structure) {
            $offices[$structure->office->id]['id'] = $structure->office->id;
            $offices[$structure->office->id]['title'] = $structure->office->title;
        }

        return json_encode($offices);
    }



    /**
     * Ajax department sort
     *
     * @param Request $req
     *
     * @return json
     */
    public function actionAjaxDepartmentSort(Request $req)
    {
        $office = $req->input('office'); // Get office

        /* Select all structures with companies, offices and departments where office_id=$office */
        $structures = Structure::with('company', 'office', 'department')
            ->where('office_id', '=', $office)
            ->get();

        $departments = [];

        foreach($structures AS $structure) {
            $departments[$structure->department->id]['id'] = $structure->department->id;
            $departments[$structure->department->id]['title'] = $structure->department->title;
        }

        return json_encode($departments);
    }


    /**
     * Show account
     *
     * @param Request $request
     * @param User $user
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowAccount(Request $request, User $user)
    {
        return view('user.show-account', compact('user'));
    }


    /**
     * Show Edit account
     *
     * @param Request $request
     * @param User $user
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionEditAccount(Request $request, User $user)
    {
        return view('user.edit-account', compact('user'));
    }


    /**
     * Show Hr Edit account
     *
     * @param Request $request
     * @param User $user
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionHrEditAccount(Request $request, User $user)
    {
        return view('user.hr-edit-account', compact('user'));
    }



    /**
     * Store account
     *
     * @param Request $request
     *
     * @return redirect
     */
    public function actionStoreAccount(Request $request)
    {
        /*
         * user_id необходим для того если HR будут изменять данные пользователя
         */
        $request->validate([
            'user_id' => 'required|integer',
            'user__name' => 'required|string',
            'user__last_name' => 'required|string',
            'user__email' => 'required|email',
            'user-info__work_email' => 'email|nullable',
            'user-info__salary' => 'integer|nullable',
            'user-info__work_phone' => 'string|nullable',
            'user-info__personal_phone' => 'string|nullable',
            'user-info__gender' => 'string|in:male,female|nullable',
            'user-info__date_of_birth' => 'string|nullable',
            'user-info__citizenship' => 'string|nullable',
            'user-info__education' => 'string|nullable',
            'user-info__basecamp' => 'string|nullable',
            'user-info__telegram' => 'string|nullable',
            'user-info__skype' => 'string|nullable',
        ]);

        $allowedForUser = ['name', 'last_name', 'email'];
        /* for User model */
        $dataUser = CustomHelper::getFormFields('user', $request->all());
        $dataUser = CustomHelper::searchNecessaryFields($dataUser, $allowedForUser);
        /* for UserInfo model */
        $dataUserInfo = CustomHelper::getFormFields('user-info', $request->all());

        /* save User */
        $user = User::find($request->post('user_id'));
        $user->fill($dataUser)->save();

        $userInfo = UserInfo::firstOrCreate(['user_id' => $user->id]);
        /* fill userInfo */
        $userInfo->fill($dataUserInfo);
        /* store photo */
        if ( !empty($request->file('photo')) ) {
            $userInfo->photo = $this->storeUserPhoto($request->file('photo'));
        }
        /* save userInfo */
        $userInfo->save();

        return redirect()->back()->with('success', 'User info edited.');
    }



    public function actionHrDeleteUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);
        $user = User::findOrFail($request->post('user_id'));
        $user->delete();
        return redirect()->back()->with('success', 'User ' . $user->name . ' successfully deleted.');
    }


    /**
     * Store user photo
     *
     * @param UploadedFile $photo
     * @return string
     * @throws UserException
     */
    protected function storeUserPhoto(UploadedFile $photo)
    {
        $allowed_types = [
            image_type_to_mime_type(IMAGETYPE_GIF),
            image_type_to_mime_type(IMAGETYPE_PNG),
            image_type_to_mime_type(IMAGETYPE_JPEG),
            image_type_to_mime_type(IMAGETYPE_BMP)
        ];
        /* check size and mime types */
        if ( $photo->getSize() > 4193000 ) {
            throw new UserException('File must be no more than 4mb');
        } elseif ( !in_array($photo->getClientMimeType(), $allowed_types) ){
            throw new UserException('File must be an image');
        }

        $file_name = 'photo_'. auth()->user()->id . '.'. $photo->getClientOriginalExtension();
        $photo->storeAs('public/user_photo', $file_name);

        return $file_name;
    }


}