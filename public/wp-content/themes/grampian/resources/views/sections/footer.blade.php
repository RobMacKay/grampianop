@php
  $phone          = get_field('phone', 'option') ?: '01467 629675';
  $email          = get_field('email', 'option') ?: 'info@grampianopportunities.org.uk';
  $address        = get_field('address', 'option') ?: '54 West High Street, Inverurie, Aberdeenshire, AB51 3QR';
  $charity_number = get_field('charity_number', 'option') ?: 'SC030396';
  $facebook_url   = get_field('facebook_url', 'option') ?: 'https://www.facebook.com/GOGrampianOpportunities/';
  $instagram_url  = get_field('instagram_url', 'option') ?: 'https://www.instagram.com/grampian54whs/';
  $logo_compact   = get_field('logo_compact', 'option');
@endphp

<footer class="bg-white" aria-label="Site footer">
  <div class="mx-auto w-full max-w-screen-xl p-4 py-6 lg:py-8">
    <div class="md:flex md:justify-between">

      {{-- Brand & address --}}
      <div class="mb-6 md:mb-0">
        <a href="{{ home_url('/') }}" class="flex items-center">
          @if($logo_compact)
            <img src="{{ $logo_compact['url'] }}" class="h-8 me-3" alt="{{ $logo_compact['alt'] ?: 'Grampian Opportunities Logo' }}" />
          @else
            <img src="{{ \Roots\asset('images/gologo.webp') }}" class="h-8 me-3" alt="Grampian Opportunities Logo" />
          @endif
          <span class="self-center text-2xl font-semibold whitespace-nowrap">Grampian Opportunities</span>
        </a>
        <address class="block py-4 prose not-italic text-sm text-gray-600">
          <strong>Grampian Opportunities {{ $charity_number }}</strong><br />
          {!! nl2br(esc_html($address)) !!}<br />
          Tel: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a><br />
          Email: <a href="mailto:{{ $email }}">{{ $email }}</a>
        </address>
      </div>

      {{-- Footer link columns --}}
      <div class="grid grid-cols-2 gap-8 sm:gap-6 sm:grid-cols-3">
        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase">Resources</h2>
          @php
            wp_nav_menu([
              'theme_location' => 'footer_navigation',
              'container'      => false,
              'menu_class'     => 'text-gray-500 font-medium space-y-4',
              'link_before'    => '',
              'link_after'     => '',
              'fallback_cb'    => function () {
                // Hardcoded fallback matching the original site
                echo '<ul class="text-gray-500 font-medium space-y-4">';
                $links = [
                  'positions'      => 'Jobs',
                  'events'         => 'Events',
                  'online-referral'=> 'Online Referral',
                  'contact'        => 'Contact Us',
                ];
                foreach ($links as $slug => $label) {
                  echo '<li><a href="' . home_url('/' . $slug) . '" class="hover:underline">' . $label . '</a></li>';
                }
                echo '</ul>';
              },
            ]);
          @endphp
        </div>

        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase">Follow us</h2>
          <ul class="text-gray-500 font-medium space-y-4">
            @if($facebook_url)
              <li><a href="{{ $facebook_url }}" class="hover:underline" target="_blank" rel="noopener">Facebook</a></li>
            @endif
            @if($instagram_url)
              <li><a href="{{ $instagram_url }}" class="hover:underline" target="_blank" rel="noopener">Instagram</a></li>
            @endif
          </ul>
        </div>

        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase">Legal</h2>
          <ul class="text-gray-500 font-medium space-y-4">
            <li><a href="{{ home_url('/privacy-policy') }}" class="hover:underline">Privacy Policy</a></li>
            <li><a href="{{ home_url('/terms-of-service') }}" class="hover:underline">Terms &amp; Conditions</a></li>
          </ul>
        </div>
      </div>
    </div>

    <hr class="my-6 border-gray-200 sm:mx-auto lg:my-8" />

    {{-- Bottom bar --}}
    <div class="sm:flex sm:items-center sm:justify-between">
      <span class="text-sm text-gray-500 sm:text-center">
        &copy; {{ date('Y') }} <a href="{{ home_url('/') }}" class="hover:underline">Grampian Opportunities</a>. All Rights Reserved.
      </span>
      <span class="text-sm text-gray-500 sm:text-center">
        Website by <a href="https://rscmedia.co.uk" class="hover:underline font-medium" target="_blank" rel="noopener">RSC Media</a>
      </span>
      <div class="flex mt-4 sm:justify-center sm:mt-0 gap-5">
        @if($facebook_url)
          <a href="{{ $facebook_url }}" class="text-gray-500 hover:text-gray-900" target="_blank" rel="noopener" aria-label="Facebook">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 8 19">
              <path fill-rule="evenodd" d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z" clip-rule="evenodd"/>
            </svg>
          </a>
        @endif
        @if($instagram_url)
          <a href="{{ $instagram_url }}" class="text-gray-500 hover:text-gray-900" target="_blank" rel="noopener" aria-label="Instagram">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069ZM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0Zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881Z"/>
            </svg>
          </a>
        @endif
      </div>
    </div>
  </div>
</footer>
