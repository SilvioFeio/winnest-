@extends('layouts.app')
 
@section('title', 'Home')
 
@section('hero')
<section class="hero">
  <div class="container">
    <h1>Every pairing tells you something, once you can see it.</h1>
    <p>WinNest links your Compuclub pedigrees to your youngsters' one-loft race results, so you always know which pairs are actually producing.</p>
    <div class="stats">
      {{-- Live counts from the database (PageController@home) --}}
      <div class="stat"><span class="num">{{ $pairCount }}</span><span class="label">Breeding pairs</span></div>
      <div class="stat"><span class="num">{{ $races->count() }}</span><span class="label">One-loft races tracked</span></div>
      <div class="stat"><span class="num">{{ $youngsterCount }}</span><span class="label">Youngsters registered</span></div>
    </div>
    <a class="btn" href="{{ route('rankings') }}">View pair rankings</a>
  </div>
</section>
@endsection
 
@section('content')
  <div class="section-head">
    <h2>One-loft races</h2>
    <p>Results feed in from OneLoftRace</p>
  </div>

  <table class="ledger">
    <caption>Races and our best final result</caption>
    <thead>
      <tr>
        <th>Race</th>
        <th>Season</th>
        <th class="num">Stages</th>
        <th>Best youngster in the final</th>
      </tr>
    </thead>
    <tbody>
      {{-- $bestFinals is keyed by race id; a race without final results shows a dash --}}
      @forelse ($races as $race)
        @php $best = $bestFinals->get($race->id); @endphp
        <tr>
          <td><a href="{{ route('results', ['race' => $race->id]) }}">{{ $race->name }}</a></td>
          <td>{{ $race->season }}</td>
          <td class="num">{{ $race->stages_count }}</td>
          <td>{{ $best ? $best->pigeon->ring_number.' — #'.$best->position.' of '.$best->stage->participants : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="center">No races yet. Run <code>php artisan import:pigeons</code>.</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection