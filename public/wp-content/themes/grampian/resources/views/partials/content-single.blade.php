@php
  $thumb_id  = get_post_thumbnail_id();
  $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'hero_image') : null;
@endphp

<article @php(post_class('mx-auto max-w-screen-xl px-4 py-8'))>

  {{-- Featured image --}}
  @if ($thumb_url)
    <div class="mb-6 rounded-lg overflow-hidden">
      <img src="{{ $thumb_url }}" alt="{{ get_the_title() }}" loading="eager" class="w-full object-cover max-h-80" />
    </div>
  @endif

  <header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">
      {!! $title !!}
    </h1>
    @include('partials.entry-meta')
  </header>

  <div class="prose max-w-none">
    @php(the_content())
  </div>

  @if ($pagination)
    <nav class="mt-8 flex justify-center gap-2" aria-label="{{ __('Page', 'sage') }}">
      {!! $pagination !!}
    </nav>
  @endif

  @php(comments_template())
</article>
