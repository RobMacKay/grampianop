@php
  $bg_map = [
    'primary' => 'bg-og-green-200',
    'light'   => 'bg-gray-50',
    'default' => 'bg-white',
  ];
  $bg_class = $bg_map[$block['background_color'] ?? 'light'] ?? 'bg-gray-50';

  $align_map = [
    'left'   => ['figure' => 'text-left',   'icon' => 'mr-auto', 'caption' => 'justify-start'],
    'right'  => ['figure' => 'text-right',  'icon' => 'ml-auto', 'caption' => 'justify-end'],
    'center' => ['figure' => 'text-center', 'icon' => 'mx-auto', 'caption' => 'justify-center'],
  ];
  $align = $align_map[$block['text_alignment'] ?? 'center'] ?? $align_map['center'];
@endphp

<section class="py-12 {{ $bg_class }}">
  <div class="mx-auto max-w-screen-xl px-4">
    <figure class="max-w-2xl mx-auto {{ $align['figure'] }}">
      <svg class="w-10 h-10 mb-4 text-gray-400 {{ $align['icon'] }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 14">
        <path d="M6 0H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3H2a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v1a3 3 0 0 1-3 3h-1a1 1 0 0 0 0 2h1a5.006 5.006 0 0 0 5-5V2a2 2 0 0 0-2-2Z"/>
      </svg>
      <blockquote class="text-xl italic font-medium text-gray-900">
        <p>"{!! nl2br(esc_html($block['quote'])) !!}"</p>
      </blockquote>
      @if(!empty($block['author']))
        <figcaption class="flex items-center mt-6 space-x-3 {{ $align['caption'] }}">
          <div class="flex items-center divide-x-2 divide-gray-300">
            <cite class="pe-3 font-medium text-gray-900">{{ $block['author'] }}</cite>
          </div>
        </figcaption>
      @endif
    </figure>
  </div>
</section>
