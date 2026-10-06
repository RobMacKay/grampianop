@php
  $heading          = $block['heading']          ?? "What's on this week";
  $heading_level    = $block['heading_level']    ?? 'h2';
  $archive_link     = $block['archive_link']     ?? ['url' => '/events', 'title' => 'Full events calendar', 'target' => ''];
  $limit            = (int) ($block['limit'] ?? 3);
  $empty_message    = $block['empty_message'] ?? 'There are no upcoming events right now — check back soon.';

  $items = \App\Content::upcomingEvents($limit ?: 3);
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
    @if(empty($items))
      <div class="p-[26px] rounded-[20px] bg-go-mint border-2 border-go-mint">
        <p class="font-body text-[18px] text-go-ink-soft leading-[1.55]">{{ $empty_message }}</p>
      </div>
    @else
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
    @endif

  </div>
</section>
