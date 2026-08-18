@php
  $heading          = $block['heading']          ?? "What's on this week";
  $heading_level    = $block['heading_level']    ?? 'h2';
  $archive_link     = $block['archive_link']     ?? ['url' => '/events', 'title' => 'Full events calendar', 'target' => ''];
  $limit            = $block['limit']            ?? 3;
  $fallback_sessions = !empty($block['fallback_sessions']) ? $block['fallback_sessions'] : [
    ['weekday' => 'WED', 'day' => '•',  'title' => 'Community Café',    'detail' => '10:30–12:00 · Home bakes and coffee, every second Wednesday.'],
    ['weekday' => 'THU', 'day' => '•',  'title' => 'Bingo afternoon',   'detail' => '13:00–14:00 · Every Thursday. Just turn up.'],
    ['weekday' => 'WED', 'day' => '•',  'title' => 'Art Group',         'detail' => '13:30–15:30 · All materials provided, no experience needed.'],
  ];

  // Pull live events — one-off (start_date >= today) and active recurring
  $events    = [];
  $today_str = date('Y-m-d');

  // One-off events upcoming from today
  $query_oneoff = new WP_Query([
    'post_type'      => 'event',
    'posts_per_page' => (int) $limit * 3, // fetch extra; we'll trim after merge
    'post_status'    => 'publish',
    'meta_query'     => [
      'relation' => 'AND',
      [
        'key'     => 'event_type',
        'value'   => 'one_off',
        'compare' => '=',
      ],
      [
        'key'     => 'start_date',
        'value'   => $today_str,
        'compare' => '>=',
        'type'    => 'DATE',
      ],
    ],
    'orderby'  => 'meta_value',
    'meta_key' => 'start_date',
    'order'    => 'ASC',
  ]);

  if ($query_oneoff->have_posts()) {
    while ($query_oneoff->have_posts()) {
      $query_oneoff->the_post();
      $card = \App\EventRecurrence::nextCard(get_the_ID());
      if ($card) $events[] = $card;
    }
    wp_reset_postdata();
  }

  // Recurring events that haven't ended yet
  $query_recurring = new WP_Query([
    'post_type'      => 'event',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
      'relation' => 'OR',
      [
        'key'     => 'event_type',
        'value'   => 'recurring',
        'compare' => '=',
      ],
      // Backwards compat: older posts saved with true_false `recurring` field
      [
        'key'     => 'recurring',
        'value'   => '1',
        'compare' => '=',
      ],
    ],
  ]);

  if ($query_recurring->have_posts()) {
    while ($query_recurring->have_posts()) {
      $query_recurring->the_post();
      $card = \App\EventRecurrence::nextCard(get_the_ID());
      if ($card) $events[] = $card;
    }
    wp_reset_postdata();
  }

  // Sort merged list by next occurrence, then take the requested limit
  if (!empty($events)) {
    usort($events, fn($a, $b) => $a['sort_ts'] <=> $b['sort_ts']);
    $events = array_slice($events, 0, (int) $limit);
  }

  $items = !empty($events) ? $events : $fallback_sessions;
@endphp

<section class="go-block not-prose bg-white px-6 py-[84px]">
  <div class="max-w-[1240px] mx-auto">

    {{-- Header row --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-10">
      <{{ $heading_level }} class="font-heading font-bold text-go-ink tracking-[-0.8px]" style="font-size: clamp(32px, 3.4vw, 44px)">
        {!! $heading !!}
      </{{ $heading_level }}>
      @if($archive_link)
        <a
          href="{{ $archive_link['url'] }}"
          @if(!empty($archive_link['target'])) target="{{ $archive_link['target'] }}" rel="noopener" @endif
          class="font-heading font-bold text-[17px] text-go-green-deep hover:underline transition-colors"
        >
          {{ $archive_link['title'] }} →
        </a>
      @endif
    </div>

    {{-- Event cards --}}
    <div class="grid gap-[22px]" style="grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr))">
      @foreach($items as $item)
        @php $card_tag = !empty($item['url']) ? 'a' : 'div'; $card_href = $item['url'] ?? ''; @endphp
        <{{ $card_tag }}
          @if($card_href) href="{{ $card_href }}" @endif
          class="flex items-start gap-[22px] p-[26px] rounded-[20px] bg-go-mint border-2 border-go-mint hover:shadow-[0_12px_30px_rgba(20,24,26,0.10)] transition-shadow {{ $card_href ? 'hover:border-go-green' : '' }}"
        >

          {{-- Date chip --}}
          <div class="flex flex-col items-center justify-center bg-white rounded-[14px] shrink-0 py-3" style="width: 82px;" aria-hidden="true">
            <span class="font-heading font-bold text-[15px] text-go-green-deep uppercase tracking-[1px]">{{ $item['weekday'] }}</span>
            <span class="font-heading font-extrabold text-[30px] text-go-ink leading-none">{{ $item['day'] }}</span>
          </div>

          {{-- Details --}}
          <div class="flex flex-col gap-1.5">
            <h3 class="font-heading font-bold text-[22px] text-go-ink leading-[1.2]">{{ $item['title'] }}</h3>
            @if(!empty($item['detail']))
              <p class="font-body text-[18px] text-go-ink-soft leading-[1.55]">{{ $item['detail'] }}</p>
            @endif
          </div>

        </{{ $card_tag }}>
      @endforeach
    </div>

  </div>
</section>
