<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\Vacancy;
use App\Models\HumanResource\Candidate;
use App\Models\Role;
use App\Models\Structure\Structure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

use App\Helpers\CustomHelper;
use App\Exceptions\UserException;

class VacancyController extends Controller
{

    protected $styles = ['human-resource.css'];


    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        parent::__construct();
    }

    /**
     * Show All
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowAll()
    {
        $counters = [
            'vacancies' => Vacancy::count(),
            'candidates' => Candidate::count(),
            'invites' => 3,
        ];

        $vacancies = Vacancy::with('role', 'structure.company', 'structure.office', 'structure.department')->get()
            ->sortByDesc('created_at');
        return view('vacancy.show-all', compact('vacancies', 'counters'));
    }


    /**
     * Show One
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowOne($id)
    {
        $counters = [
            'vacancies' => Vacancy::count(),
            'candidates' => Candidate::count(),
            'invites' => 3,
        ];
        $vacancy = Vacancy::with('structure', 'creator_user', 'closer_user', 'role', 'candidates')->find($id);
        return view('vacancy.show-one', compact('vacancy', 'counters'));
    }



    /**
     * Show create form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionCreate()
    {
        /* add JS file */
        $this->addScripts(['vacancy-create.js']);

        $roles = Role::all();
        $priorities = Vacancy::PRIORITY_VALUES;
        $structures = json_encode(Structure::buildArrayForSelects());

        $data = $roles;
        return view('vacancy.create', compact('data', 'roles', 'priorities', 'structures'));
    }


    /**
     * Create
     *
     * Bind relations vacancy_user
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function actionStore(Request $request)
    {
        /* validation */
        if ( $request->isMethod('put') ) {
            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'priority' => 'required|string',
                'creator_user_id' => 'required|exists:users,id',
                'company_id' => 'required|exists:companies,id',
                'office_id' => 'required|exists:offices,id',
                'department_id' => 'required|exists:departments,id',
                'comment' => 'nullable|string',
            ]);

            /* select structure */
            $structure = Structure::where([
                'company_id' => $request->post('company_id'),
                'office_id' => $request->post('office_id'),
                'department_id' => $request->post('department_id'),
            ])->first();

            /* check structure */
            if ( empty($structure) ) {
                return redirect()->back()->withErrors('Error: Structure is not exists.');
            }
            /* fill vacancy */
            $vacancy = new Vacancy($request->all());
            $vacancy->status = Vacancy::STATUS_EMPTY;
            $vacancy->structure_id = $structure->id;
            $vacancy->fillComment('create', $request->post('comment') ); // add comment
            /* save vacancy */
            $vacancy->save();

            /* bind vacancy users */
            foreach ( $request->all() as $key => $value ) {
                if ( explode('_', $key)[0] == 'user' ) {
                    $this->bindVacancyUser($value, $vacancy->id);
                }
            }
            /* success message */
            $msg = 'Vacancy '. $vacancy->role->name .' successfully added.';

            return redirect(route('human-resource.vacancy.show-all'))->with('success', $msg);
        }
    }



    /**
     * Show Edit form
     *
     * @param Request $request
     * @param Vacancy $vacancy
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionEdit(Request $request, Vacancy $vacancy)
    {
        $priorities = Vacancy::PRIORITY_VALUES;
        $statuses = [Vacancy::STATUS_OPEN, Vacancy::STATUS_CLOSE_CANDIDATE_FOUND, Vacancy::STATUS_CLOSE];

        return view('vacancy.edit', compact('vacancy', 'priorities', 'statuses'));
    }


    /**
     * Update
     *
     * @param Request $request
     * @param Vacancy $vacancy
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actionUpdate(Request $request, Vacancy $vacancy)
    {
        if ( $request->isMethod('put') ) {
            $request->validate([
                'priority' => 'nullable|string',
                'status' => 'nullable|string',
                'comment' => 'nullable|string',
            ]);
            $vacancy->priority = $request->post('priority') ?: $vacancy->priority;
            $vacancy->status = $request->post('status') ?: $vacancy->status;
            $vacancy->fillComment('edit', $request->post('comment'));
            $vacancy->save();
            $msg = 'Vacancy '. $vacancy->role->name .' successfully edited.';

            return redirect(route('human-resource.vacancy.show-all'))->with('success', $msg);
        }
    }


    /**
     *
     * Close Vacancy
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws UserException
     */
    public function actionClose(Request $request)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);

        $vacancy = Vacancy::with('candidates')->get()->find($request->post('vacancy_id'));

        if ( !$vacancy ) {
            throw new UserException('Warning: vacancy does not exist.');
        }
        /* close candidates */
        if ( $vacancy->candidates->isNotEmpty() ) {
            foreach ($vacancy->candidates as $candidate) {
                $candidate->fillComment('close vacancy', $request->post('comment'));
                $candidate->setCloseStatus();
                $candidate->save();
                $candidate->delete();
            }
        }
        $vacancy->fillComment('close', $request->post('comment'));
        $vacancy->save();
        $vacancy->delete();

        $msg = 'Vacancy '. $vacancy->role->name .' successfully closed.';

        return redirect()->back()->with('success', $msg);
    }


    /**
     * Delete Vacancy
     *
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     * @throws UserException
     */
    public function actionDelete(Request $request)
    {
        $request->validate([
            'vacancy_id' => 'required|integer'
        ]);
        $vacancy = Vacancy::with('candidates')->get()->find( $request->post('vacancy_id') );
        if ( !$vacancy ) {
            throw new UserException('Warning: vacancy does not exist.');
        }

        if ( $vacancy->candidates->isNotEmpty() ) {
            return redirect()->back()->withErrors('Warning: it is not possible to delete a vacancy has candidates.');
        }
        
        $vacancy->forceDelete();
        $msg = 'Vacancy '. $vacancy->role->name .' successfully delete.';

        return redirect(route('human-resource.vacancy.show-all'))->with('success', $msg);
    }



    /**
     * @param $vacancy_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function actionShowCandidates($vacancy_id)
    {
        /* back url */
        CustomHelper::setCurrentBackUrl();

        $counters = [
            'vacancies' => Vacancy::count(),
            'candidates' => Candidate::count(),
            'invites' => 3,
        ];
        $vacancy = Vacancy::with(
            'role',
            'structure.company',
            'structure.office',
            'structure.department',
            'candidates.status',
            'creator_user'
            )->find($vacancy_id);
        $statuses = DB::table('candidate_statuses')->whereNotIn('title', ['close'])->get();

        return view('vacancy.show-candidates', compact('counters','vacancy', 'statuses'));
    }


    /**
     * Show HRs
     *
     * @param $vacancy_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws UserException
     */
    public function actionShowHrs($vacancy_id)
    {
        /* set back url */
        CustomHelper::setCurrentBackUrl();

        $counters = [
            'vacancies' => Vacancy::count(),
            'candidates' => Candidate::count(),
            'invites' => 3,
        ];

        $vacancy = Vacancy::with(
            'structure.company',
            'structure.office',
            'structure.department',
            'related_users.structures.company',
            'related_users.structures.office',
            'related_users.structures.department',
            'related_users.roles'
        )->find($vacancy_id);

        if ( !$vacancy ) {
            return redirect()->route('human-resource.vacancy.show-all')->withErrors('Warning: vacancy does not exist.');
        }

        return view('vacancy.show-hrs', compact('vacancy', 'counters'));
    }





    /**
     * Bind HRs
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actionBindHrs(Request $request)
    {
        /* add js file */
        $this->addScripts(['vacancy-bind-hrs.js']);

        /* validate */
        $request->validate([
            'vacancy_id' => 'required|exists:vacancies,id',
        ]);

        $vacancy = Vacancy::find($request->input('vacancy_id'));
        $related_users_ids =  json_encode($vacancy->related_users->keyBy('id')->keys());

        /*
         * show form
         */
        if ( $request->isMethod('get') ) {
            $structures = json_encode(Structure::buildArrayForSelects());
            return view('vacancy.bind-hrs', compact('vacancy', 'structures', 'related_users_ids'));
        }
        /*
         * bind users
         */
        elseif ( $request->isMethod('put') ) {

            /* bind vacancy users */
            foreach ( $request->all() as $key => $value ) {
                if ( explode('_', $key)[0] == 'user' ) {
                    $this->bindVacancyUser($value, $vacancy->id);
                }
            }

            /* success message */
            $msg = 'Hrs successfully added.';

            return redirect()->to(CustomHelper::getBackUrl())->with('success', $msg);
        }

    }


    /**
     * Unbind Hr
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws UserException
     */
    public function actionUnbindHrs(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:vacancy_user,user_id',
            'vacancy_id' => 'required|exists:vacancy_user,vacancy_id',
        ]);

        $vacancy = Vacancy::find($request->post('vacancy_id'));
        if (!$vacancy) {
            throw new UserException('Warning: vacancy is closed');
        }
        $vacancy->related_users()->detach($request->post('user_id'));

        return redirect(url()->previous())->with('success', 'Hr successfully unbind.');
    }





    /**
     * Bind Users
     *
     * @param $user_id
     * @param $vacancy_id
     */
    protected function bindVacancyUser($user_id, $vacancy_id)
    {
        Validator::make( compact('vacancy_id', 'user_id'), [
            'vacancy_id' =>  'required|exists:vacancies,id',
            'user_id' =>  'required|exists:users,id',
        ])->validate();
        /* set in DB */
        Vacancy::bindUser($user_id, $vacancy_id);
    }








    public function test(Request $request)
    {
        /*DB::enableQueryLog();
        $vacancy = Vacancy::first();
        dd(DB::getQueryLog());*/

        //dd(auth()->user()->roles);

        //session()->put('foo', 'bar2');
        //session()->save();
        //dd(session());



        if ($request->isMethod('get')) {
            CustomHelper::setBackUrl();
            return view('vacancy.test');
        }

        elseif ($request->isMethod('put')) {
            $msg = 'Redirect message';
            return redirect()->to(CustomHelper::getBackUrl())->with('MESSAGE', 'MESSAGE');
        }
    }



}
