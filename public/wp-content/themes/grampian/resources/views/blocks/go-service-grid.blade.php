@php
  $eyebrow       = $block['eyebrow']       ?? 'WHAT WE DO';
  $heading       = $block['heading']       ?? 'Groups and services running right now';
  $heading_level = $block['heading_level'] ?? 'h2';
  $cta           = $block['cta']           ?? ['url' => '/what-we-do', 'title' => 'See everything we do', 'target' => ''];
  $highlight_panels = !empty($block['highlight_panels']) ? $block['highlight_panels'] : [
    [
      'title' => 'Information and Guidance',
      'text'  => 'Practical help navigating benefits, housing, and local services — at your own pace.',
      'link'  => ['url' => '/information-and-guidance', 'target' => ''],
    ],
    [
      'title' => 'Financial First Aid &amp; IT Buddy',
      'text'  => 'One-to-one support with money worries or getting online — no judgement, just help.',
      'link'  => ['url' => '/financial-first-aid-it-buddy-support', 'target' => ''],
    ],
  ];

  $source   = \App\Content::resolveSource($block['source'] ?? null, $block['activities'] ?? []);
  $services = \App\Content::activityCards($source, $block['activities'] ?? [], (int) ($block['limit'] ?? 0));
@endphp

<section class="go-block not-prose bg-go-cream px-6 py-[84px]">
  <div class="max-w-[1240px] mx-auto">

    {{-- Section header row --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-12">
      <div>
        @if($eyebrow)
          <p class="font-heading font-bold text-[17px] uppercase tracking-[1.4px] text-go-green-deep mb-3">{{ $eyebrow }}</p>
        @endif
        <{{ $heading_level }} class="font-heading font-bold text-go-ink tracking-[-0.8px] max-w-[24ch]" style="font-size: clamp(32px, 3.4vw, 44px)">
          {!! $heading !!}
        </{{ $heading_level }}>
      </div>
      @if($cta)
        <a
          href="{{ $cta['url'] }}"
          @if(!empty($cta['target'])) target="{{ $cta['target'] }}" rel="noopener" @endif
          class="shrink-0 self-start inline-flex items-center px-[26px] py-4 rounded-full border-2 border-go-green-deep text-go-green-deep font-heading font-bold text-[18px] leading-none hover:bg-go-green-deep hover:text-white transition-colors"
        >
          {{ $cta['title'] }}
        </a>
      @endif
    </div>

    {{-- Programme circles grid — one card per published Activity --}}
    @if(empty($services))
      @if($is_preview ?? false)
        <p class="font-body text-[18px] text-go-ink-soft mb-10">
          No published activities yet — add some under <strong>Activities</strong>.
        </p>
      @endif
    @else
      <div class="grid gap-x-8 gap-y-11 mb-10" style="grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr))">
        @foreach($services as $service)
          <a
            href="{{ $service['url'] }}"
            class="group flex flex-col items-center text-center gap-3.5 px-3 pt-5 pb-6 rounded-[26px] border-2 border-transparent hover:border-go-green hover:shadow-[0_12px_30px_rgba(20,24,26,0.10)] transition-all"
          >
            {{-- Illustration: fluid, capped at its native 248px, always square --}}
            @if($service['image'])
              <div class="block w-full max-w-[248px]">
                <img src="{{ $service['image'] }}" alt="{{ $service['image_alt'] }}" width="248" height="248" class="block w-full h-auto aspect-square object-contain" loading="lazy">
              </div>
            @endif

            <h3 class="font-heading font-bold text-[25px] text-go-ink leading-[1.25]">{{ $service['title'] }}</h3>

            @if(!empty($service['text']))
              <p class="font-body text-[18px] text-go-ink-soft leading-[1.6] max-w-[30ch] grow">{{ $service['text'] }}</p>
            @endif

            <span class="mt-auto inline-flex items-center gap-2 font-heading font-bold text-[18px] text-go-green-deep underline decoration-2 underline-offset-4">
              {{ $service['cta'] }}<span aria-hidden="true">→</span>
            </span>
          </a>
        @endforeach
      </div>
    @endif

    {{-- Highlight panels --}}
    @if($highlight_panels)
      <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(min(340px, 100%), 1fr))">
        @foreach($highlight_panels as $panel)
          @php
            $panel_url    = $panel['link']['url']    ?? '#';
            $panel_target = $panel['link']['target'] ?? '';
          @endphp
          <a
            href="{{ $panel_url }}"
            @if($panel_target) target="{{ $panel_target }}" rel="noopener" @endif
            class="flex flex-col gap-3.5 p-[32px_30px] rounded-[22px] bg-white border border-go-line border-l-[8px] border-l-go-green hover:shadow-[0_12px_30px_rgba(20,24,26,0.10)] transition-shadow"
          >
            <h3 class="font-heading font-bold text-[26px] text-go-ink">{!! $panel['title'] !!}</h3>
            @if(!empty($panel['text']))
              <p class="font-body text-[18px] text-go-ink-soft leading-[1.6]">{{ $panel['text'] }}</p>
            @endif
            <span class="font-heading font-bold text-[18px] text-go-green-deep underline">
              {!! $panel['link']['title'] ?? $panel['title'] !!}<span aria-hidden="true">&nbsp;→</span>
            </span>
          </a>
        @endforeach
      </div>
    @endif

  </div>
</section>
