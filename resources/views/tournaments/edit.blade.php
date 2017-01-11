@extends('layouts.dashboard')
@section('styles')
    {!! Html::style('js/jquery.timepicker.css')!!}
@stop
@section('title')
    <title>{{ trans('core.editModel', ['currentModelName' => trans_choice('core.tournament',1)]) }}</title>
@stop
@section('breadcrumbs')
    {!! Breadcrumbs::render('tournaments.edit',$tournament) !!}

@stop
@section('content')
    @include("errors.list")
    <?php
    $appURL = (app()->environment() == 'local' ? getenv('URL_BASE') : config('app.url'));

    ?>


    <!-- Detached content -->
    <div class="container-detached">
        <div class="content-detached">
            <ul class="nav nav-tabs nav-tabs-solid nav-justified">
                <li class="active"><a href="#general" data-toggle="tab" id="tab1">{{trans('core.general_data')}}</a></li>
                <li><a href="#venue" data-toggle="tab" id="tab2">{{trans('core.venue')}}</a></li>
                <li><a href="#categories" data-toggle="tab" id="tab3">{{trans_choice('categories.category',2)}}</a></li>
                <li><a href="#category_setting" data-toggle="tab" id="tab4">{{trans_choice('categories.categorySettings',2)}}</a></li>

            </ul>

            <div class="row">
                <div class="col-lg-12">

                    <!-- Simple panel 1 : General Data-->


                    <div class="tab-content">

                        <div class="tab-pane active" id="general">
                            @include("layouts.tournament.general")
                        </div>

                        <div class="tab-pane" id="venue">
                            @include("layouts.tournament.venue")
                        </div>
                        <div class="tab-pane" id="categories">
                            @include("layouts.tournament.categories")

                        </div>
                        <div class="tab-pane" id="category_setting">
                            @include("layouts.tournament.categories_settings")
                        </div>
                    </div>
                </div>


                <!-- /simple panel acordion -->


            </div>
        </div>
        <!-- /detached content -->
    </div>
    @include("right-panel.tournament_menu")
    @include("modals.create_category")

    <!-- /content area -->
    <?php
    $now = Carbon\Carbon::now();
    $year = $now->year;
    $month = $now->month - 1; // Javascript dates are zero-indexed https://github.com/amsul/pickadate.js/issues/768
    $day = $now->day;

    $venue = $venue->setDefaultLocation($tournament, $venue->latitude, $venue->longitude);

    ?>

@stop

@section('scripts_footer')
    <script>
        let url_base = "{{ route('tournaments.index') }}";
        let url_api_base = "{{ route('api.tournaments.index') }}";
        let url_api_root = "{{ route('api.root') }}";
        let url_edit = "{{ URL::action('TournamentController@update', $tournament->slug) }}";
        let longitude = "{{$venue->longitude }}";
        let latitude = "{{$venue->latitude }}";
        let configured = "{{ trans('core.configured_full') }}";
        let allCategoriesSize = '{!! $categorySize !!}';
        let dualListIds = [];
        let dualList;
        let venue = "{!! addcslashes($venue, '"') !!}";
        venue = JSON.parse(venue);

        $("a").on('click', function () {

            let href = $(this).attr('href');
            let id = href.replace("#", "");
            console.log(id);
            if ($("a[data-toggle='tab'][id='" + id + "']")) {
                $("#" + id).click();
            }
        });
    </script>
    {!! Html::script('js/pages/header/tournamentEdit.js') !!}
    {!! Html::script('https://maps.google.com/maps/api/js?key=AIzaSyDMbCISDkoc5G1AP1mw8K76MsaN0pyF64k&libraries=places') !!}
    {!! Html::script('js/categoryCreate.js') !!}

@stop