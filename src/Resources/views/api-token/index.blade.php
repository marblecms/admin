@extends('marble::layouts.app')

@section('content')
    <h1>API Tokens</h1>

    @if(session('new_token'))
        <div class="alert alert-success">
            <strong>Token created successfully!</strong>
            Copy this token now &mdash; it won't be shown again:
            <br><br>
            <code class="marble-token-code">{{ session('new_token') }}</code>
        </div>
    @endif

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Existing Tokens</h2>
        </header>
        <div class="main-box-body clearfix">
            @if($tokens->isEmpty())
                <p class="text-muted">No tokens yet.</p>
            @else
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Abilities</th>
                            <th>Last Used</th>
                            <th>Expires</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tokens as $token)
                            <tr>
                                <td>{{ $token->name }}</td>
                                <td>
                                    @foreach($token->abilities ?? [] as $ability)
                                        <span class="label label-default">{{ $ability }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : '—' }}</td>
                                <td>{{ $token->expires_at ? $token->expires_at->toDateString() : '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('marble.api-token.delete', $token) }}" onsubmit="return confirm('Delete this token?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            @include('marble::components.famicon', ['name' => 'cancel'])
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Create New Token</h2>
        </header>
        <div class="main-box-body clearfix">
            <form method="POST" action="{{ route('marble.api-token.create') }}">
                @csrf

                <div class="form-group">
                    <label>Token Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. My App Token" required />
                </div>

                <div class="form-group">
                    <label>Abilities</label>
                    <div>
                        <label class="marble-ability-label">
                            <input type="checkbox" name="abilities[]" value="read" checked>
                            read
                        </label>
                        <label class="marble-ability-label">
                            <input type="checkbox" name="abilities[]" value="write">
                            write
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Expires At <small class="text-muted">(optional)</small></label>
                    <input type="date" name="expires_at" class="form-control marble-input-date" />
                </div>

                <button type="submit" class="btn btn-success">
                    @include('marble::components.famicon', ['name' => 'lock_key'])
                    Create Token
                </button>
            </form>
        </div>
    </div>
@endsection
