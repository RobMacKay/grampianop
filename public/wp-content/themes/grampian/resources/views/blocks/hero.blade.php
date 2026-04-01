@php
  $image     = $block['image'] ?? null;
  $title     = $block['title'] ?: get_the_title();
  $copyright = $block['copyright'] ?? null;
  $intro     = $block['intro'] ?? null;
@endphp

@if($image)
  <div class="relative w-full">
    <img
      src="{{ $image['sizes']['hero_image'] ?? $image['url'] }}"
      alt="{{ $image['alt'] ?: $title }}"
      width="1200"
      height="500"
      loading="eager"
      class="w-full object-cover max-h-96 object-center"
    />
    @if($copyright)
      <small class="absolute bottom-1 right-2 text-white/70 text-xs">&copy; {{ $copyright }}</small>
    @endif
  </div>
@endif

@if($intro)
  <div class="mx-auto max-w-screen-xl px-4 pt-8 pb-2">
    <div class="prose max-w-none text-lg text-gray-600">
      {!! $intro !!}
    </div>
  </div>
@endif
