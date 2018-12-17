<?php

namespace App\Helpers;

// Models
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


class CustomHelper
{
    
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
        return session()->pull('back', session()->previousUrl());
    }


}