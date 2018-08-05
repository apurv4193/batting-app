<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{asset('css/admin/dist/img/avatar5.png')}}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><a href="{{ url('admin/dashboard') }}">{{ucfirst(Auth::user()->name)}}</a></p>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="header"><center>=================================</center></li>
            <li class="{{ (Request::is('admin/users')) ? 'active treeview' : 'treeview' }}"> <!-- Request::is('admin/create-user') ||  -->
                <a href="{{url('admin/users')}}">
                    <i class="fa fa-user"></i>
                    <span>
                        {{trans('adminlabels.user_management')}}
                    </span>
                </a>
            </li>
            <li class="{{ (Request::is('admin/games')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/games')}}">
                    <i class="fa fa-gamepad"></i>
                    <span>
                        {{trans('adminlabels.game_management')}}
                    </span>
                </a>
            </li>
            <li class="{{ (Request::is('admin/items') || Request::is('admin/gamecase') || Request::is('admin/edit-game-case/{id}') || Request::is('admin/add-gamecase_bundle') || Request::is('admin/gamecase_bundle')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/items')}}" style="word-break: break-word !important;white-space: initial;">
                    <i class="fa fa-diamond"></i>
                    <span>
                        {{trans('adminlabels.game_case_bundle_management')}}
                    </span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ Request::is('admin/items') || Request::is('admin/edit-item/{id}')  ? 'active' : '' }}">
                        <a href="{{url('admin/items')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.item')}}</a>
                    </li>
                    <li class="{{ Request::is('admin/gamecase') || Request::is('admin/edit-game-case/{id}')  ? 'active' : '' }}">
                        <a href="{{url('admin/gamecase')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.gamecase')}}</a>
                    </li>
                    <li class="{{ Request::is('admin/gamecase_bundle') || Request::is('admin/add-gamecase_bundle') ? 'active' : '' }}">
                        <a href="{{url('admin/gamecase_bundle')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.game_case_bundle')}}</a>
                    </li>
                </ul>
            </li>

            <li class="{{ (Request::is('admin/contests') || Request::is('admin/add-contest') || Request::is('admin/edit-contest/{id}') || Request::is('admin/contest_type')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/contests')}}">
                    <i class="fa fa-diamond"></i>
                    <span>
                        {{trans('adminlabels.contest_management')}}
                    </span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ Request::is('admin/contest_type') || Request::is('admin/edit-contest-type') || Request::is('admin/edit-game-case/{id}')  ? 'active' : '' }}">
                        <a href="{{url('admin/contest_type')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.contest_type')}}</a>
                    </li>
                    <li class="{{ Request::is('admin/contests') || Request::is('admin/add-contest') ? 'active' : '' }}">
                        <a href="{{url('admin/contests')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.contest')}}</a>
                    </li>
                </ul>
            </li>
            <li class="{{ (Request::is('admin/ads')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/ads')}}">
                    <i class="fa fa-buysellads"></i>
                    <span> {{trans('adminlabels.ads_management')}} </span>
                </a>
            </li>
            <!--<li class="{{ (Request::is('admin/list-roster')) ? 'active treeview' : 'treeview' }}">
                            <a href="{{url('admin/rosters')}}">
                                <i class="fa fa-dashboard"></i>
                                <span>
                                    {{trans('adminlabels.roster_management')}}
                                </span>
                            </a>
            </li>-->
            <li class="{{ (Request::is('admin/players')) || Request::is('admin/team') ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/players')}}">
                    <i class="fa fa-dashboard"></i>
                    <span>
                        {{trans('adminlabels.players_management')}}
                    </span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="{{ Request::is('admin/players') || Request::is('admin/edit-contest-type') || Request::is('admin/edit-game-case/{id}')  ? 'active' : '' }}">
                        <a href="{{url('admin/players')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.players')}}</a>
                    </li>
                    <li class="{{ Request::is('admin/team') || Request::is('admin/edit-contest-type') || Request::is('admin/edit-game-case/{id}')  ? 'active' : '' }}">
                        <a href="{{url('admin/team')}}"><i class="fa fa-circle-o"></i>{{trans('adminlabels.teams')}}</a>
                    </li>
                </ul>

            </li>
            <li class="{{ (Request::is('admin/prize_distribution')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/prize_distribution')}}">
                    <i class="fa fa-dashboard"></i>
                    <span>
                        {{trans('adminlabels.Prize_management')}}
                    </span>
                </a>
            </li>
            <li class="{{ (Request::is('admin/contest_score')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/contest_score')}}">
                    <i class="fa fa-dashboard"></i>
                    <span>
                        {{trans('adminlabels.contest_score')}}
                    </span>
                </a>
            </li>
            <li class="{{ (Request::is('admin/klash-coin-pack') || Request::is('admin/add-klash-coin-pack') || Request::is('admin/edit-klash-coin-pack/{id}')) ? 'active treeview' : 'treeview' }}">
                <a href="{{url('admin/klash-coin-pack')}}">
                    <i class="fa fa-money"></i>
                    <span>
                        {{trans('adminlabels.kalsh_coin_pack')}}
                    </span>
                </a>
            </li>

        </ul>
    </section>
</aside>
