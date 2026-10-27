@extends('layouts.app')
 
@section('title', 'Pair Rankings')
 
@section('hero')
<section class="hero">
  <div class="container">
    <h1>Parent pair rankings</h1>
    <p>Breeding pairs ranked by the combined race performance of their youngsters this season.</p>
  </div>
</section>
@endsection
 
@section('content')
  {{-- $season is the newest season in the ranking, null when there are no pairs --}}
  <div class="section-head">
    <h2>Season {{ $season ?? '—' }} leaderboard</h2>
    <p>Score 0–1: 60% race performance, 20% fertility, 20% survival</p>
  </div>

  <table class="ledger">
    <caption>Pair leaderboard</caption>
    <thead>
      <tr>
        <th>Rank</th>
        <th>Pair</th>
        <th>Season</th>
        <th>Youngsters</th>
        <th class="num">Performance</th>
        <th class="num">Fertility</th>
        <th class="num">Survival</th>
        <th class="num">Score</th>
      </tr>
    </thead>
    <tbody>
      {{-- One row per pair, already sorted best first by PairRanking --}}
      @forelse ($ranking as $index => $pair)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td><a href="{{ route('pairs', ['pair' => $pair['pair_id']]) }}">{{ $pair['cock'] }} × {{ $pair['hen'] }}</a></td>
            <td>{{ $pair['season'] }}</td>
            <td>{{ $pair['youngsters'] }}@unless ($pair['enough_data']) <span title="Too few youngsters for a fair judgement">*</span>@endunless</td>
            <td class="num">{{ $pair['performance'] }}</td>
            <td class="num">{{ $pair['fertility'] }}</td>
            <td class="num">{{ $pair['survival'] }}</td>
            <td class="num pts">{{ $pair['total'] }}</td>
        </tr>
      @empty
        <tr><td colspan="8" class="center">No pairs have been imported yet. Run <code>php artisan import:pigeons</code>.</td></tr>
      @endforelse
    </tbody>
  </table>
  <p class="meta-line" style="margin-top:16px;">Performance rewards each youngster's placing (finals count 3×, hotspots 2×, trainings 1×). * = fewer than 2 youngsters, so the score says little yet.</p>
@endsection