{{--
  Template Name: Contact
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post() @endphp

    <div class="min-h-screen bg-gray-50 py-16 px-4">
      <div class="mx-auto max-w-2xl">

        {{-- Page heading --}}
        <div class="mb-10 text-center">
          <h1 class="text-3xl font-bold text-gray-900">{{ get_the_title() }}</h1>
          @php $intro = get_the_content() @endphp
          @if($intro)
            <div class="mt-3 text-gray-600 prose prose-sm mx-auto">{!! $intro !!}</div>
          @endif
        </div>

        @if(isset($_GET['submitted']) && $_GET['submitted'] === '1')

          {{-- Success --}}
          <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-10 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-og-green-100">
              <x-heroicon-o-check-circle class="h-9 w-9 text-og-green-600" />
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Message sent!</h2>
            <p class="text-gray-600 mb-6">Thanks for getting in touch. We'll get back to you as soon as possible.</p>
            <a href="{{ home_url() }}" class="inline-flex items-center gap-2 bg-og-green-400 hover:bg-og-green-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors text-sm">
              <x-heroicon-m-arrow-left class="w-4 h-4" />
              Back to home
            </a>
          </div>

        @else

          @if(isset($_GET['form_error']) && $_GET['form_error'] === '1')
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
              <x-heroicon-m-exclamation-circle class="w-4 h-4 flex-shrink-0" />
              Please check all required fields are filled in correctly and try again.
            </div>
          @endif

          <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-8 md:p-10">
            <form method="POST" action="{{ esc_url(admin_url('admin-post.php')) }}" novalidate>
              <input type="hidden" name="action" value="submit_contact">
              <input type="hidden" name="return_url" value="{{ esc_url(get_permalink()) }}">
              @php wp_nonce_field('submit_contact_nonce', 'contact_nonce') @endphp

              <div class="space-y-5">

                {{-- Name --}}
                <div>
                  <label for="contact-name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="contact-name"
                    type="text"
                    name="name"
                    required
                    autocomplete="name"
                    value="{{ esc_attr($_POST['name'] ?? '') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-og-green-400"
                  >
                </div>

                {{-- Email + Phone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label for="contact-email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                      Email <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="contact-email"
                      type="email"
                      name="email"
                      required
                      autocomplete="email"
                      value="{{ esc_attr($_POST['email'] ?? '') }}"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-og-green-400"
                    >
                  </div>
                  <div>
                    <label for="contact-phone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                      Phone <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input
                      id="contact-phone"
                      type="tel"
                      name="phone"
                      autocomplete="tel"
                      value="{{ esc_attr($_POST['phone'] ?? '') }}"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-og-green-400"
                    >
                  </div>
                </div>

                {{-- Subject --}}
                <div>
                  <label for="contact-subject" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Subject <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="contact-subject"
                    type="text"
                    name="subject"
                    required
                    value="{{ esc_attr($_POST['subject'] ?? '') }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-og-green-400"
                  >
                </div>

                {{-- Message --}}
                <div>
                  <label for="contact-message" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Message <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    id="contact-message"
                    name="message"
                    required
                    rows="6"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-og-green-400 resize-y"
                  >{{ esc_textarea($_POST['message'] ?? '') }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                  <button
                    type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-og-green-400 hover:bg-og-green-600 text-white font-semibold px-8 py-3 rounded-lg transition-colors text-sm"
                  >
                    <x-heroicon-m-paper-airplane class="w-4 h-4" />
                    Send Message
                  </button>
                </div>

              </div>
            </form>
          </div>

        @endif

      </div>
    </div>

  @endwhile
@endsection
