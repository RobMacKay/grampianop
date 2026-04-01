@php
  $phone        = get_field('phone', 'option') ?: '01467 629675';
  $email        = get_field('email', 'option') ?: 'info@grampianopportunities.org.uk';
  $donate_url   = get_field('donate_url', 'option') ?: 'https://checkout.justgiving.com/c/3088502';
  $get_support  = get_field('get_support_url', 'option') ?: '/online-referral';
  $volunteer    = get_field('volunteer_url', 'option') ?: '/volunteering';
  $banner_text  = get_field('banner_text', 'option') ?: 'Serving the community for over 25 years';
  $logo_wide    = get_field('logo_wide', 'option');
  $logo_compact = get_field('logo_compact', 'option');
@endphp

<header>
  {{-- Top bar: contact info + search --}}
  <div class="text-black font-bold w-full py-2">
    <div class="flex justify-between mx-auto max-w-screen-xl px-4 items-center gap-4">

      {{-- Contact links --}}
      <div class="flex items-center gap-2 lg:gap-4">
        <span class="flex gap-1 items-center">
          <a href="tel:{{ $phone }}" aria-label="Phone Number">
            <x-heroicon-s-phone class="h-6 w-6 text-red-700" />
          </a>
          <span class="hidden lg:inline text-black">{{ $phone }}</span>
        </span>
        <span class="flex gap-1 items-center">
          <a href="mailto:{{ $email }}" aria-label="Email Address">
            <x-heroicon-s-envelope class="h-6 w-6 text-blue-700" />
          </a>
          <span class="hidden lg:inline">
            <a href="mailto:{{ $email }}" class="text-black">{{ $email }}</a>
          </span>
        </span>
        <span>
          <a href="{{ $donate_url }}" aria-label="Donate" class="flex gap-1 items-center" target="_blank" rel="noopener">
            <x-heroicon-s-trophy class="h-6 w-6 text-amber-500 animate-bounce" />
            <span class="hidden lg:inline text-black">Donate</span>
          </a>
        </span>
      </div>

      {{-- Search form --}}
      <form action="{{ home_url('/') }}" method="GET" role="search" class="flex-shrink-0">
        <div class="flex border border-gray-200 rounded-md overflow-clip">
          <label for="site-search" class="sr-only">Search</label>
          <input
            id="site-search"
            type="search"
            name="s"
            placeholder="Search..."
            value="{{ get_search_query() }}"
            class="border-0 min-w-10 max-w-52 md:max-w-fit px-3 py-1.5 text-sm focus:outline-none"
          >
          <button type="submit" aria-label="Search">
            <x-heroicon-m-magnifying-glass class="h-6 block pr-2" />
          </button>
        </div>
      </form>

      {{-- Mobile menu toggle --}}
      <div class="flex md:hidden items-center">
        <button
          type="button"
          x-data
          @click="document.getElementById('navbar-dropdown').classList.toggle('hidden')"
          class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
          aria-controls="navbar-dropdown"
          aria-expanded="false"
        >
          <span class="sr-only">Open main menu</span>
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  {{-- Logo row --}}
  <div class="relative">
    <div class="py-4 px-4 mx-auto max-w-screen-xl text-center z-10 relative">
      <div class="flex justify-between items-center">
        <h1>
          <span class="sr-only">Grampian Opportunities - Empowering people to find their way forward</span>
          <a href="{{ home_url('/') }}">
            @if($logo_wide)
              <img src="{{ $logo_wide['url'] }}" alt="{{ $logo_wide['alt'] ?: 'Grampian Opportunities Logo' }}" class="h-14 sm:h-16 lg:h-24 w-auto" />
            @else
              <img src="{{ \Roots\asset('images/go_name2.webp') }}" alt="Grampian Opportunities Logo" class="h-14 sm:h-16 lg:h-24 w-auto" />
            @endif
          </a>
        </h1>
        <div class="flex flex-col gap-2">
          <a href="{{ $get_support }}" class="w-full block bg-blue-700 text-white font-semibold py-2 text-center px-4 sm:px-8 rounded-md transition ease-in-out delay-150 hover:-translate-y-1 hover:scale-105">Get Support</a>
          <a href="{{ $volunteer }}" class="w-full block bg-red-700 text-white font-semibold py-2 text-center px-4 sm:px-8 rounded-md transition ease-in-out delay-150 hover:-translate-y-1 hover:scale-105">Volunteer</a>
        </div>
      </div>
    </div>
    <div class="bg-gradient-to-b from-gray-100 w-full h-full absolute top-0 left-0 z-0"></div>
  </div>

  {{-- Primary navigation --}}
  <nav class="lg:bg-gradient-to-t lg:from-og-green-400 lg:to-og-green-200 border-gray-200" aria-label="Primary">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between md:justify-center mx-auto px-4 lg:p-4">
      <div class="hidden w-full md:block md:w-auto" id="navbar-dropdown">
        @php
          wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'container'      => false,
            'menu_class'     => 'flex flex-col font-medium p-4 mb-4 lg:mb-0 md:p-0 mt-4 border border-gray-100 rounded-lg md:gap-4 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0',
            'item_spacing'   => 'discard',
            'fallback_cb'    => false,
            'walker'         => new \App\Walker\NavWalker(),
          ]);
        @endphp
      </div>
    </div>
  </nav>

  {{-- Sticky banner strip --}}
  @if($banner_text)
    <div class="flex justify-center w-full p-3 border-b border-gray-200 bg-gray-50">
      <p class="flex items-center text-sm font-normal text-gray-800">
        <span class="inline-flex p-1 me-3 bg-gray-100 border border-gray-200 rounded-full w-6 h-6 items-center justify-center shrink-0">
          <x-heroicon-s-cake class="h-4 w-4 text-pink-400" aria-hidden="true" />
        </span>
        <strong>{{ $banner_text }}</strong>
        <span class="inline-flex p-1 ms-3 bg-gray-100 border border-gray-200 rounded-full w-6 h-6 items-center justify-center shrink-0">
          <x-heroicon-s-cake class="h-4 w-4 text-pink-400" aria-hidden="true" />
        </span>
      </p>
    </div>
  @endif
</header>
