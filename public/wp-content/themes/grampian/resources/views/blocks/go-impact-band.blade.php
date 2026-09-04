@php
  $image         = $block['image']         ?? null;
  $heading       = $block['heading']       ?? '25 years of people helping people in Aberdeenshire';
  $heading_level = $block['heading_level'] ?? 'h2';
  $intro         = $block['intro']         ?? 'We started with one day service and a borrowed room. Today we run eight weekly groups and support hundreds of people across Aberdeenshire every year — always free at the point of need.';
  $stats         = !empty($block['stats']) ? $block['stats'] : [
    ['figure' => '25+',  'label' => 'Years in Inverurie'],
    ['figure' => '8',    'label' => 'Groups running weekly'],
    ['figure' => '£0',   'label' => 'Cost to walk through the door'],
  ];
  $quote         = $block['quote']         ?? '"Coming here changed everything. I went from feeling completely isolated to having friends and a reason to get up in the morning."';
  $attribution   = $block['attribution']   ?? 'Day Services member';
@endphp

<section class="go-block not-prose bg-go-green-deep text-white px-6 py-[84px]">
  <div class="max-w-[1240px] mx-auto grid gap-16 items-center" style="grid-template-columns: repeat(auto-fit, minmax(min(380px, 100%), 1fr))">

    {{-- Left: image --}}
    <div class="min-w-0 w-full">
      @if($image)
        <img
          src="{{ $image['sizes']['hero_image'] ?? $image['url'] }}"
          alt="{{ $image['alt'] ?? '' }}"
          class="w-full object-cover rounded-[24px]"
          style="height: 400px;"
          loading="lazy"
        />
      @else
        <div class="w-full rounded-[24px] bg-go-green-deepest border-2 border-dashed border-go-green-light flex items-center justify-center" style="height: 400px;">
          <p class="font-body text-[17px] text-go-green-pale text-center px-8">Impact image to be supplied by client</p>
        </div>
      @endif
    </div>

    {{-- Right: copy --}}
    <div class="flex flex-col gap-7">

      {{-- Heading --}}
      <{{ $heading_level }} class="font-heading font-bold text-white tracking-[-0.8px]" style="font-size: clamp(32px, 3.4vw, 44px)">
        {!! $heading !!}
      </{{ $heading_level }}>

      {{-- Intro --}}
      @if($intro)
        <p class="font-body text-[21px] leading-[1.65] text-go-green-pale max-w-[52ch]">{{ $intro }}</p>
      @endif

      {{-- Stats dl --}}
      @if($stats)
        <dl class="grid gap-6 pt-2 border-t border-white/25" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr))">
          @foreach($stats as $stat)
            <div class="flex flex-col gap-2 pt-4">
              <dd class="font-heading font-extrabold text-go-green-light leading-none" style="font-size: 52px;">{{ $stat['figure'] }}</dd>
              <dt class="font-body text-[17px] leading-[1.45] text-go-green-pale">{{ $stat['label'] }}</dt>
            </div>
          @endforeach
        </dl>
      @endif

      {{-- Quote plate --}}
      @if($quote)
        <blockquote class="bg-go-green-deepest rounded-[18px] px-[30px] py-[26px]">
          <p class="font-body text-[20px] leading-[1.6] text-white">{!! $quote !!}</p>
          @if($attribution)
            <footer class="mt-3 font-body text-[17px] text-go-green-pale">— {{ $attribution }}</footer>
          @endif
        </blockquote>
      @endif

    </div>

  </div>
</section>
