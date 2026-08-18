@php
  $heading       = $block['heading']       ?? 'Come and see us';
  $heading_level = $block['heading_level'] ?? 'h2';
  $phone         = get_field('phone', 'option') ?: '01467 629675';
  $body          = $block['body']          ?? '54 West High Street, Inverurie, Aberdeenshire, AB51 3QR. Step-free access from the street, and the kettle is always on.';
  $image         = $block['image']         ?? null;
  $primary_cta   = $block['primary_cta']   ?? ['url' => 'tel:' . preg_replace('/\s+/', '', $phone), 'title' => 'Call ' . $phone, 'target' => ''];
  $secondary_cta = $block['secondary_cta'] ?? ['url' => '/contact-us', 'title' => 'Contact us', 'target' => ''];
@endphp

<section class="go-block not-prose bg-go-mint px-6 py-[72px]">
  <div class="max-w-[1240px] mx-auto grid gap-14 items-center" style="grid-template-columns: repeat(auto-fit, minmax(min(380px, 100%), 1fr))">

    {{-- Left: copy --}}
    <div>
      <{{ $heading_level }} class="font-heading font-bold text-go-ink tracking-[-0.6px] mb-[18px]" style="font-size: clamp(30px, 3vw, 40px)">
        {!! $heading !!}
      </{{ $heading_level }}>

      @if($body)
        <p class="font-body text-[20px] leading-[1.6] text-go-ink-soft mb-[26px]">{{ $body }}</p>
      @endif

      <div class="flex flex-wrap gap-3.5">
        @if($primary_cta)
          <a
            href="{{ $primary_cta['url'] }}"
            @if(!empty($primary_cta['target'])) target="{{ $primary_cta['target'] }}" rel="noopener" @endif
            class="inline-flex items-center px-7 py-[17px] rounded-full bg-go-green-deep text-white font-heading font-bold text-[19px] leading-none hover:bg-go-green-deepest transition-colors"
          >
            {{ $primary_cta['title'] }}
          </a>
        @endif
        @if($secondary_cta)
          <a
            href="{{ $secondary_cta['url'] }}"
            @if(!empty($secondary_cta['target'])) target="{{ $secondary_cta['target'] }}" rel="noopener" @endif
            class="inline-flex items-center px-7 py-[17px] rounded-full bg-white border-2 border-go-green-deep text-go-green-deep font-heading font-bold text-[19px] leading-none hover:bg-go-mint transition-colors"
          >
            {{ $secondary_cta['title'] }}
          </a>
        @endif
      </div>
    </div>

    {{-- Right: map or shopfront photo --}}
    <div class="min-w-0 w-full rounded-[22px] overflow-hidden border border-go-line">
      @if($image)
        <img
          src="{{ $image['url'] }}"
          alt="{{ $image['alt'] ?? '' }}"
          class="w-full object-cover"
          style="height: 300px;"
          loading="lazy"
        />
      @else
        <div class="w-full bg-white flex items-center justify-center" style="height: 300px;">
          <p class="font-body text-[17px] text-go-ink-soft text-center px-8">Map or shopfront photo to be supplied by client</p>
        </div>
      @endif
    </div>

  </div>
</section>
