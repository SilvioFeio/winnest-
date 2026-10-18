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
  <div class="section-head">
    <h2>Season 2026 leaderboard</h2>
    <p>Updated after every race</p>
  </div>
 
  <table class="ledger">
    <caption>Pair leaderboard</caption>
    <thead>
      <tr>
        <th>Rank</th>
        <th>Pair</th>
        <th>Season</th>
        <th>Youngsters</th>
        <th>Score</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($ranking as $index => $pair)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $pair['cock'] }} × {{ $pair['hen'] }}</td>
            <td>{{ $pair['season'] }}</td>
            <td>{{ $pair['youngsters'] }}</td>
            <td>{{ $pair['total'] }}</td>
        </tr>
    @endforeach
    </tbody>
  </table>
  <p class="meta-line" style="margin-top:16px;">Points are awarded to the breeding pair based on their youngster's placement in each one-loft race.</p>
@endsection