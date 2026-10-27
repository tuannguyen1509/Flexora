<?php
/** Contract metadata only. No production AZ declarations or data adapter yet. */
return array(
	'id' => 'az-v1',
	'version' => '1.0.0',
	'profile' => 'az-bridge',
	'physical_parent_slug' => 'az-theme',
	'child_template' => 'az-theme',
	'early_paths' => array( 'inc/floating-contact.php' ),
	'patterns' => array( 'child-first', 'early-require-remove', 'pluggable', 'stable-callback-priority' ),
	'protected_storage' => array( 'az_theme_options', '_az_home_page_data', '_az_shared_banner', 'theme_mods_az-theme' ),
	'production_compatibility' => false,
);
