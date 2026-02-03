@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Family Entries</h1>
    
    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to All Callers</a>
        <a href="{{ route('winners') }}" class="btn btn-secondary">View Winners</a>
    </div>

    <div class="alert alert-info">
        This page shows all callers marked as family entries. Families cannot be edited from this page.
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>CPR</th>
                <th>Status</th>
                <th>Family</th>
                <th>Winner</th>
            </tr>
        </thead>
        <tbody>
            @foreach($families as $family)
            <tr>
                <td class="caller-name-cell">{{ $family->name }}</td>
                <td>{{ $family->phone }}</td>
                <td>{{ $family->cpr }}</td>
                <td>
                    <span class="badge status-{{ strtolower($family->status) }}">
                        {{ $family->status ?? 'PENDING' }}
                    </span>
                </td>
                <td>
                    <span class="badge is-family">FAMILY</span>
                </td>
                <td>
                    <span class="badge {{ $family->is_winner ? 'is-winner' : 'not-winner' }}">
                        {{ $family->is_winner ? 'WINNER' : 'NOT WINNER' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($families->isEmpty())
    <div class="alert alert-warning">
        No family entries found.
    </div>
    @endif
</div>
@endsection