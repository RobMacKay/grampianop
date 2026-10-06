/**
 * Renders a Cloudflare Turnstile widget into every `.go-turnstile` placeholder
 * that app/Forms/Turnstile.php adds to an acf_form().
 *
 * Explicit rendering (rather than Cloudflare's auto-render) is what lets two
 * forms share a page, and `interaction-only` keeps the widget invisible unless
 * Cloudflare needs the visitor to do something. The widget adds its token to the
 * form as `cf-turnstile-response`, which ACF's AJAX validation serialises along
 * with every other field.
 */
export default function initTurnstile() {
  const config = window.goTurnstile;
  const placeholders = Array.from(document.querySelectorAll('.go-turnstile'));

  if (!config || placeholders.length === 0) {
    return;
  }

  // The Cloudflare script is deferred; wait for it the way referral-form.js waits for ACF.
  let waited = 0;

  const start = () => {
    if (!window.turnstile) {
      waited += 100;

      if (waited < 10000) {
        window.setTimeout(start, 100);
      }

      return;
    }

    placeholders.forEach(render);
  };

  const render = (el) => {
    window.turnstile.render(el, {
      sitekey: config.sitekey,
      action: el.dataset.action,
      appearance: 'interaction-only',
      size: 'flexible',
      theme: 'light',
      'refresh-expired': 'auto',
      // Only take up room while Cloudflare is asking the visitor to do something.
      'before-interactive-callback': () => el.classList.add('is-interactive'),
      'after-interactive-callback': () => el.classList.remove('is-interactive'),
    });
  };

  start();
}
