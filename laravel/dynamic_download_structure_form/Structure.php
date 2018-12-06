<?php

namespace App\Models\Structure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Structure extends Model
{
    protected $table = 'structures';
    
    public static function actionGetStructureDependancies()
    {
        return self::buildArrayForSelects ();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Structure\Company');
    }


    public function office()
    {
        return $this->belongsTo('App\Models\Structure\Office');
    }


    public function department()
    {
        return $this->belongsTo('App\Models\Structure\Department');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'structure_user');
    }





    /**
     * Get by members
     *
     * @param int|null $company_id
     * @param int|null $office_id
     * @param int|null $department_id
     *
     * @return array
     */
    public static function getIdsByMembers(int $company_id = null, int $office_id = null, int $department_id = null)
    {
        $query = DB::table('structures');
        if ($company_id) {
            $query->where('company_id', $company_id);
        }
        if ($office_id) {
            $query->where('office_id', $office_id);
        }
        if ($department_id) {
            $query->where('department_id', $department_id);
        }
        $ids = $query->pluck('id')->toArray();

        return $ids;
    }





    /**
     * Get Project
     *
     * @param int|null $company_id
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getProject(int $company_id = null)
    {
        $result= [];

        if ($company_id)
        {
            $projects = Project::where('company_id', $company_id)->get();
        }
        else
        {
            $projects = Project::all();
        }

        foreach ($projects as $project)
        {
            $result[$project->id]['title'] = $project->title;
            $result[$project->id]['id'] = $project->id;
        }

        return $result;
    }



    // Должен вернуть компании с проетками
    // Потестировать, если что-то не так мне сообщить
    // если всё гуд - очисти мой метод buildArrayForSelects()
    public static function withAllRelations(array $ids)
    {
        return self::with(['company.projects', 'office', 'department', 'users'])->find($ids);
    }








    /**
     *
     * Build Array
     * 
     * @param int|null $company_id
     * @param int|null $office_id
     * @param int|null $department_id
     * @return array
     */
    public static function buildArrayForSelects(int $company_id = null, int $office_id = null, int $department_id = null)
    {
        $ids = self::getIdsByMembers($company_id, $office_id, $department_id);
        $structures = self::with(['company', 'office', 'department'])->find($ids);
        $result = [];

        foreach ($structures as $structure) {
            // !!! Почему лезешь в мой метод и изменяешь его?
            $projects = self::getProject($structure->company->id);

            $result[$structure->company->id]['title'] = $structure->company->title;
            $result[$structure->company->id]['id'] = $structure->company->id;
            $result[$structure->company->id]['projects'] = $projects;
            $result[$structure->company->id]['offices'][$structure->office->id]['title'] = $structure->office->title;
            $result[$structure->company->id]['offices'][$structure->office->id]['id'] = $structure->office->id;
            $result[$structure->company->id]['offices'][$structure->office->id]['departments'][$structure->department->id]['title'] = $structure->department->title;
            $result[$structure->company->id]['offices'][$structure->office->id]['departments'][$structure->department->id]['id'] = $structure->department->id;
        }

        return $result;
    }




 

}
