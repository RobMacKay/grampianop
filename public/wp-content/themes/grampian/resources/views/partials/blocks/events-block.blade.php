@php
  $events = new WP_Query([
    'post_type'      => 'event',
    'posts_per_page' => 6,
    'meta_key'       => 'start_date',
    'meta_value'     => date('Y-m-d H:i:s'),
    'meta_compare'   => '>=',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
  ]);
@endphp

<section class="py-10">
  <div class="mx-auto max-w-screen-xl px-4">
    <h2 class="text-2xl font-semibold mb-6">Upcoming Events</h2>
    @if($events->have_posts())
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @while($events->have_posts())
          @php $events->the_post(); @endphp
          @include('components.event-card', [
            'title'      => get_the_title(),
            'permalink'  => get_permalink(),
            'start_date' => get_field('start_date'),
            'end_date'   => get_field('end_date'),
            'location'   => get_field('location'),
            'all_day'    => get_field('all_day'),
          ])
        @endwhile
      </div>
    @else
      <p class="text-gray-500">No upcoming events at the moment. Check back soon!</p>
    @endif
  </div>
</section>
@php wp_reset_postdata(); @endphp
