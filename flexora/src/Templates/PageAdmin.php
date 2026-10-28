<?php
namespace FLX\Templates;

/** Classic native meta-box integration scoped to mapped Pages. */
final class PageAdmin {
	private $registry;
	private $resolver;
	private $engine;
	private $storage;
	public function __construct( $registry, $resolver, $engine, $storage ) { $this->registry = $registry; $this->resolver = $resolver; $this->engine = $engine; $this->storage = $storage; }
	public function attach() {
		add_action( 'add_meta_boxes_page', array( $this, 'boxes' ) );
		add_action( 'save_post_page', array( $this, 'on_save' ), 20, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ), 20 );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_filter( 'use_block_editor_for_post', array( $this, 'editor_mode' ), 10, 2 );
	}
	public function editor_mode( $use, $post ) {
		// Full wp_editor lifecycle is supported in classic edit context, never a global editor replacement.
		return 'page' === $post->post_type && ( $this->resolver->map( $post->ID ) || isset( $_GET['flx_template_editor'] ) ) ? false : $use; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	public function boxes( $post ) { if ( $this->resolver->map( $post->ID ) ) { add_meta_box( 'flx-template-content', 'Flexora Template Content', array( $this, 'render' ), 'page', 'normal', 'high' ); } }
	public function assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		global $post;
		$map = $post && 'page' === $post->post_type ? $this->resolver->map( $post->ID ) : null;
		if ( $map ) { \FLX\Fields\AdminAssets::enqueue( $map['fields'] ); }
	}
	private function notice_key( $post_id ) { return 'flx_map_notice_' . get_current_user_id() . '_' . $post_id; }
	public function notices() {
		global $post;
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || ! $post || 'page' !== $post->post_type || $this->resolver->map( $post->ID ) ) { return; }
		$this->resolver->payload( $post->ID );
		foreach ( $this->resolver->diagnostics() as $entry ) { if ( $post->ID === $entry['post_id'] ) { echo '<div class="notice notice-warning"><p>' . esc_html( $entry['code'] ) . ': ' . esc_html( $entry['map'] ) . '. Dữ liệu được giữ nguyên.</p></div>'; } }
	}
	public function on_save( $post_id ) {
		$result = $this->save( $post_id, wp_unslash( $_POST ) );
		if ( isset( $result['errors'] ) && $result['errors'] ) { set_transient( $this->notice_key( $post_id ), $result, 120 ); }
	}
	public function save( $post_id, array $request, $autosave = false ) {
		$skip = array( 'skipped' => true );
		if ( $autosave || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return $skip; }
		if ( ! array_key_exists( 'flx_map_nonce', $request ) ) { return $skip; }
		$error = function ( $message ) { return array( 'errors' => array( '_form' => $message ) ); };
		if ( 'page' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return $error( 'Không có quyền chỉnh nội dung Page này.' ); }
		if ( ! is_string( $request['flx_map_nonce'] ) || ! wp_verify_nonce( $request['flx_map_nonce'], 'flx-map-' . $post_id ) ) { return $error( 'Phiên chỉnh sửa không hợp lệ. Tải lại trang.' ); }
		$map = $this->resolver->map( $post_id );
		if ( ! $map || ! isset( $request['flx_map_id'] ) || $map['id'] !== $request['flx_map_id'] ) {
			$this->resolver->diagnostic( 'template_map_changed', $post_id ); return $skip;
		}
		if ( ! isset( $request['flx_map_complete'], $request['flx_map_revision'], $request['flx_map_version'] ) || '1' !== $request['flx_map_complete'] || ! is_string( $request['flx_map_revision'] ) || ! ctype_digit( $request['flx_map_revision'] ) || (string) $map['version'] !== $request['flx_map_version'] ) { return $error( 'Schema đã thay đổi hoặc yêu cầu không đầy đủ. Không lưu dữ liệu.' ); }
		$input = isset( $request['flx_map_values'] ) ? $request['flx_map_values'] : array();
		if ( ! is_array( $input ) ) { return $error( 'Dữ liệu không hợp lệ.' ); }
		$root = $this->storage->read( $post_id ); $old = isset( $root['maps'][ $map['id'] ]['data'] ) ? $root['maps'][ $map['id'] ]['data'] : array();
		if ( ! is_array( $old ) ) { return $error( 'Dữ liệu lưu không đúng định dạng. Không ghi đè.' ); }
		$result = $this->engine->patch( $map['fields'], $old, $input );
		if ( $result['errors'] ) { $result['map'] = $map['id']; return $result; }
		if ( $input && ! $this->storage->save( $post_id, $map, $result['values'], (int) $request['flx_map_revision'] ) ) { return $error( 'Dữ liệu đã đổi ở cửa sổ khác. Tải lại trước khi lưu.' ); }
		$this->resolver->clear( $post_id ); return array( 'saved' => true );
	}
	public function render( $post ) {
		$map = $this->resolver->map( $post->ID ); if ( ! $map ) { return; }
		$root = $this->resolver->payload( $post->ID );
		$values = isset( $root['maps'][ $map['id'] ]['data'] ) ? $root['maps'][ $map['id'] ]['data'] : array();
		if ( ! is_array( $values ) ) { $values = array(); $this->resolver->diagnostic( 'template_storage_invalid', $post->ID, $map['id'] ); }
		$notice = get_transient( $this->notice_key( $post->ID ) ); delete_transient( $this->notice_key( $post->ID ) );
		$errors = isset( $notice['errors'] ) ? $notice['errors'] : array();
		if ( isset( $notice['display'], $notice['map'] ) && $notice['map'] === $map['id'] ) { $values = $notice['display']; }
		$values = array_replace( $this->engine->defaults( $map['fields'] ), $values );
		$renderer = new \FLX\Fields\Renderer( $this->engine, $errors );
		echo '<div class="flx-template-content"><p>' . esc_html( $map['label'] ) . ' · schema ' . esc_html( $map['version'] ) . '</p><p>Đổi template: lưu và tải lại để mở bộ nội dung tương ứng. Dữ liệu template cũ được giữ lại.</p>';
		foreach ( $errors as $message ) { echo '<p class="flx-field-error" role="alert">' . esc_html( $message ) . '</p>'; }
		foreach ( $this->resolver->diagnostics() as $diagnostic ) { if ( $post->ID === $diagnostic['post_id'] ) { echo '<p role="status">' . esc_html( $diagnostic['code'] ) . ': ' . esc_html( $diagnostic['map'] ) . '</p>'; } }
		wp_nonce_field( 'flx-map-' . $post->ID, 'flx_map_nonce' );
		echo '<input type="hidden" name="flx_map_id" value="' . esc_attr( $map['id'] ) . '"><input type="hidden" name="flx_map_version" value="' . esc_attr( $map['version'] ) . '"><input type="hidden" name="flx_map_revision" value="' . esc_attr( isset( $root['revision'] ) ? $root['revision'] : 0 ) . '">';
		foreach ( $map['groups'] as $group ) {
			echo '<section class="flx-settings-group"><h3>' . esc_html( $group['label'] ) . '</h3>';
			if ( isset( $group['description'] ) ) { echo '<p>' . esc_html( $group['description'] ) . '</p>'; }
			foreach ( $group['fields'] as $key => $field ) { $path = $group['id'] . '.' . $key; $renderer->field( $path, $field, $values[ $path ], 'flx_map_values[' . $path . ']' ); }
			echo '</section>';
		}
		echo '<input type="hidden" name="flx_map_complete" value="1"></div>';
	}
}
