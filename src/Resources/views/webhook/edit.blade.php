@extends('marble::layouts.app')

@php
    $prefix = config('marble.route_prefix', 'admin');
    $isNew   = $webhook === null;
    $saveUrl = $isNew
        ? route('marble.webhook.store')
        : route('marble.webhook.update', $webhook);
    $availableEvents = ['item.saved', 'item.created', 'item.deleted', 'item.published', 'item.draft'];
@endphp

@section('content')
    <h1>{{ $isNew ? trans('marble::admin.add_webhook') : trans('marble::admin.edit_webhook') }}</h1>

    <form action="{{ $saveUrl }}" method="POST">
        @csrf

        <div class="main-box">
            <div class="main-box-body clearfix">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $webhook?->name) }}" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>URL</label>
                            <input type="url" name="url" value="{{ old('url', $webhook?->url) }}" class="form-control" required placeholder="https://example.com/webhook" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.webhook_secret') }} <small class="text-muted">({{ trans('marble::admin.optional') }})</small></label>
                            <input type="text" name="secret" value="{{ old('secret', $webhook?->secret) }}" class="form-control" placeholder="HMAC secret key" />
                            <p class="help-block">{{ trans('marble::admin.webhook_secret_hint') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ trans('marble::admin.webhook_events') }}</label>
                            @foreach($availableEvents as $event)
                                <div>
                                    <label class="marble-fw-normal">
                                        <input type="checkbox" name="events[]" value="{{ $event }}"
                                            {{ in_array($event, old('events', $webhook?->events ?? [])) ? 'checked' : '' }} />
                                        <code>{{ $event }}</code>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" {{ old('active', $webhook?->active ?? true) ? 'checked' : '' }} />
                        {{ trans('marble::admin.active') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="main-box">
            <div class="main-box-body clearfix">
                <a href="{{ route('marble.webhook.index') }}" class="btn btn-danger">
                    @include('marble::components.famicon', ['name' => 'cancel']) {{ trans('marble::admin.cancel') }}
                </a>
                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                </button>
            </div>
        </div>
    </form>
@endsection
