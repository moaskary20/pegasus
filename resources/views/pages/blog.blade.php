@extends('layouts.site')

@section('title', 'المدونة - ' . config('app.name'))

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10" style="direction: rtl;">
    <h1 class="text-2xl font-extrabold text-slate-900 mb-6">المدونة</h1>

    @if($posts->isEmpty())
        <div class="rounded-3xl border bg-white p-12 text-center">
            <p class="text-slate-600">لا توجد مقالات في المدونة حالياً.</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <a href="{{ route('site.blog.show', $post->slug) }}" class="group block rounded-2xl border bg-white overflow-hidden hover:shadow-lg hover:border-[#3d195c]/30 transition">
                    @if($post->cover_image)
                        <img src="{{ asset('storage/' . ltrim($post->cover_image, '/')) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="w-full h-48 bg-[#3d195c]/10 flex items-center justify-center">
                            <svg class="w-16 h-16 text-[#3d195c]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <h2 class="font-bold text-slate-900 group-hover:text-[#3d195c] transition line-clamp-2">{{ $post->title }}</h2>
                        @if($post->excerpt)
                            <p class="text-sm text-slate-600 mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                        @endif
                        <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                            @if($post->author)
                                <span>{{ $post->author->name }}</span>
                            @endif
                            @if($post->published_at)
                                <span>·</span>
                                <span>{{ $post->published_at->format('Y-m-d') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($posts->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif
    @endif
</section>
@endsection
