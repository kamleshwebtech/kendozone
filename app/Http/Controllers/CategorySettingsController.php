<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategorySettings;
use App\Tournament;
use App\TournamentCategory;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

class CategorySettingsController extends Controller
{

    protected $currentModelName, $defaultSettings;

    public function __construct()
    {
        // Fetch the Site Settings object
//        $this->middleware('auth');
        $this->currentModelName = trans_choice('crud.categorySettings', 2);
        View::share('currentModelName', $this->currentModelName);
        $this->defaultSettings = CategorySettings::getDefaultSettings();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tournamentId, $categoryId)
    {
        $defaultSettings = $this->defaultSettings;
        return view("categories.create", compact('tournamentId', 'categoryId', 'defaultSettings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tournamentId,$categoryId)
    {
        $categoryTournament = TournamentCategory::where('tournament_id',$tournamentId)
                                                  ->where('category_id',$categoryId)->first();
        $request->request->add(['category_tournament_id' => $categoryTournament->id]);
//        dd($request);
        CategorySettings::create($request->all());
        flash("success", Lang::get('core.operation_successful'));
        return redirect("tournaments/$tournamentId/categories");
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($tournamentId, $categoryId, $settingId)
    {
//        $defaultSettings = $this->defaultSettings;

        $categorySetting = CategorySettings::findOrFail($settingId);
//        dd($categorySetting);
        return view("categories.edit", compact('tournamentId', 'categoryId', 'categorySetting')); //, 'defaultSettings'
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tournamentId, $categoryId, CategorySettings $categorySettings)
    {
        $data = $request->except('_method', '_token');
        $categorySettings->update($data);

        flash('success', trans('core.operation_successful'));
        return redirect("tournaments/$tournamentId/categories");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}