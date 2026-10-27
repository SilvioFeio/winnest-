@extends('layouts.app')
 
@section('title', 'Race Results')
 
@section('hero')
<section class="hero">
  <div class="container">
    <h1>Youngster race results</h1>
    <p>Individual one-loft finishes that feed straight into each breeding pair's season total.</p>
  </div>
</section>
@endsection
 
@section('content')
  {{-- The form reloads the page with ?race=ID&stage=ID; PageController@results picks that race and stage (or the first ones) --}}
  @if ($races->isEmpty())
    <p class="meta-line">No races have been imported yet. Run <code>php artisan import:pigeons</code>.</p>
  @else
    <div class="section-head">
      <h2>Select a race</h2>
    </div>

    <form method="get" action="{{ route('results') }}" class="control-panel">
      <label for="race-select">Choose race</label>
      <div class="control-row">
        <select id="race-select" name="race">
          @foreach ($races as $r)
            <option value="{{ $r->id }}" @selected($race && $race->id == $r->id)>{{ $r->name }}</option>
          @endforeach
        </select>
        <select id="stage-select" name="stage" aria-label="Choose stage">
          @foreach ($stages as $st)
            <option value="{{ $st->id }}" @selected($stage && $stage->id == $st->id)>{{ $st->name }}</option>
          @endforeach
        </select>
        <button type="submit">View results</button>
      </div>
    </form>

    <div class="section-head">
      <h2>{{ $race->name }}@if ($stage) — {{ $stage->name }}@endif</h2>
    </div>
    <p class="meta-line">Country: {{ $race->country }} · Season: {{ $race->season }} · Results on record: {{ $results->count() }}@if ($stage) · Pigeons in this stage: {{ $stage->participants }}@endif
      @if ($lastSynced) · Synced from OneLoftRace {{ \Illuminate\Support\Carbon::parse($lastSynced)->diffForHumans() }} @endif
    </p>

    <table class="ledger">
      <caption>Race results</caption>
      <thead>
        <tr>
          <th class="center">Position</th>
          <th>Youngster</th>
          <th>Breeding pair</th>
          <th class="num">Top %</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($results as $result)
          @php
            $pair = $result->pigeon->parentPair;
            $rankClass = $result->position == 1 ? 'gold' : ($result->position == 2 ? 'silver' : ($result->position == 3 ? 'bronze' : ''));
          @endphp
          <tr>
            <td class="center">
              @if ($rankClass)
                <span class="rank {{ $rankClass }}">{{ $result->position }}</span>
              @else
                {{ $result->position }}
              @endif
            </td>
            <td style="white-space:nowrap;">{{ $result->pigeon->ring_number }}</td>
            <td style="white-space:nowrap;">{{ $pair ? $pair->cock->ring_number.' × '.$pair->hen->ring_number : '—' }}</td>
            <td class="num pts">{{ number_format($result->position / $stage->participants * 100, 1) }}%</td>
          </tr>
        @empty
          <tr><td colspan="4" class="center">No results recorded for this race yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  @endif
@endsection
