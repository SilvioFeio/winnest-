@extends('layouts.app')
 
@section('title', 'Home')
 
@section('hero')
<section class="hero">
  <div class="container">
    <h1>Every pairing tells you something, once you can see it.</h1>
    <p>WinNest links your Compuclub pedigrees to your youngsters' one-loft race results, so you always know which pairs are actually producing.</p>
    <div class="stats">
      <div class="stat"><span class="num">70</span><span class="label">Active breeding pairs</span></div>
      <div class="stat"><span class="num">3</span><span class="label">One-loft races tracked</span></div>
      <div class="stat"><span class="num">6</span><span class="label">Youngsters in this season</span></div>
    </div>
    <a class="btn" href="{{ route('rankings') }}">View pair rankings</a>
  </div>
</section>
@endsection
 
@section('content')
  <div class="section-head">
    <h2>Recent one-loft races</h2>
    <p>Results feed in from OneLoftRace</p>
  </div>
 
  <table class="ledger">
    <caption>Recent race results</caption>
    <thead>
      <tr>
        <th>Race</th>
        <th>Date</th>
        <th class="num">Distance</th>
        <th>Winning youngster</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><a href="{{ route('results') }}">Qatar International Cup 2026</a></td>
        <td>Sept 20, 2026</td>
        <td class="num">350 km</td>
        <td>Storm Runner — NL26-1101234</td>
      </tr>
      <tr>
        <td><a href="{{ route('results') }}">Portugal Golden Wing Derby 2026</a></td>
        <td>Aug 15, 2026</td>
        <td class="num">300 km</td>
        <td>Swift Comet — NL26-1101235</td>
      </tr>
      <tr>
        <td><a href="{{ route('results') }}">Thailand One Loft Classic 2026</a></td>
        <td>Jul 10, 2026</td>
        <td class="num">250 km</td>
        <td>Blaze — NL26-1101236</td>
      </tr>
    </tbody>
  </table>
@endsection