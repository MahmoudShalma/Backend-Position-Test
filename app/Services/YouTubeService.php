<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    /**
     * Search for playlists matching a query and return up to $maxResults.
     *
     * @return array<array{
     *   playlist_id: string,
     *   title: string,
     *   description: string,
     *   thumbnail: string,
     *   channel_name: string
     * }>
     */
    public function searchPlaylists(string $query, int $maxResults = 2): array
    {
        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/search", [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'playlist',
                'maxResults' => $maxResults,
                'key' => $this->apiKey,
            ]);

            if ($response->failed()) {
                Log::error('YouTube Search API failed', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            $items = $response->json('items', []);
            $playlists = [];

            foreach ($items as $item) {
                $playlistId = $item['id']['playlistId'] ?? null;
                if (!$playlistId) continue;

                $playlists[] = [
                    'playlist_id' => $playlistId,
                    'title' => $item['snippet']['title'] ?? '',
                    'description' => $item['snippet']['description'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['high']['url']
                        ?? $item['snippet']['thumbnails']['medium']['url']
                        ?? $item['snippet']['thumbnails']['default']['url']
                        ?? '',
                    'channel_name' => $item['snippet']['channelTitle'] ?? '',
                ];
            }

            // Enrich playlists with video count and metadata
            return $this->enrichPlaylists($playlists);
        } catch (\Exception $e) {
            Log::error('YouTube search error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Enrich playlists with video count, view count from the Playlists API.
     */
    private function enrichPlaylists(array $playlists): array
    {
        if (empty($playlists)) return [];

        $ids = implode(',', array_column($playlists, 'playlist_id'));

        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/playlists", [
                'part' => 'contentDetails,statistics',
                'id' => $ids,
                'key' => $this->apiKey,
            ]);

            if ($response->failed()) return $playlists;

            $details = collect($response->json('items', []))->keyBy('id');

            foreach ($playlists as &$playlist) {
                $detail = $details->get($playlist['playlist_id']);
                $playlist['video_count'] = $detail['contentDetails']['itemCount'] ?? 0;
                $playlist['view_count'] = $detail['statistics']['viewCount'] ?? 0;
            }

            // Fetch durations for each playlist's videos
            foreach ($playlists as &$playlist) {
                $playlist['total_duration'] = $this->getPlaylistDuration($playlist['playlist_id']);
            }

            return $playlists;
        } catch (\Exception $e) {
            Log::error('YouTube enrich error', ['message' => $e->getMessage()]);
            return $playlists;
        }
    }

    /**
     * Get total duration of a playlist by summing video durations.
     */
    private function getPlaylistDuration(string $playlistId): string
    {
        try {
            // Get video IDs from playlist (first 50)
            $response = Http::timeout(15)->get("{$this->baseUrl}/playlistItems", [
                'part' => 'contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'key' => $this->apiKey,
            ]);

            if ($response->failed()) return '';

            $videoIds = collect($response->json('items', []))
                ->pluck('contentDetails.videoId')
                ->filter()
                ->implode(',');

            if (empty($videoIds)) return '';

            // Get video durations
            $videoResponse = Http::timeout(15)->get("{$this->baseUrl}/videos", [
                'part' => 'contentDetails',
                'id' => $videoIds,
                'key' => $this->apiKey,
            ]);

            if ($videoResponse->failed()) return '';

            $totalSeconds = 0;
            foreach ($videoResponse->json('items', []) as $video) {
                $duration = $video['contentDetails']['duration'] ?? '';
                $totalSeconds += $this->parseDuration($duration);
            }

            return $this->formatDuration($totalSeconds);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Parse ISO 8601 duration string (PT1H2M3S) to seconds.
     */
    private function parseDuration(string $duration): int
    {
        $seconds = 0;
        if (preg_match('/(\d+)H/', $duration, $m)) $seconds += (int)$m[1] * 3600;
        if (preg_match('/(\d+)M/', $duration, $m)) $seconds += (int)$m[1] * 60;
        if (preg_match('/(\d+)S/', $duration, $m)) $seconds += (int)$m[1];
        return $seconds;
    }

    /**
     * Format seconds into Arabic readable duration string.
     */
    private function formatDuration(int $totalSeconds): string
    {
        if ($totalSeconds <= 0) return '';

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        $parts = [];
        if ($hours > 0) $parts[] = $hours . ' ساعة';
        if ($minutes > 0) $parts[] = $minutes . ' دقيقة';

        return implode(' ', $parts);
    }
}
