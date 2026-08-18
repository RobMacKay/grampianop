@php
  $phone          = get_field('phone', 'option') ?: '01467 629675';
  $email          = get_field('email', 'option') ?: 'info@grampianopportunities.org.uk';
  $address        = get_field('address', 'option') ?: "54 West High Street\nInverurie\nAberdeenshire, AB51 3QR";
  $charity_number = get_field('charity_number', 'option') ?: 'SC030396';
  $facebook_url   = get_field('facebook_url', 'option') ?: 'https://www.facebook.com/GOGrampianOpportunities/';
  $instagram_url  = get_field('instagram_url', 'option') ?: 'https://www.instagram.com/grampian54whs/';
  $logo           = get_field('logo_wide', 'option');
  $phone_clean    = preg_replace('/\s+/', '', $phone);
  $year           = date('Y');
@endphp

<footer class="bg-go-ink" aria-label="Site footer">
  <div class="max-w-[1240px] mx-auto px-6 pt-[68px] pb-8">

    {{-- Main grid --}}
    <div class="grid gap-11" style="grid-template-columns: repeat(auto-fit, minmax(min(220px, 100%), 1fr))">

      {{-- Brand block --}}
      <div class="flex flex-col gap-5">
        <a href="{{ home_url('/') }}" class="inline-block bg-white rounded-[12px] px-[18px] py-[14px] self-start">
          @if($logo)
            <img
              src="{{ $logo['url'] }}"
              alt="{{ $logo['alt'] ?: 'Grampian Opportunities' }}"
              height="52"
              class="h-[52px] w-auto"
            />
          @else
            <img
              src="{{ \Roots\asset('images/go_name2.webp') }}"
              alt="Grampian Opportunities"
              height="52"
              class="h-[52px] w-auto"
            />
          @endif
        </a>
        <address class="not-italic font-body text-[18px] leading-[1.7] text-[#C9D2CD]">
          {!! nl2br(esc_html($address)) !!}<br>
          <a href="tel:{{ $phone_clean }}" class="text-go-green-light hover:underline">{{ $phone }}</a><br>
          <a href="mailto:{{ $email }}" class="text-go-green-light hover:underline">{{ $email }}</a>
        </address>
      </div>

      {{-- Resources column --}}
      <div>
        <h2 class="mb-4 font-heading font-bold text-[19px] text-white">Resources</h2>
        <ul class="space-y-3 font-body text-[17px] text-[#C9D2CD]">
          <li><a href="{{ home_url('/positions') }}" class="hover:text-go-green-light hover:underline transition-colors">Jobs</a></li>
          <li><a href="{{ home_url('/events') }}" class="hover:text-go-green-light hover:underline transition-colors">Events</a></li>
          <li><a href="{{ home_url('/online-referral') }}" class="hover:text-go-green-light hover:underline transition-colors">Online referral</a></li>
          <li><a href="{{ home_url('/contact-us') }}" class="hover:text-go-green-light hover:underline transition-colors">Contact us</a></li>
        </ul>
      </div>

      {{-- Follow us column --}}
      <div>
        <h2 class="mb-4 font-heading font-bold text-[19px] text-white">Follow us</h2>
        <ul class="space-y-3 font-body text-[17px] text-[#C9D2CD]">
          @if($facebook_url)
            <li>
              <a href="{{ $facebook_url }}" class="hover:text-go-green-light hover:underline transition-colors" target="_blank" rel="noopener">Facebook</a>
            </li>
          @endif
          @if($instagram_url)
            <li>
              <a href="{{ $instagram_url }}" class="hover:text-go-green-light hover:underline transition-colors" target="_blank" rel="noopener">Instagram</a>
            </li>
          @endif
        </ul>
      </div>

      {{-- Legal column --}}
      <div>
        <h2 class="mb-4 font-heading font-bold text-[19px] text-white">Legal</h2>
        <ul class="space-y-3 font-body text-[17px] text-[#C9D2CD]">
          <li><a href="{{ home_url('/privacy-policy') }}" class="hover:text-go-green-light hover:underline transition-colors">Privacy policy</a></li>
          <li><a href="{{ home_url('/terms-and-conditions') }}" class="hover:text-go-green-light hover:underline transition-colors">Terms &amp; conditions</a></li>
          <li><a href="{{ home_url('/accessibility') }}" class="hover:text-go-green-light hover:underline transition-colors">Accessibility</a></li>
        </ul>
      </div>

    </div>

    {{-- Copyright bar --}}
    <div class="mt-8 pt-6 border-t border-[#333B3D]">
      <p class="font-body text-[16px] text-[#C9D2CD]">
        &copy; {{ $year }} Grampian Opportunities. All rights reserved.
        Registered charity {{ $charity_number }}.
      </p>
    </div>

  </div>
</footer>
