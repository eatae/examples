<?php

namespace App\Classes\Helpers;

use App\Models\HumanResource\Poll;
use App\Models\HumanResource\PollAnswer;
use App\Models\HumanResource\Award;
use App\Models\Permission;
use App\Models\Role;
use Auth;
use App\Models\Structure\Company;
use App\Models\Structure\Structure;
use App\Models\Structure\Project;
use App\Models\Structure\Office;
use App\Models\Structure\Department;
use Illuminate\Support\Facades\DB;
use App\Models\HumanResource\Vacancy;
use App\Models\HumanResource\Candidate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\HumanResource\Invite;


class CustomHelper
{
    public static function getCountStructureLeftMenu()
    {
        return DB::select("SELECT * FROM (
                                    SELECT COUNT(id) as count, 'structure' as name FROM structures
                                    UNION ALL
                                    SELECT COUNT(id), 'company' as name FROM companies
                                    UNION ALL
                                    SELECT COUNT(id), 'project' as name FROM projects
                                    UNION ALL
                                    SELECT COUNT(id), 'office' as name FROM offices
                                    UNION ALL
                                    SELECT COUNT(id), 'department' as name FROM departments) T
                                ");
    }


    public static function getCountHrLeftMenu()
    {
        return DB::select("SELECT * FROM (
                                    SELECT COUNT(id) as count, 'vacancy' as name FROM vacancies
                                    UNION ALL
                                    SELECT COUNT(id), 'candidate' as name FROM candidates) T
                                ");
    }


    /**
     * Set current URL into session
     */
    public static function setCurrentBackUrl()
    {
        session()->put('back', URL::current());
    }

    /**
     * Set previous URL into session
     */
    public static function setPreviousBackUrl()
    {
        session()->put('back', session()->previousUrl());
    }


    /**
     * Get 'back' URL
     *
     * @param null $parentPage
     * @return mixed
     */
    public static function getBackUrl($parentPage = null)
    {
        /*if (null != $parentPage && URL::current() == session('back')) {
            return $parentPage;
        }*/
        $back_url = session()->pull('back', session()->previousUrl());
        if ($back_url == url()->current()) {
            return url('/');
        }
        return $back_url;
    }


    /**
     * Get Form fields
     *
     * @param string $name
     * @param array $data
     * @param string $divider
     * @return array
     */
    public static function getFormFields(string $name, array $data, string $divider = '__')
    {
        $result = [];
        foreach ( $data as $key => $value ) {
            $arrName = explode($divider, $key);
            if ($name == $arrName[0]) {
                $result[$arrName[1]] = $value;
            }
        }
        return $result;
    }


    /**
     * Search fields in Array
     *
     * @param array $source
     * @param array $keys
     * @return array
     */
    public static function searchNecessaryFields(array $source, array $keys)
    {
       return array_intersect_key($source, array_flip($keys));
    }


    /**
     * Compose Bindings parameters
     *
     * @param array $params
     * @param string $name
     * @return array
     */
    public static function composeBindingsParams(array $params, string $name)
    {
        $result = [];
        foreach ($params as $key => $val) {
            $bind_name = $name . '_' . $key;
            $result['keys'][] = ':' . $bind_name;
            $result['binds'][$bind_name] = $val;
        }
        $result['keys'] = implode(', ', $result['keys']);

        return $result;
    }




    /**
     * Percent
     *
     * @param int $full
     * @param int $share
     * @return float|int
     */
    public static function percent(int $full, int $share)
    {
        if (!$full || !$share) {
            return 0;
        }
        return abs(($share / $full) * 100);
    }

    /**
     * User has role or no
     */
    public static function actionUserCanPermission($permission)
    {
        if (Auth::check()) {
            $user = User::find(Auth::id());
            return $user->can($permission);
        } else return false;
    }











    /**
     * @return array
     */
    public static function permissionCountersForMenu()
    {
        return [
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ];
    }



    public static function pollCountersForMenu()
    {
        return [
            /* structures for poll-show-all */
            'structures' => Structure::count(),
            'polls' => Poll::count(),
            'poll-answers' => PollAnswer::count(),
        ];
    }

    /**
     * @return array
     */
    public static function hrCountersForMenu()
    {
        return [
            'vacancies' => Vacancy::count(),
            'candidates' => Candidate::count(),
            'users' => User::count(),
            'invites' => Invite::count(),
            'awards' => Award::count(),
        ];
    }
    /**
     * @return array
     */
    public static function structureCountersForMenu()
    {
        return [
            'structures' => Structure::count(),
            'companies' => Company::count(),
            'projects' => Project::count(),
            'offices' => Office::count(),
            'departments' => Department::count()
        ];
    }




}