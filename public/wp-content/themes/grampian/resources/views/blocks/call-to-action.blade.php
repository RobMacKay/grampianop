@php
  $bg_map = [
    'primary' => 'bg-og-green-200',
    'light'   => 'bg-gray-50',
    'default' => 'bg-white',
  ];
  $bg_class = $bg_map[$block['background_color'] ?? 'default'] ?? 'bg-white';
  $buttons  = $block['buttons'] ?? [];

  $btn_classes = [
    'primary'   => 'btn btn--primary',
    'secondary' => 'btn btn--secondary',
    'ghost'     => 'inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors',
    'link'      => 'inline-flex items-center text-sm font-medium text-blue-700 hover:underline',
  ];
@endphp

<section class="py-12 {{ $bg_class }}">
  <div class="mx-auto max-w-screen-xl px-4 text-center">
    @if(!empty($block['title']))
      <h2 class="text-2xl font-semibold mb-4">{{ $block['title'] }}</h2>
    @endif
    @if(!empty($block['text']))
      <p class="text-gray-600 mb-8 max-w-xl mx-auto">{!! nl2br(esc_html($block['text'])) !!}</p>
    @endif
    @if($buttons)
      <div class="flex flex-wrap justify-center items-center gap-4">
        @foreach($buttons as $button)
          @php $cls = $btn_classes[$button['style'] ?? 'primary'] ?? $btn_classes['primary']; @endphp
          <a
            href="{{ $button['url'] }}"
            class="{{ $cls }}"
            @if(!empty($button['new_tab'])) target="_blank" rel="noopener" @endif
          >{{ $button['label'] }}</a>
        @endforeach
      </div>
    @endif
  </div>
</section>
