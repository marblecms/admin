@extends('marble::layouts.app')

@php $prefix = config('marble.route_prefix', 'admin'); @endphp

@section('sidebar')
    <div class="main-box clearfix profile-box-menu">
        <div class="main-box-body clearfix">
            <div class="profile-box-header gray-bg clearfix">
                <h2>{{ trans('marble::admin.usergroups') }}</h2>
            </div>
            <div class="profile-box-content clearfix">
                <ul class="menu-items">
                    <li>
                        <a href="{{ url("{$prefix}/user-group/all") }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'group']) {{ trans('marble::admin.manage_usergroups') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ url("{$prefix}/user-group/add") }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_usergroup') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <h1>
        {{ trans('marble::admin.users') }}
        <div class="pull-right">
            <a href="{{ url("{$prefix}/user/add") }}" class="btn btn-xs btn-success">@include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.add_user') }}</a>
        </div>
    </h1>

    @foreach($groups as $group)
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2 class="pull-left"><b>{{ $group->name }}</b></h2>
            <div class="pull-right">
                <a href="{{ url("{$prefix}/user-group/edit/{$group->id}") }}" class="btn btn-xs btn-info">@include('marble::components.famicon', ['name' => 'pencil'])</a>
            </div>
            <div class="clearfix"></div>
        </header>
        <div class="main-box-body clearfix">
            @if($group->users->isEmpty())
                <p class="text-muted text-center marble-mt-xs marble-mb-0">No users in this group.</p>
            @else
            <table class="table table-striped marble-table-flush">
                <tbody>
                    @foreach($group->users as $user)
                    <tr class="{{ $user->active ? '' : 'text-muted' }}">
                        <td class="marble-col-xxs">
                            @include('marble::components.famicon', ['name' => $user->active ? 'status_online' : 'status_offline'])
                        </td>
                        <td>
                            <a href="{{ url("{$prefix}/user/edit/{$user->id}") }}" class="{{ $user->active ? '' : 'text-muted' }}">{{ $user->name }}</a>
                            <small class="text-muted marble-ml-xs">{{ $user->email }}</small>
                            @if(!$user->active)
                                <span class="label label-default marble-ml-xs">{{ trans('marble::admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-muted marble-text-sm">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
                        </td>
                        <td class="text-right">
                            <a href="{{ url("{$prefix}/user/edit/{$user->id}") }}" class="btn btn-info btn-xs">@include('marble::components.famicon', ['name' => 'pencil'])</a>
                            <form method="POST" action="{{ url("{$prefix}/user/delete/{$user->id}") }}" class="marble-inline-form" onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">@include('marble::components.famicon', ['name' => 'bin'])</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endforeach
@endsection
