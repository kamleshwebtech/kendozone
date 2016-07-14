<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\InviteRequest;
use App\Invite;
use App\Mailers\AppMailer;
use App\Tournament;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use URL;

class InviteController extends Controller
{

    protected $currentModelName;
    protected $mailer;

    /**
     * InviteController constructor.
     * @param AppMailer $mailer
     */
    public function __construct(AppMailer $mailer)
    {

        $this->currentModelName = trans_choice('core.tournament_invitations', 1);
        View::share('currentModelName', $this->currentModelName);
        $this->mailer = $mailer;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invites = Auth::user()->invites()->with('tournament.owner')->paginate(config('constants.PAGINATION'));
        return view('invitation.index', compact('invites'));
    }

    /**
     * Display the specified resource.
     *
     * @param $tournamentSlug
     * @return \Illuminate\Http\Response
     */
    public function create($tournamentSlug)
    {
        $tournament = Tournament::findBySlug($tournamentSlug);
        return view('invitation.show', compact('tournament'));
    }


    /**
     * Send an email to competitor and store invitation.
     *
     * @param InviteRequest|Request $request
     * @return \Illuminate\Http\Response
     * @internal param AppMailer $mailer
     */
    public function store(Request $request)
    {

        //TODO check that recipient is list of emails
        $this->validate($request, [
            'recipients' => 'required'
        ]);


        $tournament = Tournament::findBySlug($request->tournamentSlug);
        $recipients = json_decode($request->get("recipients"));
        foreach ($recipients as $recipient) {
            // Mail to Recipients
            $invite = new Invite();
            $code = $invite->generateTournamentInvite($recipient, $tournament);
            $this->mailer->sendEmailInvitationTo($recipient, $tournament, $code);

        }
        flash()->success(trans('msg.invitation_sent'));

        return redirect(URL::action('TournamentController@edit', $tournament->slug));

    }

}
