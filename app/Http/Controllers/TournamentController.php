<?php

namespace App\Http\Controllers;

use App\Category;
use App\ChampionshipSettings;
use App\Championship;
use App\Exceptions\InvitationNeededException;
use App\Grade;
use App\Http\Requests\TournamentRequest;
use App\Http\Requests\VenueRequest;
use App\Tournament;
use App\TournamentLevel;
use App\Venue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Webpatser\Countries\Countries;


class TournamentController extends Controller
{
    protected $currentModelName;

    public function __construct()
    {
        $this->middleware('ownTournament', ['except' => ['index', 'show','register']]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $currentModelName = trans_choice('core.tournament', 2);

        if (Auth::user()->isSuperAdmin()) {
            $tournaments = Tournament::with('owner')->orderBy('created_at', 'desc')->paginate(config('constants.PAGINATION'));
        } else {
            $tournaments = Auth::user()->tournaments()->with('owner')->orderBy('created_at', 'desc')->paginate(config('constants.PAGINATION'));
        }
        $title = trans('core.tournaments_created');
        return view('tournaments.index', compact('tournaments', 'currentModelName', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currentModelName = trans_choice('core.tournament', 1);
        $levels = TournamentLevel::pluck('name', 'id');
        $categories = Category::take(10)->orderBy('id', 'asc')->pluck('name', 'id');
        $tournament = new Tournament();
        $rules = config('options.rules');
        $rulesCategories = (new Category)->getCategorieslabelByRule();
        return view('tournaments.create', compact('levels', 'categories', 'tournament', 'currentModelName', 'rules', 'rulesCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TournamentRequest $form
     * @return \Illuminate\Http\Response
     */
    public function store(TournamentRequest $form)
    {
        $tournament = $form->persist();
        $msg = trans('msg.tournament_create_successful', ['name' => $tournament->name]);
        flash()->success($msg);
//        else flash('error', 'operation_failed!');
        return redirect(URL::action('TournamentController@edit', $tournament->slug));
    }

    /**
     * Display the specified resource.
     *
     * @param Tournament $tournament
     * @return \Illuminate\Http\Response
     */
    public function show(Tournament $tournament)
    {
        $teams = "";
        $grades = Grade::pluck('name', 'id');
        return view('tournaments.show', compact('tournament', 'grades', 'teams'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Tournament $tournament
     * @return \Illuminate\Http\Response
     */
    public function edit(Tournament $tournament)
    {

        $tournament = Tournament::with('competitors', 'categorySettings', 'championships.settings', 'championships.category')->find($tournament->id);
        // Statistics for Right Panel
        $countries = Countries::pluck('name', 'id');
        $numCompetitors = $tournament->competitors->groupBy('user_id')->count();
        $numTeams = $tournament->teams()->count();
        $settingSize = $tournament->categorySettings->count();
        $categorySize = $tournament->championships->count();
        $rules = config('options.rules');
        $hanteiLimit = config('options.hanteiLimit');
        $selectedCategories = $tournament->categories;
        $baseCategories = Category::take(10)->get();
//        // Gives me a list of category containing
        $categories1 = $selectedCategories->merge($baseCategories)->unique();
        $grades = Grade::pluck('name', 'id');
        $categories = new Collection();
        $tournament->venue == null
            ? $venue = new Venue
            : $venue = $tournament->venue;



        foreach ($categories1 as $category) {

            $category->alias != '' ? $category->name = $category->alias
                : $category->name = trim($category->buildName($grades));
            $categories->push($category);
        }
        $categories = $categories->sortBy(function ($key) {
            return $key;
        })->pluck('name', 'id');


        $levels = TournamentLevel::pluck('name', 'id');

        return view('tournaments.edit', compact('tournament', 'levels', 'categories', 'settingSize', 'categorySize', 'grades', 'numCompetitors', 'rules', 'hanteiLimit', 'numTeams', 'countries', 'venue'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TournamentRequest $request
     * @param VenueRequest $venueRequest
     * @param Tournament $tournament
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TournamentRequest $request, VenueRequest $venueRequest, Tournament $tournament)
    {
        $venue = $tournament->venue;
        if ($venue == null)
            $venue = new Venue;

        if ($venueRequest->has('venue_name')) {

            $venue->fill($venueRequest->all());
            $venue->save();
        }else{
            $venue = new Venue();
        }
        $res = $request->update($tournament, $venue);
        if ($request->ajax()) {
            $res == 0
                ? $result = Response::json(['msg' => trans('msg.tournament_update_error', ['name' => $tournament->name]), 'status' => 'error'])
                : $result = Response::json(['msg' => trans('msg.tournament_update_successful', ['name' => $tournament->name]), 'status' => 'success']);
            return $result;
        } else {
            $res == 0
                ? flash()->success(trans('msg.tournament_update_error', ['name' => $tournament->name]))
                : flash()->success(trans('msg.tournament_update_successful', ['name' => $tournament->name]));
            return redirect(URL::action('TournamentController@edit', $tournament->slug));
        }


    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Tournament $tournament
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Tournament $tournament)
    {
        if ($tournament->delete()) {
            return Response::json(['msg' => Lang::get('msg.tournament_delete_successful', ['name' => $tournament->name]), 'status' => 'success']);
        }
        return Response::json(['msg' => Lang::get('msg.tournament_delete_error', ['name' => $tournament->name]), 'status' => 'error']);

    }

    /**
     * @param $tournamentSlug
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($tournamentSlug)

    {
        $tournament = Tournament::withTrashed()->whereSlug($tournamentSlug)->first();
        if ($tournament->restore()) {
            return Response::json(['msg' => Lang::get('msg.tournament_restored_successful', ['name' => $tournament->name]), 'status' => 'success']);
        }

        return Response::json(['msg' => Lang::get('msg.tournament_restored_error', ['name' => $tournament->name]), 'status' => 'error']);

    }

    /**
     * Called when a user want to register an open tournament
     * @param Tournament $tournament
     * @return mixed
     * @throws InvitationNeededException
     */
    public function register(Tournament $tournament)
    {

        if ($tournament->isOpen()) {
            return view("categories.register", compact('tournament', 'invite', 'currentModelName'));
        }
        throw new InvitationNeededException();
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        $currentModelName = trans_choice('core.tournament', 2);
        if (Auth::user()->isSuperAdmin()) {
            $tournaments = Tournament::onlyTrashed()->with('owner')
                ->has('owner')
                ->orderBy('tournament.created_at', 'desc')
                ->paginate(config('constants.PAGINATION'));
        } else {
            $tournaments = Auth::user()->tournaments()->with('owner')
                ->onlyTrashed()
                ->orderBy('created_at', 'desc')
                ->paginate(config('constants.PAGINATION'));
        }
        $title = trans('core.tournaments_deleted');
        return view('tournaments.deleted', compact('tournaments', 'currentModelName', 'title'));
    }

    /**
     * @param $tournamentId
     */
    public function generateTrees($tournamentId)
    {
        $tournament = Tournament::findOrFail($tournamentId);
        $tournamentCategories = Championship::where('tournament_id', $tournamentId)->get();
        foreach ($tournamentCategories as $tcat) {
            // Get number of area for this category
            $fightingAreas = null;
            $settings = ChampionshipSettings::where('championship_id', $tcat->id)->get();
            if (is_null($settings) || sizeof($settings) == 0) {

                // Check general user settings
                $generalSettings = Auth::user()->settings;

                if (is_null($generalSettings) || sizeof($generalSettings) == 0)
                    $fightingAreas = config('constants.CAT_FIGHTING_AREAS');
            } else {
                $fightingAreas = $settings->fightingAreas;
            }

            echo "<h3>" . $tcat->category->name . "</h3>";
            $competitors = $tournament->competitors()->where('championship_id', $tcat->id);
            echo $competitors;
        }
    }
}
