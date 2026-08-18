@php
  $heading       = $block['heading']       ?? 'Funded and supported by';
  $heading_level = $block['heading_level'] ?? 'h2';
  $logos         = $block['logos']         ?? [];
  $link          = $block['link']          ?? ['url' => '/funders-and-suppliers', 'title' => 'View all our supporters', 'target' => ''];
@endphp

<section class="go-block not-prose bg-white px-6 py-[72px] border-t border-go-line">
  <div class="max-w-[1240px] mx-auto flex flex-col items-center gap-8 text-center">

    <{{ $heading_level }} class="font-heading font-bold text-[26px] text-go-ink tracking-[-0.4px]">
      {!! $heading !!}
    </{{ $heading_level }}>

    @if($logos)
      <div class="flex flex-wrap justify-center items-center gap-7">
        @foreach($logos as $logo)
          @php
            $img_url = is_array($logo) ? ($logo['url'] ?? '') : $logo;
            $alt     = is_array($logo) ? ($logo['alt'] ?? '') : '';
          @endphp
          @if($img_url)
            <img
              src="{{ $img_url }}"
              alt="{{ $alt }}"
              class="object-contain"
              style="height: 74px; width: auto;"
              loading="lazy"
            />
          @endif
        @endforeach
      </div>
    @else
      <p class="font-body text-[17px] text-go-ink-soft">Funder logos to be added in WordPress admin.</p>
    @endif

    @if($link)
      <a
        href="{{ $link['url'] }}"
        @if(!empty($link['target'])) target="{{ $link['target'] }}" rel="noopener" @endif
        class="font-heading font-bold text-[17px] text-go-green-deep hover:underline transition-colors"
      >
        {{ $link['title'] }} →
      </a>
    @endif

  </div>
</section>
