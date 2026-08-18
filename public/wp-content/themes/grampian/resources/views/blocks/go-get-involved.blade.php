@php
  $heading       = $block['heading']       ?? 'Two ways to make a difference';
  $heading_level = $block['heading_level'] ?? 'h2';
  $donate_url    = get_field('donate_url', 'option') ?: 'https://checkout.justgiving.com/c/3088502';
  $panels        = !empty($block['panels']) ? $block['panels'] : [
    [
      'theme'   => 'green',
      'eyebrow' => 'VOLUNTEERING',
      'title'   => 'A few hours a week changes someone\'s month',
      'text'    => 'Our volunteers run sessions, provide transport, offer befriending and help behind the scenes. Whatever your skills, there\'s a way to contribute.',
      'note'    => 'Currently recruiting · Inverurie',
      'cta'     => ['url' => '/volunteering', 'title' => 'See volunteer roles', 'target' => ''],
    ],
    [
      'theme'   => 'red',
      'eyebrow' => 'DONATE',
      'title'   => '£15 keeps the café kettle on for a morning',
      'text'    => 'Every pound we raise goes directly into our programmes — no admin overheads, no hidden costs. We\'re entirely grant and donation funded.',
      'note'    => 'Registered charity SC030396',
      'cta'     => ['url' => $donate_url, 'title' => 'Donate now', 'target' => '_blank'],
    ],
  ];
@endphp

<section class="go-block not-prose bg-go-cream px-6 py-[84px]">
  <div class="max-w-[1240px] mx-auto">

    {{-- Section heading --}}
    <{{ $heading_level }} class="font-heading font-bold text-go-ink tracking-[-0.8px] mb-10" style="font-size: clamp(32px, 3.4vw, 44px)">
      {!! $heading !!}
    </{{ $heading_level }}>

    {{-- Panels grid --}}
    <div class="grid gap-[26px]" style="grid-template-columns: repeat(auto-fit, minmax(min(340px, 100%), 1fr))">
      @foreach($panels as $panel)
        @php $theme = $panel['theme'] ?? 'green'; @endphp

        @if($theme === 'red')
          {{-- Donate panel --}}
          <div class="flex flex-col gap-[18px] p-[40px_36px] rounded-[22px] bg-go-red">
            @if(!empty($panel['eyebrow']))
              <p class="font-heading font-bold text-[16px] uppercase tracking-[1.4px] text-go-red-pale">{{ $panel['eyebrow'] }}</p>
            @endif
            <h3 class="font-heading font-bold text-[30px] text-white leading-[1.2] tracking-[-0.6px]">{!! $panel['title'] !!}</h3>
            @if(!empty($panel['text']))
              <p class="font-body text-[19px] leading-[1.6] text-go-red-pale">{{ $panel['text'] }}</p>
            @endif
            @if(!empty($panel['note']))
              <p class="font-body text-[17px] text-go-red-pale">{{ $panel['note'] }}</p>
            @endif
            @if(!empty($panel['cta']))
              <a
                href="{{ $panel['cta']['url'] }}"
                @if(!empty($panel['cta']['target'])) target="{{ $panel['cta']['target'] }}" rel="noopener" @endif
                class="self-start mt-auto inline-flex items-center px-6 py-[13px] rounded-full bg-white text-go-red font-heading font-bold text-[17px] leading-none hover:bg-go-red-pale transition-colors"
              >
                {{ $panel['cta']['title'] }}
              </a>
            @endif
          </div>

        @else
          {{-- Volunteering (green) panel --}}
          <div class="flex flex-col gap-[18px] p-[40px_36px] rounded-[22px] bg-white border border-go-line">
            @if(!empty($panel['eyebrow']))
              <p class="font-heading font-bold text-[16px] uppercase tracking-[1.4px] text-go-green-deep">{{ $panel['eyebrow'] }}</p>
            @endif
            <h3 class="font-heading font-bold text-[30px] text-go-ink leading-[1.2] tracking-[-0.6px]">{!! $panel['title'] !!}</h3>
            @if(!empty($panel['text']))
              <p class="font-body text-[19px] leading-[1.6] text-go-ink-soft">{{ $panel['text'] }}</p>
            @endif
            @if(!empty($panel['note']))
              <p class="font-body text-[17px] text-go-ink-soft">{{ $panel['note'] }}</p>
            @endif
            @if(!empty($panel['cta']))
              <a
                href="{{ $panel['cta']['url'] }}"
                @if(!empty($panel['cta']['target'])) target="{{ $panel['cta']['target'] }}" rel="noopener" @endif
                class="self-start mt-auto inline-flex items-center px-6 py-[13px] rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] leading-none hover:bg-go-green-deepest transition-colors"
              >
                {{ $panel['cta']['title'] }}
              </a>
            @endif
          </div>
        @endif

      @endforeach
    </div>

  </div>
</section>
