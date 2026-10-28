<?php
/**
 * Language Switcher - Client-side VN/EN toggle
 * Uses localStorage + data attributes to switch without server reload
 * This bypasses LiteSpeed Cache and any server-side caching
 */
?>

<div class="az-lang-switcher" id="az-lang-switcher">
    <button class="az-lang-btn" data-lang="vi" title="Tiếng Việt" aria-label="Switch to Vietnamese">
        <img src="https://flagcdn.com/w20/vn.png" width="20" height="14" alt="VN" loading="lazy">
        <span>VN</span>
    </button>
    <span class="az-lang-divider">|</span>
    <button class="az-lang-btn" data-lang="en" title="English" aria-label="Switch to English">
        <img src="https://flagcdn.com/w20/gb.png" width="20" height="14" alt="EN" loading="lazy">
        <span>EN</span>
    </button>
</div>

<style>
.az-lang-switcher { display: flex; align-items: center; gap: 4px; }
.az-lang-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 4px 8px; border-radius: 6px;
    border: 1px solid transparent; background: transparent;
    color: #9ca3af; font-size: 13px; font-weight: 500;
    cursor: pointer; transition: all 0.2s ease; line-height: 1;
}
.az-lang-btn:hover { color: #fff; border-color: rgba(255,255,255,0.2); background: rgba(255,255,255,0.08); }
.az-lang-btn.active { color: #00d4ff; border-color: rgba(0,212,255,0.4); background: rgba(0,212,255,0.1); }
.az-lang-btn img { border-radius: 2px; display: block; }
.az-lang-divider { color: #374151; font-size: 12px; user-select: none; }
</style>
