@extends('layouts.site')

@section('title', ($post->title ?? 'المقال') . ' - ' . config('app.name'))

@section('content')
<section class="max-w-4xl mx-auto px-4 py-10" style="direction: rtl;">
    <div class="rounded-3xl border bg-white overflow-hidden">
        @if($post->cover_image)
            <img src="{{ asset('storage/' . ltrim($post->cover_image, '/')) }}" alt="{{ $post->title }}" class="w-full h-64 object-cover">
        @endif
        <div class="p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">{{ $post->title }}</h1>
            <div class="mt-4 flex items-center gap-3 text-sm text-slate-600">
                @if($post->author)
                    <span>{{ $post->author->name }}</span>
                @endif
                @if($post->published_at)
                    <span>{{ $post->published_at->format('Y-m-d H:i') }}</span>
                @endif
            </div>
            <div class="mt-6 prose prose-slate max-w-none prose-headings:font-extrabold prose-a:text-[#3d195c]">
                {!! $post->content !!}
            </div>
        </div>
    </div>
    <div class="mt-6">
        <a href="{{ route('site.blog') }}" class="inline-flex items-center gap-2 text-[#3d195c] font-bold hover:underline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            العودة للمدونة
        </a>
    </div>
</section>
@endsection
