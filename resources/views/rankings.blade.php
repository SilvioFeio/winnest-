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
        <th class="center">Rank</th>
        <th>Breeding pair</th>
        <th>Youngster</th>
        <th class="num">Pair points</th>
        <th class="center">Wins</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="center"><span class="rank gold">1</span></td>
        <td><a href="{{ route('pairs') }}">Cobalt Wing &times; Northern Star</a></td>
        <td>Storm Runner — NL26-1101234</td>
        <td class="num pts">125</td>
        <td class="center">4</td>
      </tr>
      <tr>
        <td class="center"><span class="rank silver">2</span></td>
        <td><a href="{{ route('pairs') }}">Dutch Flyer &times; Amber Queen</a></td>
        <td>Swift Comet — NL26-1101235</td>
        <td class="num pts">110</td>
        <td class="center">3</td>
      </tr>
      <tr>
        <td class="center"><span class="rank bronze">3</span></td>
        <td><a href="{{ route('pairs') }}">Iron Duke &times; Velvet Rose</a></td>
        <td>Blaze — NL26-1101236</td>
        <td class="num pts">97</td>
        <td class="center">2</td>
      </tr>
      <tr>
        <td class="center">4</td>
        <td><a href="{{ route('pairs') }}">Copper King &times; Misty Dawn</a></td>
        <td>Skyler — NL26-1101237</td>
        <td class="num pts">89</td>
        <td class="center">1</td>
      </tr>
      <tr>
        <td class="center">5</td>
        <td><a href="{{ route('pairs') }}">Silver Hawk &times; Golden Bell</a></td>
        <td>Ranger — NL26-1101238</td>
        <td class="num pts">82</td>
        <td class="center">1</td>
      </tr>
      <tr>
        <td class="center">6</td>
        <td><a href="{{ route('pairs') }}">Thunderbolt &times; Rosetta</a></td>
        <td>Nova — NL26-1101239</td>
        <td class="num pts">75</td>
        <td class="center">0</td>
      </tr>
    </tbody>
  </table>
  <p class="meta-line" style="margin-top:16px;">Points are awarded to the breeding pair based on their youngster's placement in each one-loft race.</p>
@endsection