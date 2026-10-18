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
  <div class="section-head">
    <h2>Select a race</h2>
  </div>
 
  <div class="control-panel">
    <label for="race-select">Choose race</label>
    <div class="control-row">
      <select id="race-select">
        <option value="qatar">Qatar International Cup 2026 — Sept 20, 2026</option>
        <option value="portugal">Portugal Golden Wing Derby 2026 — Aug 15, 2026</option>
        <option value="thailand">Thailand One Loft Classic 2026 — Jul 10, 2026</option>
      </select>
      <button type="button" id="view-btn">View results</button>
    </div>
  </div>
 
  <div class="section-head">
    <h2 id="race-title">Qatar International Cup 2026 — Results</h2>
  </div>
  <p class="meta-line" id="race-meta">Date: Sept 20, 2026 · Distance: 350 km</p>
 
  <table class="ledger">
    <caption>Race results</caption>
    <thead>
      <tr>
        <th class="center">Position</th>
        <th>Youngster</th>
        <th>Breeding pair</th>
        <th class="num">Time</th>
        <th class="num">Points</th>
      </tr>
    </thead>
    <tbody id="results-body"></tbody>
  </table>
@endsection
 
@push('scripts')
<script>
  const races = {
    qatar: {
      title: "Qatar International Cup 2026 — Results",
      meta: "Date: Sept 20, 2026 · Distance: 350 km",
      rows: [
        ["Storm Runner — NL26-1101234", "Cobalt Wing \u00d7 Northern Star", "6:42:10", 25],
        ["Swift Comet — NL26-1101235", "Dutch Flyer \u00d7 Amber Queen", "6:44:52", 20],
        ["Blaze — NL26-1101236", "Iron Duke \u00d7 Velvet Rose", "6:47:03", 16],
        ["Skyler — NL26-1101237", "Copper King \u00d7 Misty Dawn", "6:49:31", 13],
        ["Ranger — NL26-1101238", "Silver Hawk \u00d7 Golden Bell", "6:52:18", 11],
      ],
    },
    portugal: {
      title: "Portugal Golden Wing Derby 2026 — Results",
      meta: "Date: Aug 15, 2026 · Distance: 300 km",
      rows: [
        ["Swift Comet — NL26-1101235", "Dutch Flyer \u00d7 Amber Queen", "5:38:44", 25],
        ["Storm Runner — NL26-1101234", "Cobalt Wing \u00d7 Northern Star", "5:40:02", 20],
        ["Nova — NL26-1101239", "Thunderbolt \u00d7 Rosetta", "5:43:19", 16],
        ["Blaze — NL26-1101236", "Iron Duke \u00d7 Velvet Rose", "5:45:07", 13],
        ["Skyler — NL26-1101237", "Copper King \u00d7 Misty Dawn", "5:47:55", 11],
      ],
    },
    thailand: {
      title: "Thailand One Loft Classic 2026 — Results",
      meta: "Date: Jul 10, 2026 · Distance: 250 km",
      rows: [
        ["Blaze — NL26-1101236", "Iron Duke \u00d7 Velvet Rose", "4:51:20", 25],
        ["Storm Runner — NL26-1101234", "Cobalt Wing \u00d7 Northern Star", "4:52:41", 20],
        ["Swift Comet — NL26-1101235", "Dutch Flyer \u00d7 Amber Queen", "4:54:09", 16],
        ["Ranger — NL26-1101238", "Silver Hawk \u00d7 Golden Bell", "4:57:33", 13],
        ["Nova — NL26-1101239", "Thunderbolt \u00d7 Rosetta", "5:00:12", 11],
      ],
    },
  };
 
  function renderRace(key) {
    const race = races[key];
    document.getElementById("race-title").textContent = race.title;
    document.getElementById("race-meta").textContent = race.meta;
    const body = document.getElementById("results-body");
    body.innerHTML = "";
    race.rows.forEach((row, i) => {
      const pos = i + 1;
      const rankClass = pos === 1 ? "gold" : pos === 2 ? "silver" : pos === 3 ? "bronze" : "";
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="center">${rankClass ? `<span class="rank ${rankClass}">${pos}</span>` : pos}</td>
        <td>${row[0]}</td>
        <td>${row[1]}</td>
        <td class="num">${row[2]}</td>
        <td class="num pts">${row[3]}</td>
      `;
      body.appendChild(tr);
    });
  }
 
  document.getElementById("view-btn").addEventListener("click", () => {
    renderRace(document.getElementById("race-select").value);
  });
 
  renderRace("qatar");
</script>
@endpush