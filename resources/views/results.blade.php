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
        <button type="submit">View results</button>
      </div>
    </form>

    <div class="section-head">
      <h2>{{ $race->name }} — Results</h2>
    </div>
    <p class="meta-line">Country: {{ $race->country }} · Season: {{ $race->season }} · Results on record: {{ $results->count() }}</p>

    <table class="ledger">
      <caption>Race results</caption>
      <thead>
        <tr>
          <th>Stage</th>
          <th class="center">Position</th>
          <th>Youngster</th>
          <th>Breeding pair</th>
          <th class="num">Out of</th>
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
            <td>{{ $result->stage->name }}</td>
            <td class="center">
              @if ($rankClass)
                <span class="rank {{ $rankClass }}">{{ $result->position }}</span>
              @else
                {{ $result->position }}
              @endif
            </td>
            <td>{{ $result->pigeon->ring_number }}</td>
            <td>{{ $pair ? $pair->cock->ring_number.' × '.$pair->hen->ring_number : '—' }}</td>
            <td class="num">{{ $result->stage->participants }}</td>
            <td class="num pts">{{ number_format($result->position / $result->stage->participants * 100, 1) }}%</td>
          </tr>
        @empty
          <tr><td colspan="6" class="center">No results recorded for this race yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  @endif
@endsection
