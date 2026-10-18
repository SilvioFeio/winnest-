@extends('layouts.app')
 
@section('title', 'Breeding Pairs')
 
@section('hero')
<section class="hero">
  <div class="container">
    <h1>Breeding pairs &amp; youngsters</h1>
    <p>Registered youngsters this season and the breeding pair each one comes from.</p>
  </div>
</section>
@endsection
 
@section('content')
  <div class="section-head">
    <h2>Find a pair</h2>
  </div>
 
  <div class="control-panel">
    <label for="pair-select">Filter by breeding pair</label>
    <div class="control-row">
      <select id="pair-select">
        <option value="0">Cobalt Wing &times; Northern Star — parents of Storm Runner</option>
        <option value="1">Dutch Flyer &times; Amber Queen — parents of Swift Comet</option>
        <option value="2">Iron Duke &times; Velvet Rose — parents of Blaze</option>
        <option value="3">Copper King &times; Misty Dawn — parents of Skyler</option>
        <option value="4">Silver Hawk &times; Golden Bell — parents of Ranger</option>
        <option value="5">Thunderbolt &times; Rosetta — parents of Nova</option>
      </select>
      <button type="button" id="view-profile-btn">View profile</button>
    </div>
  </div>
 
  <div class="section-head">
    <h2>Registered youngsters &amp; parents</h2>
  </div>
 
  <table class="ledger">
    <caption>Youngster directory</caption>
    <thead>
      <tr>
        <th>Youngster</th>
        <th>Breeding pair</th>
        <th class="center">Hatch year</th>
        <th class="center">Races flown</th>
        <th class="num">Points</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Storm Runner — NL26-1101234</td>
        <td>Cobalt Wing &times; Northern Star</td>
        <td class="center">2026</td>
        <td class="center">5</td>
        <td class="num pts">125</td>
      </tr>
      <tr>
        <td>Swift Comet — NL26-1101235</td>
        <td>Dutch Flyer &times; Amber Queen</td>
        <td class="center">2026</td>
        <td class="center">5</td>
        <td class="num pts">110</td>
      </tr>
      <tr>
        <td>Blaze — NL26-1101236</td>
        <td>Iron Duke &times; Velvet Rose</td>
        <td class="center">2026</td>
        <td class="center">4</td>
        <td class="num pts">97</td>
      </tr>
      <tr>
        <td>Skyler — NL26-1101237</td>
        <td>Copper King &times; Misty Dawn</td>
        <td class="center">2026</td>
        <td class="center">4</td>
        <td class="num pts">89</td>
      </tr>
      <tr>
        <td>Ranger — NL26-1101238</td>
        <td>Silver Hawk &times; Golden Bell</td>
        <td class="center">2026</td>
        <td class="center">4</td>
        <td class="num pts">82</td>
      </tr>
      <tr>
        <td>Nova — NL26-1101239</td>
        <td>Thunderbolt &times; Rosetta</td>
        <td class="center">2026</td>
        <td class="center">3</td>
        <td class="num pts">75</td>
      </tr>
    </tbody>
  </table>
 
  <div class="section-head" style="margin-top:40px;">
    <h2>Pair profile</h2>
  </div>
 
  <div class="profile">
    <dl id="profile-body">
      <!-- filled by script -->
    </dl>
  </div>
@endsection
 
@push('scripts')
<script>
  const pairs = [
    { pair: "Cobalt Wing \u00d7 Northern Star", youngster: "Storm Runner", ring: "NL26-1101234", hatch: "2026", rank: 1, points: 125, wins: 4 },
    { pair: "Dutch Flyer \u00d7 Amber Queen", youngster: "Swift Comet", ring: "NL26-1101235", hatch: "2026", rank: 2, points: 110, wins: 3 },
    { pair: "Iron Duke \u00d7 Velvet Rose", youngster: "Blaze", ring: "NL26-1101236", hatch: "2026", rank: 3, points: 97, wins: 2 },
    { pair: "Copper King \u00d7 Misty Dawn", youngster: "Skyler", ring: "NL26-1101237", hatch: "2026", rank: 4, points: 89, wins: 1 },
    { pair: "Silver Hawk \u00d7 Golden Bell", youngster: "Ranger", ring: "NL26-1101238", hatch: "2026", rank: 5, points: 82, wins: 1 },
    { pair: "Thunderbolt \u00d7 Rosetta", youngster: "Nova", ring: "NL26-1101239", hatch: "2026", rank: 6, points: 75, wins: 0 },
  ];
 
  function renderProfile(i) {
    const p = pairs[i];
    document.getElementById("profile-body").innerHTML = `
      <dt>Breeding pair</dt><dd>${p.pair}</dd>
      <dt>Registered youngster</dt><dd>${p.youngster} — ${p.ring}</dd>
      <dt>Hatch year</dt><dd>${p.hatch}</dd>
      <dt>Current season rank</dt><dd class="pill">#${p.rank} (${p.points} pair points)</dd>
      <dt>Race wins</dt><dd>${p.wins}</dd>
      <dt>Breeding status</dt><dd>Active pair</dd>
    `;
  }
 
  document.getElementById("view-profile-btn").addEventListener("click", () => {
    renderProfile(document.getElementById("pair-select").value);
  });
 
  renderProfile(0);
</script>
@endpush