@php
  $heading       = $block['heading']       ?? 'Where would you like to start?';
  $intro         = $block['intro']         ?? 'Pick the line that sounds most like you. Every route leads to a real person, not a form letter.';
  $heading_level = $block['heading_level'] ?? 'h2';
  $cards         = !empty($block['cards']) ? $block['cards'] : [
    [
      'number' => '1',
      'title'  => 'I need support for myself',
      'text'   => 'Day services, wellbeing activities, money and IT help.',
      'label'  => 'Find your support',
      'link'   => ['url' => '/online-referral', 'target' => ''],
      'accent' => 'green',
    ],
    [
      'number' => '2',
      'title'  => 'I\'m a carer, parent or guardian',
      'text'   => 'Free to Be Me, self-directed support and a group that listens.',
      'label'  => 'Support for carers',
      'link'   => ['url' => '/sird-carers-guardians', 'target' => ''],
      'accent' => 'green',
    ],
    [
      'number' => '3',
      'title'  => 'I\'m referring someone',
      'text'   => 'One short online form, and we\'ll be in touch within five working days.',
      'label'  => 'Online referral',
      'link'   => ['url' => '/online-referral', 'target' => ''],
      'accent' => 'blue',
    ],
    [
      'number' => '4',
      'title'  => 'I want to help',
      'text'   => 'Volunteer a few hours, fundraise, or fund a programme.',
      'label'  => 'Get involved',
      'link'   => ['url' => '/volunteering', 'target' => ''],
      'accent' => 'red',
    ],
  ];

  $accent_chip = [
    'green' => 'bg-go-green-deep',
    'blue'  => 'bg-go-blue',
    'red'   => 'bg-go-red',
  ];
  $accent_label = [
    'green' => 'text-go-green-deep',
    'blue'  => 'text-go-blue',
    'red'   => 'text-go-red',
  ];
@endphp

<section class="go-block not-prose bg-white px-6 py-[84px]">
  <div class="max-w-[1240px] mx-auto">

    {{-- Section header --}}
    <div class="mb-10 max-w-[60ch]">
      <{{ $heading_level }} class="font-heading font-bold text-go-ink mb-4 tracking-[-0.8px]" style="font-size: clamp(32px, 3.4vw, 44px)">
        {!! $heading !!}
      </{{ $heading_level }}>
      @if($intro)
        <p class="font-body text-[20px] leading-[1.6] text-go-ink-soft">{{ $intro }}</p>
      @endif
    </div>

    {{-- Cards grid --}}
    <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(min(240px, 100%), 1fr))">
      @foreach($cards as $card)
        @php
          $accent = $card['accent'] ?? 'green';
          $url    = $card['link']['url'] ?? '#';
          $target = $card['link']['target'] ?? '';
        @endphp
        <a
          href="{{ $url }}"
          @if($target) target="{{ $target }}" rel="noopener" @endif
          class="group flex flex-col gap-4 p-[30px_26px_26px] rounded-[20px] bg-white border-2 border-go-line hover:border-go-green hover:bg-go-mint transition-colors"
        >
          {{-- Number chip --}}
          <div
            class="flex items-center justify-center w-[52px] h-[52px] rounded-[14px] {{ $accent_chip[$accent] ?? 'bg-go-green-deep' }}"
            aria-hidden="true"
          >
            <span class="font-heading font-bold text-[24px] text-white leading-none">{{ $card['number'] }}</span>
          </div>

          {{-- Title --}}
          <h3 class="font-heading font-bold text-[23px] text-go-ink leading-[1.2]">{{ $card['title'] }}</h3>

          {{-- Body --}}
          <p class="font-body text-[18px] text-go-ink-soft leading-[1.55] grow">{{ $card['text'] }}</p>

          {{-- CTA label --}}
          <span class="mt-auto font-heading font-bold text-[17px] underline {{ $accent_label[$accent] ?? 'text-go-green-deep' }}">
            {{ $card['label'] }} →
          </span>
        </a>
      @endforeach
    </div>

  </div>
</section>
