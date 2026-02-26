<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    private function baseUrl(): string
    {
        return rtrim(config('app.url', request()->getSchemeAndHttpHost()), '/');
    }

    private function coverUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        $path = trim($path);
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $url = asset('storage/' . ltrim($path, '/'));
        if (str_starts_with((string) $url, 'http')) {
            return $url;
        }
        return $this->baseUrl() . '/' . ltrim((string) $url, '/');
    }

    /**
     * GET list of published blog posts (for mobile)
     */
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(30, max(1, (int) $request->query('per_page', 15)));

        $posts = BlogPost::query()
            ->published()
            ->with('author:id,name,avatar')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $data = $posts->map(fn ($p) => $this->formatPost($p))->values()->all();

        return response()->json([
            'posts' => $data,
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'per_page' => $posts->perPage(),
            'total' => $posts->total(),
        ]);
    }

    /**
     * GET single blog post by slug
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->with('author:id,name,avatar')
            ->first();

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($this->formatPost($post, true));
    }

    private function formatPost(BlogPost $post, bool $full = false): array
    {
        $coverUrl = null;
        if ($post->cover_image) {
            $coverUrl = $this->coverUrl($post->cover_image);
        }

        $data = [
            'id' => $post->id,
            'title' => $post->title ?? '',
            'slug' => $post->slug ?? '',
            'excerpt' => $post->excerpt ?? '',
            'cover_image' => $coverUrl,
            'published_at' => $post->published_at?->toIso8601String(),
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name ?? '',
                'avatar' => $post->author->avatar ? $this->coverUrl(ltrim($post->author->avatar, '/')) : null,
            ] : null,
        ];

        if ($full) {
            $data['content'] = $post->content ?? '';
        }

        return $data;
    }
}
