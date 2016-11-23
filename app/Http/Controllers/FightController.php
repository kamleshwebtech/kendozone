<?php

namespace App\Http\Controllers;

use App\Grade;
use App\Tree;
use Illuminate\Http\Request;

class FightController extends Controller
{
    /**
     * Display a listing of the fights.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $grades = Grade::pluck('name', 'id');
        $tournament = Tree::getTournament($request);

        return view('fights.index', compact('tournament','grades'));

    }
}