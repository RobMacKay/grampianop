@php
  $eyebrow       = $block['eyebrow']       ?? 'Serving the community for over 25 years';
  $heading       = $block['heading']       ?? 'Empowering people to find their way forward';
  $intro         = $block['intro']         ?? 'A friendly place in Inverurie for anyone who needs support, connection or a fresh start. No referral needed to say hello.';
  $image         = $block['image']         ?? null;
  $primary_cta   = $block['primary_cta']   ?? ['url' => '/online-referral', 'title' => 'Get support today', 'target' => ''];
  $secondary_cta = $block['secondary_cta'] ?? ['url' => '/volunteering',    'title' => 'Volunteer with us',  'target' => ''];
  $phone_note    = $block['phone_note']    ?? 'Or call us on <strong>01467 629675</strong> — Monday to Friday.';
  $heading_level = $block['heading_level'] ?? 'h1';
@endphp

<section class="go-block not-prose bg-[linear-gradient(180deg,#EDF6F0_0%,#ffffff_100%)] px-6 py-[72px] pb-[80px]">
  <div class="max-w-[1240px] mx-auto grid gap-16 items-center" style="grid-template-columns: repeat(auto-fit, minmax(min(420px, 100%), 1fr))">

    {{-- Left: copy --}}
    <div class="flex flex-col gap-6">

      {{-- Eyebrow badge --}}
      @if($eyebrow)
        <div class="inline-flex self-start items-center gap-2 bg-white border-2 border-go-green rounded-full px-[18px] py-[9px]">
          <span aria-hidden="true" class="text-go-green-deep">♥</span>
          <span class="font-heading font-bold text-[17px] text-go-green-deep">{{ $eyebrow }}</span>
        </div>
      @endif

      {{-- Heading --}}
      <{{ $heading_level }} class="font-heading font-extrabold text-go-ink leading-[1.04] tracking-[-1.5px]" style="font-size: clamp(42px, 5vw, 66px)">
        {!! $heading !!}
      </{{ $heading_level }}>

      {{-- Intro --}}
      @if($intro)
        <p class="font-body text-[22px] leading-[1.6] text-go-ink max-w-[33ch]">{{ $intro }}</p>
      @endif

      {{-- CTAs --}}
      <div class="flex flex-wrap items-center gap-3">
        @if($primary_cta)
          <a
            href="{{ $primary_cta['url'] }}"
            @if($primary_cta['target']) target="{{ $primary_cta['target'] }}" rel="noopener" @endif
            class="inline-flex items-center px-6 py-[13px] rounded-full bg-go-green-deep text-white font-heading font-bold text-[19px] leading-none hover:bg-go-green-deepest transition-colors"
          >
            {{ $primary_cta['title'] }}
          </a>
        @endif
        @if($secondary_cta)
          <a
            href="{{ $secondary_cta['url'] }}"
            @if($secondary_cta['target']) target="{{ $secondary_cta['target'] }}" rel="noopener" @endif
            class="inline-flex items-center px-6 py-[13px] rounded-full bg-white border-2 border-go-green-deep text-go-green-deep font-heading font-bold text-[19px] leading-none hover:bg-go-mint transition-colors"
          >
            {{ $secondary_cta['title'] }}
          </a>
        @endif
      </div>

      {{-- Phone note --}}
      @if($phone_note)
        <p class="font-body text-[18px] text-go-ink-soft">{!! $phone_note !!}</p>
      @endif

    </div>

    {{-- Right: image with offset panel --}}
    <div class="relative min-w-0 w-full">
      @if($image)
        {{-- Green offset panel --}}
        <div class="absolute bg-go-green rounded-[28px]" style="inset: 18px -18px -18px 18px;" aria-hidden="true"></div>
        <img
          src="{{ $image['sizes']['hero_image'] ?? $image['url'] }}"
          alt="{{ $image['alt'] ?? '' }}"
          class="relative w-full object-cover rounded-[28px] shadow-[0_24px_60px_rgba(20,24,26,0.16)]"
          style="height: 480px;"
          loading="eager"
        />
      @else
        {{-- Placeholder when no image supplied yet --}}
        <div class="relative w-full rounded-[28px] bg-go-mint flex items-center justify-center border-2 border-dashed border-go-line" style="height: 480px;">
          <p class="font-body text-[17px] text-go-ink-soft text-center px-8">Hero image to be supplied by client</p>
        </div>
      @endif
    </div>

  </div>
</section>
