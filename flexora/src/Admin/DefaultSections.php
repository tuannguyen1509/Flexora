<?php
namespace FLX\Admin;

/** Neutral native settings plus an explicitly development-only integration playground. */
final class DefaultSections {
	public static function register( SectionRegistry $registry, $development ) {
		$registry->register( 'general', array( 'title' => 'General', 'description' => 'Thông tin chung của website.', 'groups' => array(
			array( 'title' => 'Company', 'description' => 'Nội dung dùng chung. Chưa tự động thay đổi giao diện.', 'fields' => array(
				'company_name' => array( 'type' => 'text', 'label' => 'Tên đơn vị', 'maxlength' => 120, 'default' => '' ),
				'introduction' => array( 'type' => 'editor', 'label' => 'Giới thiệu' ),
			) ),
			array( 'title' => 'Contact', 'fields' => array(
				'email' => array( 'type' => 'email', 'label' => 'Email liên hệ' ), 'hotline' => array( 'type' => 'text', 'label' => 'Hotline' ),
			) ),
		) ) );
		$registry->register( 'design', array( 'title' => 'Design', 'groups' => array( array( 'title' => 'Interface', 'description' => 'Lưu cấu hình thiết kế; chưa áp dụng token ra frontend trong phase này.', 'fields' => array(
			'accent' => array( 'type' => 'color', 'label' => 'Màu chủ đạo', 'default' => '#3157d5' ),
		) ) ) ) );
		if ( ! $development ) { return; }
		$registry->register( 'playground', array( 'title' => 'Field Playground', 'description' => 'Chỉ xuất hiện trong development mode. Dữ liệu thử không được dùng để render website.', 'groups' => array(
			array( 'title' => 'Content', 'fields' => array(
				'title' => array( 'type' => 'text', 'label' => 'Text', 'placeholder' => 'Tiêu đề ngắn', 'maxlength' => 100, 'required' => true ),
				'plain' => array( 'type' => 'textarea', 'label' => 'Textarea', 'rows' => 4, 'maxlength' => 1000 ),
				'rich' => array( 'type' => 'editor', 'label' => 'Editor' ),
			) ),
			array( 'title' => 'Choices & values', 'fields' => array(
				'count' => array( 'type' => 'number', 'label' => 'Number', 'min' => 0, 'max' => 100, 'step' => 1, 'default' => 0 ),
				'link' => array( 'type' => 'url', 'label' => 'URL' ), 'email' => array( 'type' => 'email', 'label' => 'Email' ),
				'layout' => array( 'type' => 'select', 'label' => 'Select', 'options' => array( 'standard' => 'Tiêu chuẩn', 'compact' => 'Gọn' ), 'default' => 'standard' ),
				'alignment' => array( 'type' => 'radio', 'label' => 'Radio', 'options' => array( 'left' => 'Trái', 'center' => 'Giữa' ), 'default' => 'left' ),
				'enabled' => array( 'type' => 'toggle', 'label' => 'Toggle', 'default' => false ),
				'color' => array( 'type' => 'color', 'label' => 'Color', 'default' => '#3157d5' ),
			) ),
			array( 'title' => 'Images', 'fields' => array(
				'image' => array( 'type' => 'media', 'label' => 'Media' ), 'images' => array( 'type' => 'gallery', 'label' => 'Gallery' ),
			) ),
			array( 'title' => 'Services', 'fields' => array( 'services' => array( 'type' => 'repeater', 'label' => 'Dịch vụ', 'item_label' => 'dịch vụ', 'fields' => array(
				'title' => array( 'type' => 'text', 'label' => 'Title' ), 'description' => array( 'type' => 'editor', 'label' => 'Description' ),
				'image' => array( 'type' => 'media', 'label' => 'Image' ), 'url' => array( 'type' => 'url', 'label' => 'URL' ),
				'enabled' => array( 'type' => 'toggle', 'label' => 'Enabled', 'default' => true ),
				'style' => array( 'type' => 'select', 'label' => 'Style', 'options' => array( 'standard' => 'Tiêu chuẩn', 'compact' => 'Gọn' ), 'default' => 'standard' ),
			) ) ) ),
		) ) );
	}
}
