@php
  $supporters = new WP_Query([
    'post_type'      => 'supporter',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
  ]);
@endphp

@if($supporters->have_posts())
  <section class="py-10">
    <div class="mx-auto max-w-screen-xl px-4">
      <h2 class="text-2xl font-semibold mb-8">Funders &amp; Supporters</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 items-center">
        @while($supporters->have_posts())
          @php
            $supporters->the_post();
            $logo    = get_field('logo');
            $website = get_field('website');
            $name    = get_the_title();
          @endphp
          <div class="flex items-center justify-center p-4 bg-white border border-gray-100 rounded-lg shadow-sm">
            @if($website)
              <a href="{{ $website }}" target="_blank" rel="noopener" aria-label="{{ $name }}">
            @endif
              @if($logo)
                <img
                  src="{{ $logo['url'] }}"
                  alt="{{ $logo['alt'] ?: $name }}"
                  loading="lazy"
                  class="max-h-16 w-auto object-contain grayscale hover:grayscale-0 transition-all"
                />
              @else
                <span class="text-sm font-medium text-gray-600 text-center">{{ $name }}</span>
              @endif
            @if($website)
              </a>
            @endif
          </div>
        @endwhile
      </div>
    </div>
  </section>
@endif
@php wp_reset_postdata(); @endphp
