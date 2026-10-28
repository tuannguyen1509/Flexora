# AZ Analytics

## Scope

Tracks whole-site page views and explicitly marked clicks for the AZ Theme Dashboard.

## Tracking

- Page views are counted on frontend template requests.
- Common bots, admin, AJAX, REST and cron requests are ignored.
- Clicks use elements marked with data-az-track.
- Raw IP addresses are never stored.

## Storage

Data is stored in the non-autoloaded az_site_analytics option. The module preserves existing theme options and does not alter legacy keys.

## Privacy and safety

Click requests require the az_analytics nonce. Event names are sanitized and bounded. The feature is loaded by the theme bootstrap and has no visible Tester or QA UI.
