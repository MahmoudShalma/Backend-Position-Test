@extends('layouts.app')

@section('nav-stats')
    @if($totalCount > 0)
        <div class="stat-item">
            <i class="fas fa-play-circle"></i>
            <span>{{ $totalCount }} دورة</span>
        </div>
        <div class="stat-item">
            <i class="fas fa-layer-group"></i>
            <span>{{ $categories->count() }} تصنيف</span>
        </div>
    @endif
@endsection

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-badge">
            <i class="fas fa-robot"></i>
            مدعوم بالذكاء الاصطناعي
        </div>
        <h1>اكتشف أفضل الدورات التعليمية من يوتيوب</h1>
        <p>أدخل التصنيفات واترك النظام يبحث لك عن أفضل الكورسات والقوائم التعليمية</p>
    </div>
</section>

<!-- Input Section -->
<div class="container" style="max-width: 680px;">
    <div class="input-section">
        <form id="fetchForm">
            @csrf
            <label for="categories">
                <i class="fas fa-tags"></i>
                أدخل التصنيفات (كل تصنيف في سطر جديد)
            </label>
            <textarea
                class="form-control"
                id="categories"
                name="categories"
                rows="5"
                placeholder="مثال:&#10;البرمجة&#10;التسويق الرقمي&#10;الجرافيكس&#10;الهندسة"
            ></textarea>

            <div class="btn-actions">
                <button type="submit" class="btn-fetch" id="btnFetch">
                    <i class="fas fa-search"></i>
                    ابدأ البحث
                </button>

                <button type="button" class="btn-stop" id="btnStop" style="display:none;">
                    <i class="fas fa-stop"></i>
                    إيقاف
                </button>
            </div>
        </form>

        <!-- Progress Section -->
        <div class="progress-section" id="progressSection">
            <div class="progress-header">
                <span class="progress-percent" id="progressPercent">0%</span>
                <span class="progress-label" id="progressLabel">جاري البحث...</span>
            </div>
            <div class="progress-bar-custom">
                <div class="progress-bar-fill" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="progress-text" id="progressText">
                <i class="fas fa-circle-notch"></i>
                جاري التحضير...
            </div>
            <div class="progress-log" id="progressLog"></div>
        </div>
    </div>
</div>

<!-- Courses Section -->
@if($totalCount > 0)
<section class="courses-section">
    <div class="container">
        <div class="courses-header">
            <h2>
                <span class="count-badge">{{ $totalCount }}</span>
                الدورات المكتشفة
            </h2>
            <span class="subtitle">تم العثور على {{ $totalCount }} دورة في {{ $categories->count() }} تصنيفات</span>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs">
            <a href="{{ route('courses.index') }}"
               class="category-tab {{ !$categoryFilter || $categoryFilter === 'all' ? 'active' : '' }}">
                الكل
                <span class="tab-count">{{ $totalCount }}</span>
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('courses.index', ['category' => $cat->category]) }}"
                   class="category-tab {{ $categoryFilter === $cat->category ? 'active' : '' }}">
                    {{ $cat->category }}
                    <span class="tab-count">{{ $cat->count }}</span>
                </a>
            @endforeach
        </div>

        <!-- Course Cards Grid -->
        <div class="row g-4">
            @foreach($playlists as $index => $playlist)
                <div class="col-xl-3 col-lg-4 col-md-6 fade-in-up" style="animation-delay: {{ $index * 0.06 }}s">
                    <div class="course-card">
                        <div class="card-img-wrapper">
                            <a href="{{ $playlist->youtube_url }}" target="_blank" rel="noopener">
                                <img src="{{ $playlist->thumbnail }}" alt="{{ $playlist->title }}" loading="lazy">
                                <div class="overlay"></div>
                                <div class="play-btn">
                                    <i class="fas fa-play"></i>
                                </div>
                            </a>
                            @if($playlist->video_count > 0)
                                <span class="video-count">
                                    <i class="fas fa-list-ul"></i>
                                    {{ $playlist->video_count }} درس
                                </span>
                            @endif
                            @if($playlist->total_duration)
                                <span class="duration-badge">
                                    <i class="fas fa-clock"></i>
                                    {{ $playlist->total_duration }}
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            <a href="{{ $playlist->youtube_url }}" target="_blank" rel="noopener" class="card-title">
                                {{ html_entity_decode($playlist->title) }}
                            </a>
                            <div class="channel-name">
                                <i class="fas fa-user-circle"></i>
                                {{ $playlist->channel_name }}
                            </div>
                            <div class="card-footer-custom">
                                @if($playlist->view_count > 0)
                                    <span class="view-count">
                                        <i class="fas fa-eye"></i>
                                        {{ $playlist->formatted_view_count }} مشاهدة
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <span class="category-badge">{{ $playlist->category }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($playlists->hasPages())
            <div class="pagination-wrapper">
                {{ $playlists->links() }}
            </div>
        @endif
    </div>
</section>
@else
<section class="courses-section">
    <div class="container">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h4>لا توجد دورات بعد</h4>
            <p>أدخل التصنيفات واضغط "ابدأ البحث" لاكتشاف أفضل الدورات التعليمية من يوتيوب</p>
        </div>
    </div>
</section>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('fetchForm');
    const btnFetch = document.getElementById('btnFetch');
    const btnStop = document.getElementById('btnStop');
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressLog = document.getElementById('progressLog');
    const progressPercent = document.getElementById('progressPercent');
    const progressLabel = document.getElementById('progressLabel');

    let totalCategories = 0;
    let completedCategories = 0;
    let abortController = null;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const categories = document.getElementById('categories').value.trim();
        if (!categories) {
            // Shake the textarea
            const ta = document.getElementById('categories');
            ta.style.borderColor = 'var(--primary)';
            ta.style.animation = 'shake 0.4s ease';
            setTimeout(() => { ta.style.animation = ''; ta.style.borderColor = ''; }, 500);
            return;
        }
        startFetching(categories);
    });

    btnStop.addEventListener('click', function() {
        stopFetching();
    });

    function startFetching(categories) {
        btnFetch.disabled = true;
        btnFetch.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري البحث...';
        btnStop.style.display = 'inline-flex';
        progressSection.classList.add('active');
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressLabel.textContent = 'جاري البحث...';
        progressLog.innerHTML = '';
        completedCategories = 0;

        abortController = new AbortController();

        const formData = new FormData();
        formData.append('categories', categories);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('{{ route("courses.fetch") }}', {
            method: 'POST',
            body: formData,
            signal: abortController.signal,
        }).then(response => {
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            function read() {
                reader.read().then(({ done, value }) => {
                    if (done) { onComplete(); return; }

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop();

                    let currentEvent = '';
                    for (const line of lines) {
                        if (line.startsWith('event: ')) {
                            currentEvent = line.substring(7);
                        } else if (line.startsWith('data: ')) {
                            try {
                                const data = JSON.parse(line.substring(6));
                                handleEvent(currentEvent, data);
                            } catch(e) {}
                        }
                    }
                    read();
                }).catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Stream error:', err);
                    }
                    onComplete();
                });
            }
            read();
        }).catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Fetch error:', err);
                addLog('حدث خطأ في الاتصال', 'error');
            }
            onComplete();
        });
    }

    function handleEvent(event, data) {
        switch(event) {
            case 'start':
                totalCategories = data.total_categories;
                addLog(data.message, 'info');
                break;

            case 'category_start':
                progressLabel.textContent = data.message;
                progressText.innerHTML = '<i class="fas fa-circle-notch"></i> ' + data.message;
                addLog(data.message, 'info');
                break;

            case 'titles_generated':
                addLog(data.message, 'success');
                break;

            case 'searching':
                progressText.innerHTML = '<i class="fas fa-circle-notch"></i> ' + data.message;
                break;

            case 'category_done':
                completedCategories++;
                const percent = Math.round((completedCategories / totalCategories) * 100);
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
                addLog(data.message, 'success');
                break;

            case 'category_error':
                addLog(data.message, 'error');
                break;

            case 'complete':
                progressBar.style.width = '100%';
                progressPercent.textContent = '100%';
                progressLabel.textContent = 'اكتمل!';
                progressText.innerHTML = '<i class="fas fa-check-circle" style="color:#059669;animation:none"></i> ' + data.message;
                addLog(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
                break;
        }
    }

    function addLog(message, type) {
        const div = document.createElement('div');
        div.className = 'log-entry ' + (type || '');
        div.textContent = message;
        progressLog.prepend(div);
    }

    function stopFetching() {
        if (abortController) {
            abortController.abort();
        }
        fetch('{{ route("courses.stop") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });
        addLog('تم إيقاف البحث', 'error');
        onComplete();
    }

    function onComplete() {
        btnFetch.disabled = false;
        btnFetch.innerHTML = '<i class="fas fa-search"></i> ابدأ البحث';
        btnStop.style.display = 'none';
        completedCategories = 0;
        abortController = null;
    }
});
</script>
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    75% { transform: translateX(8px); }
}
</style>
@endsection
