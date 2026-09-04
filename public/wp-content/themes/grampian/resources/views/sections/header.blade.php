@php
  $phone       = get_field('phone', 'option') ?: '01467 629675';
  $email       = get_field('email', 'option') ?: 'info@grampianopportunities.org.uk';
  $donate_url  = get_field('donate_url', 'option') ?: 'https://checkout.justgiving.com/c/3088502';
  $get_support = get_field('get_support_url', 'option') ?: '/online-referral';
  $referral    = get_field('referral_url', 'option') ?: '/online-referral';
  $logo        = get_field('logo_wide', 'option');
  $phone_clean = preg_replace('/\s+/', '', $phone);

  $nav_groups = [
    'Who we are' => [
      ['About us',    '/about-us'],
      ['Governance',  '/governance'],
      ['Our team',    '/our-team'],
      ['What we do',  '/what-we-do'],
      ['Contact us',  '/contact-us'],
    ],
    'Support & activities' => [
      ['Day Services',                    '/go-learners-club'],
      ['Our Journey Our Way',             '/therapeutic-activities'],
      ['Free to Be Me',                   '/befriending'],
      ['Community Art Group',             '/go-create-art-group'],
      ['Go to Work',                      '/go-work'],
      ['Go Social Club',                  '/go-social-activities-and-events'],
      ['Information and Guidance',        '/information-and-guidance'],
      ['Financial First Aid & IT Buddy',  '/financial-first-aid-it-buddy-support'],
    ],
    'Resources' => [
      ['Useful information',    '/useful-information'],
      ['Resources',             '/resources'],
      ['Support and wellbeing', '/support-and-wellbeing'],
      ['Carers & guardians',    '/sird-carers-guardians'],
    ],
    'Get involved' => [
      ['Volunteering',         '/volunteering'],
      ['Work with us',         '/work-with-us'],
      ['Donate',               $donate_url],
      ['Funders and suppliers','/funders-and-suppliers'],
    ],
    '_standalone' => [
      ["What's on", '/events'],
      ['Room hire',  '/room-hire'],
    ],
  ];
@endphp

<header
  x-data="{
    mobileOpen: false,
    resetOnWide() {
      if (window.innerWidth >= 1240) this.mobileOpen = false;
    }
  }"
  @keydown.escape.window="mobileOpen = false"
  @resize.window.debounce="resetOnWide()"
  class="sticky top-0 z-40"
>
  {{-- Utility bar --}}
  <div class="bg-go-ink text-white py-[10px] px-6">
    <div class="max-w-[1240px] mx-auto flex justify-end items-center gap-6 flex-wrap text-[16px] leading-none">
      <a href="tel:{{ $phone_clean }}" class="flex items-center gap-1.5 hover:text-go-green-light transition-colors">
        <span aria-hidden="true">✆</span>
        <span>{{ $phone }}</span>
      </a>
      <a href="mailto:{{ $email }}" class="hover:text-go-green-light transition-colors">{{ $email }}</a>
      <div class="w-px h-4 bg-[#3D4548]" aria-hidden="true"></div>
      <a href="{{ $referral }}" class="font-bold border-b-2 border-go-blue-line pb-0.5 hover:text-go-blue-line transition-colors">Make a referral</a>
    </div>
  </div>

  {{-- Header row --}}
  <div class="bg-white border-b border-go-line">
    <div class="max-w-[1240px] mx-auto px-6 py-[14px] flex items-center gap-5 flex-wrap">

      {{-- Logo --}}
      <a href="{{ home_url('/') }}" class="shrink-0">
        @if($logo)
          <img
            src="{{ $logo['url'] }}"
            alt="{{ $logo['alt'] ?: 'Grampian Opportunities — empowering people to find their way forward' }}"
            height="56"
            width="{{ round(($logo['width'] / $logo['height']) * 56) }}"
            class="h-14 w-auto"
          />
        @else
          <img
            src="{{ \Roots\asset('images/go_name2.webp') }}"
            alt="Grampian Opportunities — empowering people to find their way forward"
            height="56"
            class="h-14 w-auto"
          />
        @endif
      </a>

      {{-- Desktop nav (≥1240px). ml-auto + wrapping keeps long menu labels from
           pushing the CTA group onto a second row. --}}
      {{-- flex-1 basis-0 so the nav never forces the CTA group onto a second row:
           it takes whatever width is left and wraps its own items instead. --}}
      <nav class="hidden [@media(min-width:1240px)]:flex flex-1 basis-0 min-w-0 items-center gap-0.5 flex-wrap justify-end" aria-label="Primary">
        @php
          wp_nav_menu([
            'theme_location' => 'primary_navigation',
            'container'      => false,
            'menu_class'     => 'flex flex-wrap items-center justify-end gap-x-0.5 gap-y-1 list-none min-w-0',
            'item_spacing'   => 'discard',
            'fallback_cb'    => false,
            'walker'         => new \App\Walker\NavWalker(),
          ]);
        @endphp
      </nav>

      {{-- CTAs + menu toggle. Per the handoff the two CTAs are always visible,
           at every width — only the Menu button is breakpoint-dependent. --}}
      <div class="flex items-center gap-2.5 shrink-0 ml-auto">
        <a href="{{ $get_support }}"
           class="inline-flex items-center px-5 py-[13px] rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] leading-none whitespace-nowrap hover:bg-go-green-deepest transition-colors">
          Get support
        </a>
        <a href="{{ $donate_url }}"
           class="inline-flex items-center px-5 py-[13px] rounded-full bg-go-red border-2 border-go-red text-white font-heading font-bold text-[17px] leading-none whitespace-nowrap hover:bg-go-red-hover hover:border-go-red-hover transition-colors"
           target="_blank" rel="noopener">
          Donate
        </a>

        {{-- Mobile menu toggle (<1240px) --}}
        <button
          type="button"
          @click="mobileOpen = !mobileOpen"
          :aria-expanded="mobileOpen.toString()"
          aria-controls="go-mobile-menu"
          class="[@media(min-width:1240px)]:hidden flex items-center gap-2.5 px-[18px] py-3 rounded-full bg-go-mint border-2 border-go-green-deep font-heading font-bold text-[17px] leading-none text-go-green-deep whitespace-nowrap transition-colors hover:bg-go-green-pale"
        >
          <span class="grid gap-1 w-5" aria-hidden="true">
            <span class="block h-[3px] bg-go-green-deep rounded-[2px]"></span>
            <span class="block h-[3px] bg-go-green-deep rounded-[2px]"></span>
            <span class="block h-[3px] bg-go-green-deep rounded-[2px]"></span>
          </span>
          <span>Menu</span>
        </button>
      </div>

    </div>
  </div>

  {{-- Mobile drawer --}}
  <div
    id="go-mobile-menu"
    x-show="mobileOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    @click.outside="mobileOpen = false"
    class="[@media(min-width:1240px)]:hidden bg-white border-b border-go-line max-h-[70vh] overflow-y-auto"
    role="dialog"
    aria-label="Mobile navigation"
  >
    <div class="max-w-[1240px] mx-auto px-6 py-6">
      <div class="grid gap-x-8 gap-y-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr))">

        @foreach($nav_groups as $group => $links)
          @if($group === '_standalone')
            <div>
              <p class="mb-3 text-[13px] font-heading font-bold uppercase tracking-[1.3px] text-go-green-deep">More</p>
              <ul class="space-y-1">
                @foreach($links as [$label, $href])
                  <li>
                    <a href="{{ $href }}" class="block px-3 py-2 rounded-lg font-body text-[17px] text-go-ink hover:bg-go-mint hover:text-go-green-deep transition-colors" @click="mobileOpen = false">
                      {{ $label }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          @else
            <div>
              <p class="mb-3 text-[13px] font-heading font-bold uppercase tracking-[1.3px] text-go-green-deep">{{ $group }}</p>
              <ul class="space-y-1">
                @foreach($links as [$label, $href])
                  <li>
                    <a href="{{ $href }}" class="block px-3 py-2 rounded-lg font-body text-[17px] text-go-ink hover:bg-go-mint hover:text-go-green-deep transition-colors" @click="mobileOpen = false">
                      {{ $label }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        @endforeach

        {{-- Mobile CTAs --}}
        <div class="flex flex-col gap-3 justify-end">
          <a href="{{ $get_support }}"
             class="flex items-center justify-center px-5 py-3 rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] hover:bg-go-green-deepest transition-colors">
            Get support
          </a>
          <a href="{{ $donate_url }}"
             class="flex items-center justify-center px-5 py-3 rounded-full bg-go-red text-white font-heading font-bold text-[17px] hover:bg-go-red-hover transition-colors"
             target="_blank" rel="noopener">
            Donate
          </a>
        </div>

      </div>
    </div>
  </div>

</header>
