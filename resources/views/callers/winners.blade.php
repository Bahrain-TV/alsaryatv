@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Winners</h1>
    
    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to All Callers</a>
        <a href="{{ route('families') }}" class="btn btn-secondary">View Families</a>
    </div>

    <div class="alert alert-info">
        This page shows all callers marked as winners. Winners cannot be edited from this page.
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>CPR</th>
                <th>Status</th>
                <th>Winner</th>
                <th>Entry Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($winners as $winner)
            <tr>
                <td class="caller-name-cell">{{ $winner->name }}</td>
                <td>{{ $winner->phone }}</td>
                <td>{{ $winner->cpr }}</td>
                <td>
                    <span class="badge status-{{ strtolower($winner->status) }}">
                        {{ $winner->status ?? 'PENDING' }}
                    </span>
                </td>
                <td>
                    <span class="badge is-winner">WINNER</span>
                </td>
                <td class="entry-type-cell" data-is-family="{{ $winner->is_family ? 'true' : 'false' }}">
                    {{ $winner->is_family ? 'FAMILY' : 'INDIVIDUAL' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($winners->isEmpty())
    <div class="alert alert-warning">
        No winners found. Go to the main callers list to mark callers as winners.
    </div>
    @endif
</div>
@endsection