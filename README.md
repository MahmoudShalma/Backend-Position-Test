# YouTube Course Scraper

A Laravel web application that discovers educational YouTube playlists using AI-generated search queries and displays them in a clean, Arabic RTL interface.

## How It Works

1. **Category Input** — User enters categories (e.g. Marketing, Programming, Graphic Design)
2. **AI Title Generation** — Gemini AI generates 15 realistic course titles per category
3. **YouTube Search** — Each title is searched via YouTube Data API v3, returning 2 playlists per query
4. **Deduplication & Storage** — Results are saved to the database, skipping duplicates based on `playlist_id`

## Requirements

- PHP >= 8.2
- Composer
- MySQL
- Gemini API Key
- YouTube Data API v3 Key

## Installation

```bash
# Clone the repository
git clone https://github.com/MahmoudShalma/Backend-Position-Test
cd Backend-Position-Test

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

## Configuration

### Database

Update your `.env` file with your MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then run migrations:

```bash
php artisan migrate
```

### API Keys

Add the following keys to your `.env` file:

```env
# Gemini AI API Key
# Get it from: https://aistudio.google.com/apikey
GEMINI_API_KEY=your_gemini_api_key_here

# YouTube Data API v3 Key
# Get it from: https://console.cloud.google.com/apis/credentials
# Make sure YouTube Data API v3 is enabled for your project
YOUTUBE_API_KEY=your_youtube_api_key_here
```

## Running the Project

```bash
php artisan serve
```

Then open your browser at: `http://localhost:8000`

## Project Structure

```
app/
├── Http/Controllers/
│   └── CourseController.php          # Main controller (SSE streaming, pagination, filtering)
├── Models/
│   └── Playlist.php                  # Playlist model with accessors & scopes
├── Services/
│   ├── GeminiService.php             # Gemini AI integration (title generation + retry logic)
│   └── YouTubeService.php            # YouTube Data API (search, enrich, duration calculation)
├── Providers/
│   └── AppServiceProvider.php        # Bootstrap paginator configuration

config/
└── services.php                      # API keys configuration

database/migrations/
└── create_playlists_table.php        # Database schema

resources/views/
├── layouts/app.blade.php             # Main layout (Bootstrap 5 RTL + custom CSS)
└── courses/index.blade.php           # Main page (input form, progress, course cards)

routes/
└── web.php                           # Routes (GET /, POST /fetch, POST /stop)
```

## Database Schema

### `playlists` table

| Column         | Type              | Description                      |
|----------------|-------------------|----------------------------------|
| id             | bigint (PK)       | Auto-increment primary key       |
| playlist_id    | string (unique)   | YouTube playlist ID              |
| title          | string            | Course/playlist title            |
| description    | text (nullable)   | Playlist description             |
| thumbnail      | string (nullable) | Thumbnail URL                    |
| channel_name   | string (nullable) | YouTube channel name             |
| category       | string (indexed)  | Category assigned during search  |
| video_count    | unsigned integer  | Number of videos in playlist     |
| total_duration | string (nullable) | Total duration (e.g. "3 ساعة 25 دقيقة") |
| view_count     | unsigned bigint   | Total view count                 |
| created_at     | timestamp         | Record creation time             |
| updated_at     | timestamp         | Record update time               |

## Features

- **AI-Powered Search** — Gemini AI generates relevant Arabic course titles with retry logic and multiple model fallbacks
- **Real-Time Progress** — Server-Sent Events (SSE) stream live progress updates to the browser
- **Deduplication** — Prevents duplicate playlists based on unique `playlist_id`
- **Category Filtering** — Filter discovered courses by category
- **Pagination** — Bootstrap 5 paginated results
- **Responsive Design** — RTL Arabic interface built with Bootstrap 5 and custom CSS
- **Duration Calculation** — Fetches and sums individual video durations per playlist
- **View Count** — Displays formatted view counts (e.g. 1.2K, 3.5M)
