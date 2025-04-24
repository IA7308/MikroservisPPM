<?php

namespace App\Http\Controllers;

use App\Services\ProfileFacultyService;
use Illuminate\Http\Request;

class ProfileFacultyController extends Controller
{
    protected $ProfileFacultyService;

    public function __construct(ProfileFacultyService $ProfileFacultyService)
    {
        $this->ProfileFacultyService = $ProfileFacultyService;
    }

    public function index()
    {
        return $this->ProfileFacultyService->getAllFaculty();
    }
}
