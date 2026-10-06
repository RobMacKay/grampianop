# Implementation plan: bookings, Turnstile, form tidy-up, CPT-driven blocks

Theme: `public/wp-content/themes/grampian` (Sage 11 / Acorn, Blade, Tailwind 4, ACF Pro).
All paths below are relative to that directory unless they start with `/`.

Read this whole document before starting. Phases are ordered so each one builds
on the last; do them in order and commit after each phase.

---

## 0. Ground rules and context

### How the existing forms work (copy this pattern — do not invent a new one)

- Submissions are **ACF front-end forms** (`acf_form()`), not hand-rolled `<form>`s.
  The field group *is* the form, so editors can change labels in ACF.
  - Referral: `resources/views/template-referral.blade.php` → CPT `referral`,
    field group `acf-json/group_grampian_referral_fields.json`.
  - Contact: `resources/views/blocks/contact-form.blade.php` (block `acf/contact-form`)
    → CPT `contact_submission`, field group `acf-json/group_grampian_contact_fields.json`.
- `acf_form_head()` must run before any output. It is called from the
  `template_redirect` hook in `app/setup.php` (~line 25), keyed off the page
  template or `has_block()`.
- Post-processing (title, emails) lives in `app/Forms/*Notifier.php`, hooked on
  `acf/save_post` at priority 20, guarded by a `_go_*_notified` meta flag so
  editing in wp-admin never re-sends. Registered in `app/setup.php` (`::init()`).
- CPTs are registered by ACF from `acf-json/post-type-*.json`. Submission CPTs
  are `public: false`, `supports: ["title"]`.
- `app/Editor.php` forces the classic editor for data-entry CPTs.
- Styling for all ACF forms: `resources/css/go-form.css` (unlayered, scoped to `.go-form`).
- JS: `resources/js/app.js` (Alpine). `referral-form.js` is a generic ACF-tab stepper;
  `form-ids.js` de-dupes ids when two forms share a page.
- Design tokens: `resources/css/app.css` `@theme` (`go-green-deep`, `go-mint`,
  `go-line`, `go-ink`, `go-ink-soft`, `go-red`, `font-heading`, `font-body`).
  New UI must use `go-*` tokens, not the legacy `og-*`/`gray-*` classes. Minimum body text is 17–18px.

### Data protection (important)

Referrals carry special category data (health/disability), so `ReferralNotifier`
puts **only a summary + wp-admin link** in staff email and repeats nothing in the
acknowledgement. **Bookings are the same:** the "requirements" box will hold
access/health/support needs. Follow the `ReferralNotifier` approach, not `ContactNotifier`.

### ACF JSON conventions

- Keep the existing key style: `field_grampian_<area>_<name>`, `group_grampian_<name>`,
  `post_type_grampian_<name>`.
- Bump `"modified"` to a current unix timestamp in every JSON file you touch, so ACF
  offers a sync in wp-admin.
- Validate every JSON file you write: `python3 -m json.tool <file> > /dev/null`.
- Note `acf-json/group_event_fields.json` is a **retired** duplicate group on the `event`
  post type. Do not edit it; edit `group_grampian_event_fields.json`.

### Verification available in this repo

There is no WordPress install or ACF Pro in the repo, so you cannot run the site. For every phase:

1. `composer install` (once), then `php -l` every PHP file you touched
   (`find app -name '*.php' -exec php -l {} \;`).
2. `npm ci` (once), then `npm run build` — must succeed (it compiles Blade-referenced Tailwind classes too).
3. `python3 -m json.tool` on every touched `acf-json/*.json`.
4. Blade: render-check is not possible without WP. Re-read each template for unbalanced
   `@if/@endif`, `@foreach/@endforeach`, `@php/@endphp`.
5. For CSS work (Phase 3), use the fixture + Playwright screenshot described there.

State plainly in the final summary what was and was not verified.

---

## Phase 1 — CPT-driven Activities and Events blocks (task 5)

Do this first: it is self-contained, and Phase 2 needs to know which items are bookable.

### 1.1 Activity post type tweaks

`acf-json/post_type_69cbc49bdb569.json` (the `activity` CPT):
- Add `"page-attributes"` to `supports` so editors can set **Order** (`menu_order`). All
  activity queries below sort by `menu_order` then `title`.

`acf-json/group_activity_fields.json` — add one field:
- `field_grampian_act_summary` / `summary` / textarea, 3 rows, max 200 chars, label
  "Short summary", instructions "One or two sentences shown on activity cards and grids."
  (`intro` exists but is used as the long intro on cards; if `summary` is empty, fall back
  to `intro`, then `wp_trim_words(get_the_content(), 25)`.)

### 1.2 Shared query helper

Create `app/Content.php` (namespace `App`, static class, like `EventRecurrence`):

```php
/** @return array<int, array{id:int,title:string,url:string,text:string,image:?string,image_alt:string}> */
public static function activityCards(string $mode = 'all', array $selected = [], int $limit = 0): array
```
- `mode === 'selected'`: use the given post IDs/objects in the given order (published only).
- `mode === 'all'`: `get_posts(['post_type'=>'activity','post_status'=>'publish','posts_per_page'=> $limit ?: -1,'orderby'=>['menu_order'=>'ASC','title'=>'ASC']])`.
- Image: ACF `icon` (array; prefer `sizes['medium']`), else featured image (`get_the_post_thumbnail_url($id,'medium')`), else `null`.
- Text: `summary` → `intro` → trimmed content (see 1.1).

```php
/** @return array<int, array> cards in EventRecurrence::nextCard() shape */
public static function upcomingEvents(int $limit = 3): array
```
- Move the two `WP_Query` calls + merge/sort/slice **verbatim** out of
  `resources/views/blocks/go-events-strip.blade.php` into this method. Keep the
  backwards-compat `recurring == '1'` meta clause. Use `'fields' => 'ids'` and
  `EventRecurrence::nextCard($id)` instead of `the_post()` loops.
- Use `wp_date('Y-m-d')` rather than `date()` so the site timezone is respected.

### 1.3 GO: Service Grid block → activities

This is the "display activities" block with hand-made cards. Today
`blocks/go-service-grid.blade.php` renders a `services` repeater, falling back to a
hardcoded `$default_services` PHP array.

`acf-json/group_block_go_service_grid.json`:
- Add `field_go_sg_source` / `source` / radio: `all` = "All published activities"
  (default), `selected` = "Choose activities".
- Add `field_go_sg_activities` / `activities` / relationship, `post_type: ["activity"]`,
  `return_format: "id"`, filters `["search"]`, conditional on `source == selected`.
- Add `field_go_sg_limit` / `limit` / number, min 0, default 0, instructions "0 = show all",
  conditional on `source == all`.
- **Remove** the `services` repeater (`field_go_sg_services` and its sub-fields).
  Existing saved repeater data is simply ignored after this — that is intended.

`acf-json/group_grampian_page_flex.json`, layout `go_service_grid`: add the same three
sub-fields (use new keys with a `_fc` suffix, e.g. `field_go_sg_fc_source`, since keys must be unique).
This layout already has no `services` repeater.

`resources/views/blocks/go-service-grid.blade.php`:
- Delete `$default_services` and the repeater normalisation `array_map`.
- `$services = \App\Content::activityCards($block['source'] ?? 'all', $block['activities'] ?? [], (int) ($block['limit'] ?? 0));`
- Map to the existing card markup: `url` = permalink, `link_label` = the activity's
  `cta_text` field or "Find out more", image = `image`/`image_alt`. Drop the `slug`
  image fallback branch.
- Empty state: if no activities, in the editor (`$is_preview`) show a short
  "No published activities yet — add some under Activities." note; on the front end
  render nothing for the grid (keep header + highlight panels).
- Leave highlight panels untouched (they are not activities).
- Note the flexible-content include does not pass `$is_preview`; use `$is_preview ?? false`.

### 1.4 Activities block

`blocks/activities.blade.php` + `acf-json/group_block_activities.json` + the
`activities` layout in **both** `group_grampian_page_flex.json` and `group_page_flexible_content.json`:
- Add the same `source` radio (default `all`) and make the existing `activities`
  relationship conditional on `source == selected`.
- **Back-compat:** if `source` is empty (blocks saved before this change) and the
  relationship has posts, treat it as `selected`. Otherwise `all`.
- Query via `Content::activityCards()` for the ID list, then keep rendering
  `components.activity-card` as now (it reads fields via `get_field()` on the global
  post, so keep the `setup_postdata()` / `wp_reset_postdata()` pattern).
- Make `archive-activity.blade.php` use `Content::activityCards('all')` too, so ordering is consistent.

### 1.5 GO: Events Strip block → events only

`blocks/go-events-strip.blade.php`:
- Replace the inline queries with `\App\Content::upcomingEvents((int) ($block['limit'] ?? 3))`.
- **Remove the hardcoded `$fallback_sessions`** and the `fallback_sessions` repeater
  (`field_go_es_fallback` + sub-fields) from `group_block_go_events_strip.json` and from the
  `go_events_strip` layout in `group_grampian_page_flex.json`.
- Add `field_go_es_empty` / `empty_message` / text, default
  "There are no upcoming events right now — check back soon." Render it in a single
  mint panel (same styling as an event card, no date chip) when there are no events.
- Leave the card markup and `EventRecurrence` as they are.

### 1.6 Legacy partial

`resources/views/partials/blocks/events-block.blade.php` queries `start_date >= now` and so
misses recurring events. Grep for includes (`grep -rn "events-block" resources app`). If
unused, delete it. If used, switch it to `Content::upcomingEvents(6)`.

**Commit:** "Drive activity and event blocks from their post types"

---

## Phase 2 — Booking system + capture (tasks 1 and 4)

### 2.1 Design summary

- New private CPT `booking`, captured exactly like referrals/contacts (ACF front-end form →
  pending post → notifier on `acf/save_post`).
- Bookings are switched on **per activity / per event** by an editor.
- The booking form appears automatically at the bottom of a bookable single event /
  activity page (anchor `#book`), and as a `Booking Form` block for any page.
- The booked item is **not** a user-editable field: it is passed as a hidden input,
  re-validated server-side, and written to an admin-only field.

### 2.2 Make events and activities bookable

Add a **Bookings** tab to `group_grampian_event_fields.json` (after the Info tab) and to
`group_activity_fields.json`:
- `bookings_enabled` — true_false, UI on, label "Take bookings on the website".
- `booking_notify_emails` — text, conditional on enabled, "Comma-separated. Leave blank to
  use the bookings address in Site Settings."
- `booking_intro` — textarea, conditional on enabled, optional copy shown above the form.

Keys: `field_grampian_ev_bookings_enabled`, `field_grampian_act_bookings_enabled`, etc.

Precedence with the existing event `booking_url` (external link): if `bookings_enabled` is on,
show the on-site form and hide the external button; otherwise behave as today.

Site Settings (`acf-json/group_site_options.json`, Contact tab): add
`field_grampian_opt_bookings_email` / `bookings_email` — text, "Where booking notifications
go (comma-separated). Falls back to the main email."

### 2.3 CPT + field groups

`acf-json/post-type-booking.json` — copy `post-type-referral.json`; change key to
`post_type_grampian_booking`, `post_type: "booking"`, labels "Bookings"/"Booking", menu icon
`dashicons-tickets-alt`, `menu_position: 27`, description "Activity and event bookings from the website".

`acf-json/group_grampian_booking_fields.json` — **the public form**, location
`post_type == booking`, `label_placement: top`, `menu_order: 0`. No tabs (it is short; one
page is better UX than a stepper). Use ACF **Message** fields as section headings
(they render inside the form and are styled in Phase 3).

| Key suffix | name | type | req | width | notes |
|---|---|---|---|---|---|
| `bk_msg_you` | — | message | | | Label "About you", empty message |
| `bk_first_name` | `first_name` | text | ✓ | 50 | autocomplete given-name (see 2.6) |
| `bk_last_name` | `last_name` | text | ✓ | 50 | |
| `bk_phone` | `phone` | text | ✓ | 50 | label "Phone number" |
| `bk_email` | `email` | email | | 50 | instructions "So we can confirm your booking." |
| `bk_date` | `booking_date` | date_picker | ✓ | 50 | "Which date would you like to come?" display `d/m/Y`, return `Y-m-d`. Removed for one-off events (2.5) |
| `bk_attendees` | `attendees` | number | ✓ | 50 | "How many people are coming, including you?" min 1, max 10, default 1 |
| `bk_msg_needs` | — | message | | | Label "Anything we should know?" |
| `bk_requirements` | `requirements` | textarea | | 100 | rows 4. Label "Support or access needs". Instructions: "For example: wheelchair access, dietary needs, communication support, or someone coming with you." |
| `bk_msg_support` | — | message | | | Label "Carer or social worker" |
| `bk_has_support` | `has_support_contact` | true_false | | 100 | message "Someone (a carer, social worker or support worker) helps with this booking" — UI off, so it renders as the styled checkbox card |
| `bk_support_name` | `support_name` | text | ✓ | 50 | conditional on `has_support_contact == 1` |
| `bk_support_role` | `support_role` | select | | 50 | Carer / Social worker / Support worker / Family member / Other; conditional |
| `bk_support_phone` | `support_phone` | text | ✓ | 50 | conditional |
| `bk_support_email` | `support_email` | email | | 50 | conditional |
| `bk_consent` | `consent_data` | true_false | ✓ | 100 | message "I agree to Grampian Opportunities storing these details to manage this booking." Copy the wording style from `field_grampian_ref_consent_data` |

`acf-json/group_grampian_booking_admin.json` — **admin only**, location `post_type == booking`,
`position: side`, `menu_order: 0`, title "Booking status". Never passed to `acf_form`.
- `booked_item` — post_object, `post_type: ["event","activity"]`, return id, label "Booked for".
- `booking_status` — select: `new` "New" (default), `confirmed` "Confirmed", `waitlist` "Waiting list", `cancelled` "Cancelled".
- `staff_notes` — textarea.

Add `'booking'` to the array in `app/Editor.php`.

### 2.4 Rendering the form

Create `resources/views/partials/booking-form.blade.php`, expecting `$item_id` (int),
`$form_id` (string), `$is_preview` (bool). Model it on `blocks/contact-form.blade.php`:
- Success state when `$_GET['booked'] === '1'`: mint panel, "Booking request sent", "We'll be in
  touch to confirm your place." + phone link (same markup as contact success).
- Otherwise heading "Book a place" (h2), subheading with the item title and, for events,
  `EventRecurrence::nextCard($item_id)['detail']`, then the item's `booking_intro`.
- Wrapper `<section id="book" class="go-block not-prose ...">` so `#book` deep links work.
- In the editor preview, show a placeholder (see contact block) — `acf_form()` cannot render there.
- Call:
  ```php
  \App\Forms\BookingForm::$currentItem = $item_id; // read by prepare_field filters
  acf_form([
      'id'                 => $form_id,
      'post_id'            => 'new_post',
      'new_post'           => ['post_type' => 'booking', 'post_status' => 'pending'],
      'field_groups'       => ['group_grampian_booking_fields'],
      'form_attributes'    => ['class' => 'acf-form go-form'],
      'html_before_fields' => sprintf('<input type="hidden" name="go_booking_item" value="%d">', $item_id),
      'submit_value'       => 'Send booking request',
      'honeypot'           => true,
      'updated_message'    => false,
      'return'             => add_query_arg('booked', '1', get_permalink()) . '#book',
  ]);
  ```

Include it:
- `resources/views/single-event.blade.php`: after the `<article>`, when
  `BookingForm::isBookable(get_the_ID())`. Hide the external "Book / More Info" button in that case.
- There is no `single-activity.blade.php`; activities use `single.blade.php`. Create
  `resources/views/single-activity.blade.php` extending `layouts.app`, rendering
  `partials.content-single` as `single.blade.php` does, then the booking partial when bookable.
  Check `single.blade.php` first and mirror it exactly.
- New block **Booking Form** (`booking-form`, view `blocks.booking-form`, icon `tickets-alt`) in
  `$contentBlocks` in `app/Blocks.php`. Field group `acf-json/group_block_booking_form.json`
  (location `block == acf/booking-form`): `booking_item` post_object (event|activity, return id,
  required). The block view resolves `$item_id`, and if the item is not bookable shows an
  editor-only warning ("Turn on bookings for this item first") and nothing on the front end.
  Form id: `'booking-form-' . substr(md5($block_id), 0, 8)`.

Register `acf_form_head()` for these pages. In `app/setup.php`'s `template_redirect` closure, add:
- `is_singular(['event', 'activity']) && BookingForm::isBookable(get_queried_object_id())`
- `has_block('acf/booking-form', $post)`

Refactor while you are there: move the "does this request render an ACF form?" logic into
`App\Forms\FormPages::current(): bool` so Turnstile (Phase 4) can reuse it. Keep the existing
comments; they explain real gotchas.

### 2.5 `App\Forms\BookingForm`

New class `app/Forms/BookingForm.php`, `init()` called from `app/setup.php`:
- `public static ?int $currentItem = null;`
- `isBookable(int $id): bool`: post exists, status `publish`, type in `['event','activity']`,
  `get_field('bookings_enabled', $id)` truthy. For one-off events, also require
  `EventRecurrence::nextCard($id) !== null` (do not take bookings for past events).
- `acf/prepare_field/key=field_grampian_bk_date` → return `false` (drops the field) when
  `$currentItem` is a one-off event (`event_type === 'one_off'`). Skip this filter in wp-admin
  (`is_admin() && ! wp_doing_ajax()`), so staff always see the field.
- ACF's date picker has no minimum-date setting, so past dates are rejected server-side (below).
- `acf/validate_save_post` (runs during ACF's AJAX validation **and** the real submit). Only act
  when `($_POST['_acf_screen'] ?? '') === 'acf_form'` and `$_POST['_acf_post_id'] === 'new_post'` and
  the posted field keys include `field_grampian_bk_first_name` (so it ignores contact/referral forms):
  - `go_booking_item` must pass `isBookable()`, else
    `acf_add_validation_error('', 'Sorry, bookings for this are closed. Please call us.')`.
  - If `booking_date` is posted, it must be today or later:
    `acf_add_validation_error('acf[field_grampian_bk_date]', 'Please choose a date that has not passed.')`.
  - Verify the exact `$_POST` key names against ACF's `acf_form_data()` in the installed plugin
    (`wp-content/plugins/advanced-custom-fields-pro/includes/...`) if you have access; otherwise
    log the assumption in the summary for the human to check.

### 2.6 `App\Forms\BookingNotifier`

New class modelled line-for-line on `ReferralNotifier` (`POST_TYPE = 'booking'`,
`NOTIFIED_META = '_go_booking_notified'`, `acf/save_post` priority 20, `init()` in setup.php).
`handle()` additionally does the following:
- On first save only (after the notified-meta guard, before `setTitle()`): read `(int) ($_POST['go_booking_item'] ?? 0)`, re-check
  `BookingForm::isBookable()`, then `update_field('field_grampian_bk_booked_item', $id, $post_id)` and
  `update_field('field_grampian_bk_status', 'new', $post_id)`.
- `setTitle()`: `"{first} {last} — {item title} ({d/m/Y of booking_date or 'next session'})"`.
- `notifyStaff()`: summary rows only — Booking for, Date, People, Name, "Support contact: yes/no",
  "Has support needs: yes/no" — plus the wp-admin link and the "details are not included" line.
  **Do not** put `requirements` or support-contact details in the email. Subject:
  `New booking: {item title}`.
- Recipients: item's `booking_notify_emails` → option `bookings_email` → option `email` →
  `admin_email`. Filter: `apply_filters('go_booking_recipients', $emails, $post_id, $item_id)`.
- `acknowledge()`: send to booker `email`; if empty, to `support_email`. Body: "Thanks — we've received
  your booking request for {item title}. We'll be in touch to confirm your place." + phone. Repeat nothing else.
- Extract the duplicated `headers()` from the three notifiers into
  `App\Forms\Mail::headers()` and use it in all three (small, safe refactor).

Autocomplete: ACF has no autocomplete setting in its UI. Skip it in this phase and mention it in the
summary as a possible follow-up. Do not patch the markup with JS for it.

### 2.7 Capture: admin list for staff (task 4)

New class `app/Forms/BookingAdmin.php` (`init()` from setup.php), admin-only hooks:
- `manage_booking_posts_columns` / `manage_booking_posts_custom_column`: columns
  **Booked for** (linked title), **Date** (`booking_date` d/m/Y or "—"), **People**, **Status**
  (label), keep **Date** (submitted).
- `restrict_manage_posts` (only on `booking` screen): a `<select name="go_booked_item">` of
  events + activities that have `bookings_enabled`, and a status select.
- `pre_get_posts` (admin main query, `post_type=booking`): apply `meta_query` for
  `booked_item` and `booking_status` from those selects (sanitise with `absint` / whitelist).
- Make **Date** sortable by `booking_date` meta (`manage_edit-booking_sortable_columns`).
- Optional, only if time allows and kept small: a "Download CSV" button on the list screen
  (`admin_post_go_export_bookings`, `current_user_can('edit_posts')`, nonce, same filters, includes
  requirements and support contact since staff are signed in). Mark it clearly in the commit if done.

Leave the contact and referral admin screens as they are.

**Commit:** "Add activity and event bookings"

---

## Phase 3 — Tidy form design (task 3)

### 3.1 Why the forms look cramped

ACF lays out fields with **floats and inline widths** (`style="width:50%"` + `data-width="50"`).
`go-form.css` sets `width: 100%` without `!important`, so the inline width wins; half-width fields
float flush against each other with **no horizontal gap**, and ACF's own `acf-input.css` adds
`border-top` and padding between fields. Fix the layout system once and every form improves.

### 3.2 `resources/css/go-form.css` changes

- Turn the field list into a wrapping flex row:
  ```css
  .go-form .acf-fields { display: flex; flex-wrap: wrap; gap: var(--go-field-gap) 24px; }
  .go-form .acf-fields > .acf-field {
    float: none; flex: 1 1 100%; width: auto !important; min-width: 0;
    margin: 0; padding: 0; border: 0;
  }
  .go-form .acf-fields > .acf-field[data-width] { flex-basis: calc(var(--w, 50%) - 12px); }
  .go-form .acf-fields > .acf-field[data-width="50"] { --w: 50%; }
  .go-form .acf-fields > .acf-field[data-width="33"] { --w: 33.333%; }
  @media (max-width: 640px) { .go-form .acf-fields > .acf-field[data-width] { flex-basis: 100%; } }
  ```
  Remove the now-redundant `margin-bottom` / `:last-child` rules. Kill ACF's separator:
  `.go-form .acf-fields > .acf-field + .acf-field { border-top: 0; }` (check ACF's selector names in
  its CSS if available; otherwise this is the known one).
- Hidden fields must stay hidden in flex: confirm `.acf-hidden` (ACF uses `display:none`) and the
  honeypot (inline `display:none`) still collapse. Tab fields (`.acf-field-tab`) must not take a slot:
  `.go-form .acf-fields > .acf-field-tab { display: none; }` is **not** allowed (breaks the stepper,
  see the existing comment) — give them `flex-basis: 0; position: absolute;` with the existing clip
  approach instead, or leave as is and verify in the fixture.
- `.go-form__controls`, `.acf-form-submit`: `flex-basis: 100%`.
- **Section headings** (message fields): `.go-form .acf-field-message` → `flex-basis: 100%`, top
  border `1px solid var(--color-go-line)`, `padding-top: 28px`, label styled as `font-heading`, 22px,
  bold, `go-ink`; the first one in the form gets no border/padding (`:first-child` won't work because
  of hidden inputs — use `.acf-field-message:first-of-type` or the `-r0` class ACF adds; verify).
- Spacing scale: field gap 26px → 24px row / 24px column; label→input 8px (keep); description under
  label 4px (keep); input padding 14px 16px; textarea min-height 140px.
- Focus: currently both a border colour change and a 3px outline offset 2px — keep (accessible), but
  set `outline-offset: 1px` so it doesn't collide with neighbours in a tight grid.
- Checkbox/true-false cards: keep, but `align-items: center` when the label is one line, and give the
  card `background: #fff` so it reads as tappable on mint/cream backgrounds.
- Error state: add `.go-form .acf-field.acf-error .acf-label label { color: var(--color-go-red); }` and
  make ACF's per-field error message (`.acf-field .acf-notice.-error`) sit **under the label**, not
  floating — set `margin: 0 0 8px; order` as needed.
- Submit button on the contact and booking forms: full width under 640px, auto above.

### 3.3 Card containers

The contact block (`blocks/contact-form.blade.php`) and booking partial use `p-[32px]`. Change to
`p-6 sm:p-8 md:p-10` so mobile gets breathing room on the screen edge rather than inside the card.
Apply the same to the referral template's inner `px-[32px]` blocks.

### 3.4 Legacy contact template

`resources/views/template-contact.blade.php` is a hand-written grey form posting to
`ContactFormHandler` (admin-post). It looks different from everything else and would bypass Turnstile.
Replace its body with the contact block view:
```blade
@include('blocks.contact-form', ['block' => ['heading' => get_the_title(), 'heading_level' => 'h1', 'intro' => get_the_content()], 'is_preview' => false, 'block_id' => 'contact-page'])
```
Add `is_page_template('template-contact.blade.php')` to `FormPages::current()`, then delete
`app/Forms/ContactFormHandler.php` and the two `admin_post_*submit_contact` hooks in `app/setup.php`
(both write the same `contact_submission` CPT, so nothing is lost). Note: the block's success flag is
`contact_sent`, not `submitted`.

### 3.5 Visual verification (do not skip)

Create a fixture page in a scratch folder **outside** the repo (your session scratchpad), e.g.
`<scratchpad>/form-fixture/index.html`, that:
- Links the built CSS from `public/build/assets/app-*.css` (after `npm run build`), plus a copy of
  ACF's front-end CSS if you can find it; if not, note that the screenshot excludes ACF's base CSS.
- Contains hand-written ACF markup for the booking form: `<form class="acf-form go-form"><div class="acf-fields -top">`
  with `.acf-field.acf-field-text[data-width="50"][style="width:50%"]`, `.acf-label > label`,
  `.acf-input > .acf-input-wrap > input`, a message field, a true_false, a textarea, and an
  `.acf-form-submit`. Copy real markup shapes from ACF's `render_field_wrap` output if available.
- Screenshot at 375px and 1280px with Playwright (Chromium is pre-installed at
  `/opt/pw-browsers/chromium`; do **not** run `playwright install`).
Check: 24px gaps between half-width fields, consistent rhythm, no double borders, sections clearly
separated, error state readable. Share the screenshots with the user.

**Commit:** "Tidy form layout and spacing"

---

## Phase 4 — Cloudflare Turnstile (task 2)

Goal: invisible to almost everyone, zero effect when not configured, one place in code, works for
every ACF front-end form (contact, referral, booking) including two forms on one page.

### 4.1 Configuration

- Keys from constants first (`GO_TURNSTILE_SITE_KEY`, `GO_TURNSTILE_SECRET_KEY` in `wp-config.php`),
  falling back to new Site Settings fields in a **Spam protection** tab of `group_site_options.json`:
  `turnstile_site_key` (text), `turnstile_secret_key` (password field type). Instructions on the tab
  say constants are preferred for the secret.
- If either key is missing, `Turnstile::enabled()` is false and **everything below is a no-op**.
  This keeps local dev and staging working without Cloudflare.

### 4.2 `App\Forms\Turnstile` (new, `init()` from setup.php)

- **Script:** on `wp_enqueue_scripts`, when `enabled() && FormPages::current()`, enqueue
  `https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit` with `strategy => 'defer'`
  (WP 6.3+ `wp_enqueue_script` args) and `null` version (Cloudflare forbids caching/versioning the script).
  Expose the site key with `wp_add_inline_script(..., 'before')` → `window.goTurnstile = {sitekey: "..."}`.
- **Widget placement:** filter `acf/validate_form` (ACF passes the `acf_form()` args through it; confirm
  the filter name in the plugin source, it is the standard one). When on the front end and enabled,
  prepend to `html_after_fields`:
  `<div class="go-turnstile" data-action="{form id}"></div>`.
  Prepend (not append) so on the referral form it sits above the Back/Next/Submit row.
- **Server check — the single-use-token trap:** ACF front-end forms validate twice: first by AJAX
  (`acf/validate_save_post` via admin-ajax), then again on the real POST. Turnstile tokens are
  **single use**, so siteverify must be called **only on the real POST**. Implement:
  - In `acf/validate_save_post`, when the request is from an `acf_form` and enabled:
    if `wp_doing_ajax()`: only check `cf-turnstile-response` is non-empty, else
    `acf_add_validation_error('', 'Please wait a moment for the security check to finish, then try again.')`.
    If not AJAX (the real submit): call `verify($token)`; on failure
    `acf_add_validation_error('', 'We could not confirm you are not a robot. Please try again.')`.
  - `verify()` = `wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['timeout' => 8, 'body' => ['secret'=>…, 'response'=>…, 'remoteip'=> $_SERVER['REMOTE_ADDR'] ?? '']])`,
    return `success === true`. **Fail open** on `is_wp_error()` (network failure) with `error_log()`,
    because a Cloudflare outage must not stop someone booking or referring; the honeypot is still there.
    Fail closed on an explicit `success: false`.
  - Check how ACF surfaces non-AJAX validation errors (it may `wp_die()`). If it does, prefer hooking
    the real-submit verification into `acf/pre_submit_form` (filter in ACF's form-front submit path) and
    redirect back with `?go_form_error=verify` + a friendly notice rendered by the form views. Confirm
    which hooks exist in the installed ACF version before choosing; document the choice.
- Don't break wp-admin saves: every hook must bail unless `$_POST['_acf_screen'] === 'acf_form'`.

### 4.3 `resources/js/turnstile.js` (imported from `app.js`)

- On `DOMContentLoaded`, if `window.goTurnstile` and `.go-turnstile` elements exist, wait for
  `window.turnstile` (poll every 100ms up to ~10s, like `referral-form.js` waits for `window.acf`)
  then `turnstile.render(el, { sitekey, action: el.dataset.action, appearance: 'interaction-only',
  theme: 'light', size: 'flexible', 'refresh-expired': 'auto', 'response-field-name': 'cf-turnstile-response' })`
  for **each** element (explicit rendering is what makes two forms on one page work).
- `appearance: 'interaction-only'` = invisible unless Cloudflare needs a click. That is the UX goal.
- Must run **after** `dedupeFormIds()` — or simply not depend on ids at all (use the element reference).
- For the referral stepper, keep the widget inside the form; no stepper changes needed.

### 4.4 Styling

`go-form.css`: `.go-form .go-turnstile { flex-basis: 100%; }` and `.go-turnstile:empty { display: none; }`
so an invisible widget doesn't leave a gap.

### 4.5 Verify

`php -l`, `npm run build`. Without keys, confirm by reading the code that no script is enqueued and no
markup is added. Write down in the summary that live verification needs real keys (Cloudflare provides
test keys: site `1x00000000000000000000AA` always passes, `2x00000000000000000000AB` always blocks;
secret `1x0000000000000000000000000000000AA` passes, `2x0000000000000000000000000000000AA` fails) and
list those for the human to try on staging.

**Commit:** "Add Cloudflare Turnstile to front-end forms"

---

## Final checklist

- [ ] `php -l` clean on all touched PHP; `npm run build` succeeds; all touched JSON parses.
- [ ] No `og-*` / `gray-*` classes in new markup.
- [ ] Booking email to staff contains no requirements / support-contact details.
- [ ] Every new hook bails out for wp-admin saves of unrelated posts.
- [ ] New ACF JSON files have unique keys (`grep -rho '"key": "[^"]*"' acf-json | sort | uniq -d` prints nothing).
- [ ] Form fixture screenshots (mobile + desktop) shared.
- [ ] Summary lists anything assumed about ACF internals (`_acf_screen`, `acf/validate_form`,
      `acf/pre_submit_form`, non-AJAX validation behaviour) that a human must confirm on a real site.
- [ ] Post-deploy steps for the human, written out: sync ACF field groups in wp-admin
      (ACF → Field Groups → Sync), re-save permalinks (new `single-activity` template doesn't need it but
      the `booking` CPT does no harm), set Turnstile keys, turn on bookings for an event, place the
      Booking Form block where needed, check SMTP is configured (existing notifiers already depend on it).

## Out of scope (do not build)

- Capacity limits / automatic waiting lists, payments, calendar sync, booker accounts.
- Changing referral or contact notification content.
- Editing the retired `group_event_fields.json`.
