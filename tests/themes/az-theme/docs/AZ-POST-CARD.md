# AZ Post Card

The reusable blog card uses the following structure:

```html
<article class="az-post-card">
  <a class="az-post-card__media">...</a>
  <div class="az-post-card__content">
    <div class="az-post-card__meta">...</div>
    <h2 class="az-post-card__title"><a>...</a></h2>
    <p class="az-post-card__excerpt">...</p>
    <a class="az-post-card__link">...</a>
  </div>
</article>
```

## Shared Rules

- Use the `az-post-card` block and `__element` names consistently.
- Keep metadata inside `az-post-card__meta` and use `az-post-card__meta-icon` for inline icons.
- Titles and excerpts are limited to two lines by the shared card stylesheet.
- Do not add body-scoped selectors or legacy `az-` classes.
- Keep card content independent from WooCommerce markup and plugin body classes.

Blog sidebar placement is configured independently with `blog.archive_sidebar` for archive/category pages and `blog.single_sidebar` for single posts. The legacy `blog.sidebar` value remains a fallback for existing installations.

The parent archive omits the duplicate title block below the breadcrumb by default, including sites with previously saved banner styling. It keeps a visually hidden, accessible H1 in the main content and retains visible category descriptions. `blog.archive_show_title` explicitly restores the title block; `blog.archive_hero_layout` then selects `plain` or `banner` styling. An active shared banner owns the H1; the homepage uses its existing Branding H1. The posts index uses the configured posts page title, falling back to `Blog`. These rules live in the parent theme and are inherited by child themes using its archive/home templates.
