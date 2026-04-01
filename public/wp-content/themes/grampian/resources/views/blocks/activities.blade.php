@php
  $title      = $block['title'] ?? null;
  $intro      = $block['intro'] ?? null;
  $activities = $block['activities'] ?? [];
@endphp

@if($title || $intro || $activities)
  <section class="py-12">
    <div class="mx-auto max-w-screen-xl px-4">

      @if($title || $intro)
        <div class="mb-16 text-center">
          @if($title)
            <h2 class="text-3xl font-bold text-gray-900 mb-3">{{ $title }}</h2>
          @endif
          @if($intro)
            <p class="text-gray-500 max-w-2xl mx-auto">{!! nl2br(esc_html($intro)) !!}</p>
          @endif
        </div>
      @endif

      @if($activities)
        <div
          id="activities-cards"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-12"
          x-data="{ open: null }"
        >
          @foreach($activities as $activity)
            @php setup_postdata($GLOBALS['post'] = $activity); @endphp
            @include('components.activity-card', [
              'title'     => get_the_title($activity),
              'permalink' => get_permalink($activity),
              'index'     => $loop->index,
            ])
          @endforeach
          @php wp_reset_postdata(); @endphp
        </div>
      @endif

    </div>
  </section>
@endif
