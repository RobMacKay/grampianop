@php $items = \App\Content::upcomingEvents(6); @endphp

<section class="go-block not-prose bg-white px-6 py-[72px]">
  <div class="max-w-[1240px] mx-auto">
    <h2 class="font-heading font-bold text-go-ink tracking-[-0.8px] mb-8" style="font-size: clamp(30px, 3vw, 40px)">Upcoming events</h2>

    @if($items)
      <div class="grid gap-[22px]" style="grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr))">
        @foreach($items as $item)
          <a href="{{ $item['url'] }}"
             class="flex items-start gap-[22px] p-[26px] rounded-[20px] bg-go-mint border-2 border-go-mint hover:border-go-green hover:shadow-[0_12px_30px_rgba(20,24,26,0.10)] transition-shadow">
            <div class="flex flex-col items-center justify-center bg-white rounded-[14px] shrink-0 py-3" style="width: 82px;" aria-hidden="true">
              <span class="font-heading font-bold text-[15px] text-go-green-deep uppercase tracking-[1px]">{{ $item['weekday'] }}</span>
              <span class="font-heading font-extrabold text-[30px] text-go-ink leading-none">{{ $item['day'] }}</span>
            </div>
            <div class="flex flex-col gap-1.5">
              <h3 class="font-heading font-bold text-[22px] text-go-ink leading-[1.2]">{{ $item['title'] }}</h3>
              @if(!empty($item['detail']))
                <p class="font-body text-[18px] text-go-ink-soft leading-[1.55]">{{ $item['detail'] }}</p>
              @endif
            </div>
          </a>
        @endforeach
      </div>
    @else
      <p class="font-body text-[18px] text-go-ink-soft">There are no upcoming events right now — check back soon.</p>
    @endif
  </div>
</section>
