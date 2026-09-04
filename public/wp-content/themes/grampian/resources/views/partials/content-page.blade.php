<article @php(post_class('go-page-content prose max-w-none'))>
  @php(the_content())

  @if ($pagination)
    <nav class="max-w-[1240px] mx-auto px-6 pb-12 flex justify-center gap-2" aria-label="{{ __('Page', 'sage') }}">
      {!! $pagination !!}
    </nav>
  @endif
</article>
