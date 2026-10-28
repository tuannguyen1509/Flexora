<?php
defined( 'ABSPATH' ) || exit;

/** Creates a website-specific child once, without replacing existing files. */
class AZ_Child_Theme_Installer {
    const CHILD_SLUG = 'az-theme-child';
    const NOTICE_KEY = 'az_child_theme_install_status';

    public function __construct() {
        add_action( 'after_switch_theme', array( $this, 'install_after_parent_activation' ) );
        if ( is_admin() ) {
            add_action( 'admin_notices', array( $this, 'render_activation_prompt' ) );
            add_action( 'admin_post_az_create_child_theme', array( $this, 'create_child_theme' ) );
            add_action( 'admin_post_az_dismiss_child_theme_prompt', array( $this, 'dismiss_prompt' ) );
        }
    }

    public static function is_parent_active() {
        return get_template() === get_stylesheet() && 'az-theme' === get_template();
    }

    public static function child_theme_path() {
        return trailingslashit( get_theme_root( 'az-theme' ) ) . self::CHILD_SLUG;
    }

    public static function child_theme_exists() {
        $theme = wp_get_theme( self::CHILD_SLUG, get_theme_root( 'az-theme' ) );
        return $theme->exists() && ! $theme->errors() && 'az-theme' === $theme->get( 'Template' );
    }

    /** The activation event itself is authorized; its next request may be anonymous. */
    public function install_after_parent_activation() {
        if ( ! self::is_parent_active() ) {
            return;
        }

        $result = $this->install_child_theme();
        set_transient( self::NOTICE_KEY, is_wp_error( $result ) ? $result->get_error_code() : $result, WEEK_IN_SECONDS );
        delete_transient( 'az_child_theme_install_prompt' );
    }

    /** @return string|WP_Error 'created', 'exists', or an installation error. */
    public function install_child_theme() {
        if ( ! self::is_parent_active() ) {
            return new WP_Error( 'parent_inactive', 'Hãy kích hoạt AZ Theme chính trước khi tạo theme child.' );
        }

        wp_clean_themes_cache();
        if ( self::child_theme_exists() ) {
            return 'exists';
        }
        // An incomplete or unrelated directory belongs to the site owner too.
        if ( file_exists( self::child_theme_path() ) || is_link( self::child_theme_path() ) ) {
            return new WP_Error( 'child_theme_conflict', 'Thư mục az-theme-child đã tồn tại nhưng không phải child theme hợp lệ của az-theme. Không ghi đè; hãy kiểm tra hoặc đổi tên thư mục đó rồi thử lại.' );
        }
        if ( ! wp_is_file_mod_allowed( 'az_create_child_theme' ) || ( is_multisite() && ! is_super_admin() ) ) {
            return new WP_Error( 'file_mods_disabled', 'WordPress không cho phép tạo theme trên website này. Hãy nhờ quản trị viên hosting hoặc quản trị mạng cài az-theme-child từ bộ mẫu trong az-theme.' );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        global $wp_filesystem;
        $theme_root = get_theme_root( 'az-theme' );
        // Never request FTP credentials from an automatic activation callback.
        if ( ! WP_Filesystem( false, $theme_root ) || ! $wp_filesystem ) {
            return $this->filesystem_error();
        }

        $target_root = $wp_filesystem->find_folder( $theme_root );
        $source_path = $wp_filesystem->find_folder( get_template_directory() . '/inc/admin/templates/child-theme' );
        if ( ! $target_root || ! $source_path ) {
            return $this->filesystem_error();
        }
        $target_path = trailingslashit( $target_root ) . self::CHILD_SLUG;
        if ( $wp_filesystem->exists( $target_path ) ) {
            return new WP_Error( 'child_theme_conflict', 'Thư mục az-theme-child đã tồn tại. Không ghi đè; hãy kiểm tra thư mục đó rồi thử lại.' );
        }

        // Read all sources before writing; publish style.css last so WordPress
        // cannot discover a half-written child theme.
        $files = array();
        foreach ( array( 'functions.php', 'README.md', 'style.css' ) as $file ) {
            $contents = $wp_filesystem->get_contents( trailingslashit( $source_path ) . $file );
            if ( false === $contents || '' === trim( $contents ) ) {
                return new WP_Error( 'source_error', 'Bộ mẫu child theme trong az-theme bị thiếu hoặc không đọc được. Hãy upload đầy đủ thư mục inc/admin/templates/child-theme.' );
            }
            $files[ $file ] = $contents;
        }

        // Do not proceed if another request has already created this directory.
        if ( ! $wp_filesystem->mkdir( $target_path, FS_CHMOD_DIR ) ) {
            return $this->filesystem_error();
        }
        foreach ( $files as $file => $contents ) {
            if ( ! $wp_filesystem->put_contents( trailingslashit( $target_path ) . $file, $contents, FS_CHMOD_FILE ) ) {
                // Only remove the known files in the directory we just created.
                foreach ( array_keys( $files ) as $created_file ) {
                    $wp_filesystem->delete( trailingslashit( $target_path ) . $created_file );
                }
                $wp_filesystem->delete( $target_path, false, 'd' );
                wp_clean_themes_cache();
                return $this->filesystem_error();
            }
        }

        wp_clean_themes_cache();
        return 'created';
    }

    private function filesystem_error() {
        return new WP_Error( 'filesystem_error', 'Không thể tạo az-theme-child tự động. Hãy kiểm tra quyền ghi thư mục wp-content/themes hoặc cấu hình kết nối filesystem của hosting, rồi bấm Thử tạo lại. Bạn cũng có thể copy bộ mẫu inc/admin/templates/child-theme thành thư mục wp-content/themes/az-theme-child.' );
    }

    public function render_activation_prompt() {
        if ( ! current_user_can( 'switch_themes' ) || ! self::is_parent_active() ) {
            return;
        }
        $status = get_transient( self::NOTICE_KEY );
        if ( ! is_string( $status ) || '' === $status ) {
            return;
        }

        $messages = array(
            'created'              => 'AZ Theme đã tự tạo az-theme-child. Kích hoạt theme con rồi lưu CSS, PHP và bố cục riêng của website tại đó.',
            'exists'               => 'az-theme-child đã tồn tại và được giữ nguyên toàn bộ. Kích hoạt theme con để sử dụng giao diện riêng của website.',
            'filesystem_error'     => $this->filesystem_error()->get_error_message(),
            'child_theme_conflict' => 'Thư mục az-theme-child đã tồn tại nhưng không phải child theme hợp lệ của az-theme. Không ghi đè; hãy kiểm tra thư mục đó rồi thử lại.',
            'file_mods_disabled'   => 'WordPress không cho phép tạo theme trên website này. Hãy nhờ quản trị viên hosting hoặc quản trị mạng cài az-theme-child từ bộ mẫu trong az-theme.',
            'source_error'         => 'Bộ mẫu child theme bị thiếu hoặc không đọc được. Hãy upload đầy đủ thư mục inc/admin/templates/child-theme trong az-theme.',
        );
        if ( ! isset( $messages[ $status ] ) ) {
            return;
        }
        $success = in_array( $status, array( 'created', 'exists' ), true );
        $dismiss_url = wp_nonce_url( admin_url( 'admin-post.php?action=az_dismiss_child_theme_prompt' ), 'az_dismiss_child_theme_prompt' );
        ?>
        <div class="notice <?php echo $success ? 'notice-success' : 'notice-error'; ?>">
            <p><strong>AZ Theme:</strong> <?php echo esc_html( $messages[ $status ] ); ?></p>
            <p>
                <?php if ( $success && self::child_theme_exists() ) : ?>
                    <?php if ( ! is_multisite() || wp_get_theme( self::CHILD_SLUG )->is_allowed() ) : ?>
                        <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'themes.php?action=activate&stylesheet=' . self::CHILD_SLUG ), 'switch-theme_' . self::CHILD_SLUG ) ); ?>">Kích hoạt AZ Theme Child</a>
                    <?php else : ?>
                        <span>Quản trị mạng cần bật AZ Theme Child cho website trước khi kích hoạt.</span>
                    <?php endif; ?>
                <?php elseif ( current_user_can( 'install_themes' ) ) : ?>
                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=az_create_child_theme' ), 'az_create_child_theme' ) ); ?>">Thử tạo lại</a>
                <?php endif; ?>
                <a class="button" href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">Quản lý giao diện</a>
                <a class="button" href="<?php echo esc_url( $dismiss_url ); ?>">Đóng thông báo</a>
            </p>
        </div>
        <?php
    }

    public function create_child_theme() {
        if ( ! current_user_can( 'switch_themes' ) || ! current_user_can( 'install_themes' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền tạo theme child.', 'az-theme' ) );
        }
        check_admin_referer( 'az_create_child_theme' );
        $result = $this->install_child_theme();
        $status = is_wp_error( $result ) ? $result->get_error_code() : $result;
        set_transient( self::NOTICE_KEY, $status, WEEK_IN_SECONDS );
        delete_transient( 'az_child_theme_install_prompt' );
        $this->redirect( $status );
    }

    public function dismiss_prompt() {
        if ( ! current_user_can( 'switch_themes' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện thao tác này.', 'az-theme' ) );
        }
        check_admin_referer( 'az_dismiss_child_theme_prompt' );
        delete_transient( self::NOTICE_KEY );
        delete_transient( 'az_child_theme_install_prompt' );
        $this->redirect( 'dismissed' );
    }

    private function redirect( $status ) {
        wp_safe_redirect( add_query_arg( 'az_child_theme', sanitize_key( $status ), admin_url( 'admin.php?page=az-theme' ) ) );
        exit;
    }
}
