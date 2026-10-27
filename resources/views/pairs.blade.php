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
  {{-- Top: pick a pair (?pair=ID) and see its profile and youngsters; bottom: every youngster with its parents --}}
  @if (count($ranking) === 0)
    <p class="meta-line">No pairs have been imported yet. Run <code>php artisan import:pigeons</code>.</p>
  @else
    <div class="section-head">
      <h2>Find a pair</h2>
    </div>

    <form method="get" action="{{ route('pairs') }}" class="control-panel">
      <label for="pair-select">Choose a breeding pair (ordered by rank)</label>
      <div class="control-row">
        <select id="pair-select" name="pair">
          @foreach ($ranking as $index => $row)
            <option value="{{ $row['pair_id'] }}" @selected($row['pair_id'] == $selectedId)>
              #{{ $index + 1 }} — {{ $row['cock'] }} × {{ $row['hen'] }} ({{ $row['youngsters'] }} youngsters)
            </option>
          @endforeach
        </select>
        <button type="submit">View profile</button>
      </div>
    </form>

    @if ($selected && $rankRow)
      <div class="section-head" style="margin-top:32px;">
        <h2>Pair profile</h2>
      </div>

      <div class="profile">
        <dl>
          <dt>Breeding pair</dt><dd>{{ $selected->cock->ring_number }} × {{ $selected->hen->ring_number }}</dd>
          <dt>Season</dt><dd>{{ $selected->season }}</dd>
          <dt>Current season rank</dt><dd class="pill">#{{ $rankPosition }} of {{ count($ranking) }} (score {{ $rankRow['total'] }})</dd>
          <dt>Performance</dt><dd>{{ $rankRow['performance'] }}</dd>
          <dt>Fertility</dt>
          <dd>
            {{ $rankRow['fertility'] }}
            @if ($selected->fertilityRecord)
              ({{ $selected->fertilityRecord->weaned }} weaned of {{ $selected->fertilityRecord->eggs_laid }} eggs)
            @endif
          </dd>
          <dt>Survival</dt><dd>{{ $rankRow['survival'] }}</dd>
          <dt>Youngsters</dt>
          <dd>
            {{ $rankRow['youngsters'] }}
            @unless ($rankRow['enough_data']) — too few youngsters for a fair judgement @endunless
          </dd>
        </dl>
      </div>

      <table class="ledger" style="margin-top:24px;">
        <caption>Youngsters of this pair</caption>
        <thead>
          <tr>
            <th>Youngster</th>
            <th>Sex</th>
            <th>Status</th>
            <th>Results</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($selected->youngsters as $young)
            <tr>
              <td style="white-space:nowrap;">{{ $young->ring_number }}</td>
              <td>{{ $young->sex }}</td>
              <td>{{ $young->status }}</td>
              <td>
                @forelse ($young->results as $res)
                  {{ $res->stage->race->name }}, {{ $res->stage->name }}: {{ $res->position }} of {{ $res->stage->participants }}<br>
                @empty
                  No result on record
                @endforelse
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <div class="section-head" style="margin-top:40px;">
      <h2>Registered youngsters &amp; parents</h2>
    </div>

    <table class="ledger">
      <caption>Youngster directory</caption>
      <thead>
        <tr>
          <th>Youngster</th>
          <th>Breeding pair</th>
          <th>Sex</th>
          <th>Colour</th>
          <th class="center">Hatch year</th>
          <th class="center">Results</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($youngsters as $young)
          <tr>
            <td style="white-space:nowrap;">{{ $young->ring_number }}</td>
            <td style="white-space:nowrap;">
              <a href="{{ route('pairs', ['pair' => $young->parent_pair_id]) }}">
                {{ $young->parentPair->cock->ring_number }} × {{ $young->parentPair->hen->ring_number }}
              </a>
            </td>
            <td>{{ $young->sex }}</td>
            <td>{{ $young->colour }}</td>
            <td class="center">{{ $young->hatchYear() ?? '—' }}</td>
            <td class="center">{{ $young->results_count }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
@endsection
