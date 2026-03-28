<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Services\GeminiService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseController extends Controller
{
    public function __construct(
        private GeminiService $geminiService,
        private YouTubeService $youtubeService
    ) {}

    /**
     * Display the main page with categories input and discovered courses.
     */
    public function index(Request $request)
    {
        $categoryFilter = $request->query('category');
        $page = $request->query('page', 1);

        $query = Playlist::query()->orderByDesc('created_at');

        if ($categoryFilter && $categoryFilter !== 'all') {
            $query->category($categoryFilter);
        }

        $playlists = $query->paginate(8)->withQueryString();

        $categories = Playlist::select('category')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        $totalCount = Playlist::count();

        return view('courses.index', compact('playlists', 'categories', 'totalCount', 'categoryFilter'));
    }

    /**
     * Start fetching courses using SSE (Server-Sent Events) for real-time progress.
     */
    public function fetch(Request $request): StreamedResponse
    {
        $request->validate([
            'categories' => 'required|string|min:2',
        ]);

        $categories = array_filter(
            array_map('trim', explode("\n", $request->input('categories')))
        );

        if (empty($categories)) {
            return response()->json(['error' => 'No valid categories provided'], 422);
        }

        return new StreamedResponse(function () use ($categories) {
            // Disable output buffering
            while (ob_get_level()) ob_end_clean();

            $this->sendSSE('start', [
                'total_categories' => count($categories),
                'message' => 'بدأ جمع الدورات...',
            ]);

            $totalStored = 0;
            $totalSkipped = 0;

            foreach ($categories as $index => $category) {
                $this->sendSSE('category_start', [
                    'category' => $category,
                    'index' => $index + 1,
                    'total' => count($categories),
                    'message' => "جاري توليد عناوين الدورات لـ: {$category}",
                ]);

                // Step 1: Generate course titles using AI
                $titles = $this->geminiService->generateCourseTitles($category);

                if (empty($titles)) {
                    $this->sendSSE('category_error', [
                        'category' => $category,
                        'message' => "فشل في توليد عناوين لـ: {$category}",
                    ]);
                    continue;
                }

                $this->sendSSE('titles_generated', [
                    'category' => $category,
                    'count' => count($titles),
                    'message' => "تم توليد " . count($titles) . " عنوان لـ: {$category}",
                ]);

                // Step 2: Search YouTube for each title
                $categoryStored = 0;
                foreach ($titles as $titleIndex => $title) {
                    $this->sendSSE('searching', [
                        'category' => $category,
                        'title' => $title,
                        'progress' => ($titleIndex + 1) . '/' . count($titles),
                        'message' => "بحث عن: {$title}",
                    ]);

                    $playlists = $this->youtubeService->searchPlaylists($title, 2);

                    foreach ($playlists as $playlistData) {
                        // Deduplication: skip if playlist_id already exists
                        $exists = Playlist::where('playlist_id', $playlistData['playlist_id'])->exists();

                        if ($exists) {
                            $totalSkipped++;
                            continue;
                        }

                        Playlist::create([
                            'playlist_id' => $playlistData['playlist_id'],
                            'title' => $playlistData['title'],
                            'description' => $playlistData['description'],
                            'thumbnail' => $playlistData['thumbnail'],
                            'channel_name' => $playlistData['channel_name'],
                            'category' => $category,
                            'video_count' => $playlistData['video_count'] ?? 0,
                            'total_duration' => $playlistData['total_duration'] ?? '',
                            'view_count' => $playlistData['view_count'] ?? 0,
                        ]);

                        $totalStored++;
                        $categoryStored++;
                    }

                    // Small delay to respect API rate limits
                    usleep(200000); // 200ms
                }

                $this->sendSSE('category_done', [
                    'category' => $category,
                    'stored' => $categoryStored,
                    'message' => "تم الانتهاء من {$category}: تم حفظ {$categoryStored} دورة",
                ]);
            }

            $this->sendSSE('complete', [
                'total_stored' => $totalStored,
                'total_skipped' => $totalSkipped,
                'message' => "اكتمل! تم حفظ {$totalStored} دورة جديدة (تم تخطي {$totalSkipped} مكررة)",
            ]);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Send an SSE event to the client.
     */
    private function sendSSE(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

        if (connection_aborted()) return;

        flush();
    }

    /**
     * Stop fetching - clears the stop flag (handled client-side by closing SSE connection).
     */
    public function stop()
    {
        return response()->json(['message' => 'Stopped']);
    }
}
