<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Reads race data from oneloftrace.live. These are the public JSON endpoints the website itself
 * uses; they are not a documented API, so they can change without notice.
 */
class OneLoftRace
{
    private const BASE = 'https://oneloftrace.live/api';

    /** All flights of a season: ['running_flights' => [...], 'stopped_flights' => [...]]. */
    public function flights(int $seasonId): array
    {
        return $this->get("/custom/v1/public/season/$seasonId/flightsAndTrainings");
    }

    /** Arrivals in a flight whose ring or team name contains $search. */
    public function results(int $flightId, string $search): array
    {
        return $this->get("/search/v1/public/flights/$flightId/results/live", ['search' => $search])['all_flight_results'] ?? [];
    }

    // GET with a 15 s timeout and 2 retries; throws on an HTTP error so callers never save a bad response
    private function get(string $path, array $query = []): array
    {
        return Http::acceptJson()
            ->withUserAgent('WinNest/1.0 (student project, results for Koopman Racing Pigeons)')
            ->timeout(15)
            ->retry(2, 1000)
            ->get(self::BASE . $path, $query)
            ->throw()
            ->json();
    }
}
