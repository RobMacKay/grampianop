/**
 * Live "places left" check for the booking form.
 *
 * When an event or activity has limited places, they are counted per date. This
 * asks the server how many are left for the date the visitor picks, says so under
 * the date field, and stops them choosing more people than there are places.
 * It is only a courtesy — BookingForm::validate() enforces the limit again on submit.
 *
 * The form opts in by carrying `data-spaces-url` on its hidden go_booking_item input.
 */
const MAX_PER_BOOKING = 10;

export default function initBookingSpaces() {
  document
    .querySelectorAll('input[name="go_booking_item"][data-spaces-url]')
    .forEach(setup);
}

function setup(itemInput) {
  const form = itemInput.closest('form');
  const dateInput = form?.querySelector('input[name="acf[field_grampian_bk_date]"]');
  const people = form?.querySelector('input[name="acf[field_grampian_bk_attendees]"]');

  if (!form || !dateInput) {
    return;
  }

  const note = document.createElement('p');
  note.className = 'go-spaces';
  note.setAttribute('role', 'status');
  note.hidden = true;

  // Its own full-width line under the date / people row — inside the date field it
  // would make that field taller and push the neighbouring input out of line.
  (people ?? dateInput).closest('.acf-field').insertAdjacentElement('afterend', note);

  let last = '';
  let request = null;

  const show = (text, full = false) => {
    note.textContent = text;
    note.classList.toggle('is-full', full);
    note.hidden = text === '';
  };

  const limitPeople = (left) => {
    if (!people) {
      return;
    }

    const max = left === null ? MAX_PER_BOOKING : Math.max(1, Math.min(MAX_PER_BOOKING, left));

    people.max = String(max);

    if (Number(people.value) > max) {
      people.value = String(max);
    }
  };

  const check = async () => {
    const date = dateInput.value.replace(/\D/g, '');

    if (date === last) {
      return;
    }

    last = date;
    request?.abort();

    if (date.length !== 8) {
      show('');
      limitPeople(null);

      return;
    }

    request = new AbortController();

    try {
      const url = new URL(itemInput.dataset.spacesUrl, window.location.href);
      url.search = new URLSearchParams({ action: 'go_booking_spaces', item: itemInput.value, date });

      const response = await fetch(url, { signal: request.signal });
      const { success, data } = await response.json();

      if (!success || !data.limited || data.left === null) {
        show('');
        limitPeople(null);

        return;
      }

      const day = new Date(+date.slice(0, 4), +date.slice(4, 6) - 1, +date.slice(6, 8))
        .toLocaleDateString('en-GB', { day: 'numeric', month: 'long' });

      if (data.left === 0) {
        show(`Fully booked: there are no places left on ${day}. Please choose another date.`, true);
        limitPeople(1);
      } else {
        show(`${data.left} ${data.left === 1 ? 'place' : 'places'} left on ${day}.`);
        limitPeople(data.left);
      }
    } catch (error) {
      if (error.name !== 'AbortError') {
        // The check is a courtesy; the server still decides on submit.
        show('');
        limitPeople(null);
      }
    }
  };

  // ACF's date picker does not reliably fire `change` on its hidden Ymd input, so
  // re-read it after any interaction in the form and act only when it differs.
  ['change', 'input', 'focusout', 'click'].forEach((type) =>
    form.addEventListener(type, () => window.setTimeout(check, 50), true)
  );
}
