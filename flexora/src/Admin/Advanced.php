<?php
namespace FLX\Admin;

use FLX\Fields\Renderer;
use FLX\Data\SettingsStore;

/** Admin composition root. No content queries, default writes, or frontend assets. */
final class Advanced {
	private static $instance;
	private $engine;
	private $sections;
	private $store;
	private $errors = array();
	private $attempt = null;
	private $screen;
	public static function attach() {
		add_action( 'after_setup_theme', array( self::class, 'initialize' ), 35 );
	}
	public static function instance() {
		if ( ! self::$instance ) { throw new \LogicException( 'Settings registration has not opened.' ); }
		return self::$instance;
	}
	public static function initialize() {
		if ( self::$instance ) { return; }
		$self = new self(); self::$instance = $self;
		$self->engine = \FLX\Fields\Runtime::engine(); $self->sections = new SectionRegistry( $self->engine ); $self->store = new SettingsStore();
		DefaultSections::register( $self->sections, self::development() );
		do_action( 'flx/settings/register' ); $self->sections->seal();
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $self, 'menu' ) );
			add_action( 'admin_init', array( $self, 'submit' ) );
			add_action( 'admin_enqueue_scripts', array( $self, 'assets' ) );
		}
	}
	public static function development() { return defined( 'WP_DEBUG' ) && WP_DEBUG || defined( 'FLX_DEVELOPMENT' ) && FLX_DEVELOPMENT; }
	public function register_section( $id, array $section ) { $this->sections->register( $id, $section ); }
	public function menu() {
		add_menu_page( 'Flexora Advanced', 'Flexora', 'manage_options', 'flx-advanced', array( $this, 'render' ), 'dashicons-admin-generic', 61 );
		$this->screen = add_submenu_page( 'flx-advanced', 'Advanced', 'Advanced', 'manage_options', 'flx-advanced', array( $this, 'render' ) );
	}
	private function current_id() {
		$id = isset( $_GET['section'] ) && is_string( $_GET['section'] ) ? wp_unslash( $_GET['section'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return preg_match( '/^[a-z][a-z0-9_.-]*$/D', $id ) ? $id : '';
	}
	public function submit() {
		if ( ! isset( $_GET['page'] ) || 'flx-advanced' !== $_GET['page'] || 'POST' !== $_SERVER['REQUEST_METHOD'] ) { return; }
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Bạn không có quyền lưu cấu hình.', '', array( 'response' => 403 ) ); }
		$id = $this->current_id(); $section = $this->sections->get( $id );
		check_admin_referer( 'flx-settings-' . $id, 'flx_nonce' );
		if ( ! $section || ! isset( $_POST['flx_complete'], $_POST['flx_revision'] ) || '1' !== $_POST['flx_complete'] || ! is_string( $_POST['flx_revision'] ) || ! ctype_digit( $_POST['flx_revision'] ) ) { wp_die( 'Yêu cầu không đầy đủ. Không có dữ liệu nào được lưu.', '', array( 'response' => 400 ) ); }
		$input = isset( $_POST['flx_values'] ) ? wp_unslash( $_POST['flx_values'] ) : array();
		if ( ! is_array( $input ) ) { wp_die( 'Dữ liệu không hợp lệ.', '', array( 'response' => 400 ) ); }
		$data = $this->store->read(); $old = isset( $data[ $id ] ) && is_array( $data[ $id ] ) ? $data[ $id ] : array();
		$result = $this->engine->patch( $section['fields'], $old, $input );
		$this->errors = $result['errors']; $this->attempt = $result['display'];
		if ( $this->errors ) { return; }
		if ( array() !== $input && ! $this->store->save( $id, $result['values'], (int) $_POST['flx_revision'] ) ) { $this->errors['_form'] = 'Cấu hình đã được thay đổi ở một cửa sổ khác. Tải lại trang trước khi lưu.'; return; }
		wp_safe_redirect( add_query_arg( array( 'page' => 'flx-advanced', 'section' => $id, 'saved' => '1' ), admin_url( 'admin.php' ) ) ); exit;
	}
	public function assets( $hook ) {
		if ( ! in_array( $hook, array( 'toplevel_page_flx-advanced', $this->screen ), true ) ) { return; }
		$section = $this->sections->get( $this->current_id() ); if ( ! $section ) { return; }
		flx_asset( 'flx.admin.advanced' );
		\FLX\Fields\AdminAssets::enqueue( $section['fields'] );
	}
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Bạn không có quyền xem cấu hình.', '', array( 'response' => 403 ) ); }
		$id = $this->current_id(); $section = $this->sections->get( $id );
		if ( ! $section ) { wp_die( 'Không tìm thấy section.', '', array( 'response' => 404 ) ); }
		$data = $this->store->read(); $values = null !== $this->attempt ? $this->attempt : ( isset( $data[ $id ] ) && is_array( $data[ $id ] ) ? $data[ $id ] : array() );
		$values = array_replace( $this->engine->defaults( $section['fields'] ), $values );
		$renderer = new Renderer( $this->engine, $this->errors );
		echo '<div class="wrap flx-advanced"><header class="flx-admin-header"><span class="flx-wordmark">Flexora</span><h1>Advanced</h1><p>Cấu hình rõ ràng. Nội dung linh hoạt.</p></header><div class="flx-admin-layout"><nav class="flx-admin-nav" aria-label="Settings sections">';
		foreach ( $this->sections->all() as $key => $item ) {
			echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'flx-advanced', 'section' => $key ), admin_url( 'admin.php' ) ) ) . '"' . ( $key === $id ? ' aria-current="page"' : '' ) . '>' . esc_html( $item['title'] ) . '</a>';
		}
		echo '</nav><main class="flx-admin-content"><h2>' . esc_html( $section['title'] ) . '</h2>';
		if ( isset( $section['description'] ) ) { echo '<p class="flx-section-description">' . esc_html( $section['description'] ) . '</p>'; }
		if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) { echo '<div class="notice notice-success inline" role="status"><p>Đã lưu cấu hình.</p></div>'; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $this->errors ) {
			echo '<div class="notice notice-error inline" role="alert"><p>Chưa lưu. Vui lòng kiểm tra các trường được báo lỗi.</p><ul>';
			foreach ( array_unique( $this->errors ) as $message ) { echo '<li>' . esc_html( $message ) . '</li>'; }
			echo '</ul></div>';
		}
		echo '<form method="post" class="flx-settings-form" novalidate>';
		wp_nonce_field( 'flx-settings-' . $id, 'flx_nonce' );
		echo '<input type="hidden" name="flx_revision" value="' . esc_attr( isset( $data['_revision'] ) ? $data['_revision'] : 0 ) . '">';
		foreach ( $section['groups'] as $group ) {
			echo '<section class="flx-settings-group"><h3>' . esc_html( $group['title'] ) . '</h3>';
			if ( isset( $group['description'] ) ) { echo '<p class="flx-group-description">' . esc_html( $group['description'] ) . '</p>'; }
			foreach ( $group['fields'] as $key => $field ) { $renderer->field( $key, $field, $values[ $key ], 'flx_values[' . $key . ']' ); }
			echo '</section>';
		}
		echo '<input type="hidden" name="flx_complete" value="1"><div class="flx-save-bar"><button class="button button-primary button-hero" type="submit">Lưu thay đổi</button><span>Chỉ lưu section hiện tại</span></div></form></main></div></div>';
	}
}
