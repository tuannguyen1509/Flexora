# Analytics Shortcode

Use `[az_stats]` to render a reusable site statistics bar.

Supported values are `online`, `today`, `30_days`, and `total`:

`[az_stats show="online,today,30_days,total"]`

The online value is a lightweight display count and defaults to `5`. Developers can customize it with the `az_analytics_online_count` filter. Page views and total views come from the AZ analytics option; no visitor IP addresses are stored.
