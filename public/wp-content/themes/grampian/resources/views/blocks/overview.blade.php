@php
  $bg_map = [
    'primary' => 'bg-og-green-200',
    'light'   => 'bg-gray-50',
    'default' => 'bg-white',
  ];
  $bg_class = $bg_map[$block['background_color'] ?? 'default'] ?? 'bg-white';
  $cards    = $block['cards'] ?? [];
  $cols_map = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-3', '4' => 'md:grid-cols-4'];
  $cols     = $cols_map[$block['columns'] ?? '3'] ?? 'md:grid-cols-3';
@endphp

<section class="py-12 {{ $bg_class }}">
  <div class="mx-auto max-w-screen-xl px-4">
    @if(!empty($block['title']))
      <h2 class="text-2xl font-semibold mb-2">{{ $block['title'] }}</h2>
    @endif
    @if(!empty($block['intro']))
      <p class="text-gray-600 mb-8">{!! nl2br(esc_html($block['intro'])) !!}</p>
    @endif
    @if($cards)
      <div class="grid grid-cols-1 {{ $cols }} gap-6">
        @foreach($cards as $card)
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            @if(!empty($card['image']))
              <img
                src="{{ $card['image']['url'] }}"
                alt="{{ $card['image']['alt'] }}"
                loading="lazy"
                class="w-full h-48 object-cover"
              />
            @endif
            <div class="p-5 flex flex-col flex-1">
              <h3 class="text-lg font-semibold mb-2">{{ $card['title'] }}</h3>
              @if(!empty($card['text']))
                <p class="text-gray-600 text-sm flex-1">{!! nl2br(esc_html($card['text'])) !!}</p>
              @endif
              @if(!empty($card['link']))
                <a href="{{ $card['link'] }}" class="mt-4 text-blue-700 text-sm font-medium hover:underline">
                  {{ $card['link_text'] ?: 'Read more' }} &rarr;
                </a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>
