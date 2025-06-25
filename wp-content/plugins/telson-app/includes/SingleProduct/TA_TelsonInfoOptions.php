<?php

namespace App\TelsonApp\SingleProduct;

if (!defined('ABSPATH')) {
    exit;
}

use StoutLogic\AcfBuilder\FieldsBuilder;

class TA_TelsonInfoOptions {

	public function __construct() {
		add_action( 'acf/init', [ $this, 'registerOptionsPage' ] );
		add_action( 'acf/init', [ $this, 'registerFields' ] );
	}

	/**
	 * 1) Create the options page.
	 */
	public function registerOptionsPage(): void {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page( [
			'page_title' => 'Telson Info Product',
			'menu_title' => 'Telson Info Product',
			'menu_slug'  => 'telson-info-product',
			'capability' => 'manage_options',
			'position'   => 25, // 👈 Add the tab at position 12
			'icon_url'   => 'dashicons-screenoptions',
			'redirect'   => false,
		] );
		
	}

	/**
	 * 2–3) Define the WYSIWYG + Repeater field group.
	 */
	public function registerFields(): void {

		$telsonInfo = new FieldsBuilder( 'telson_info_product' );

		$telsonInfo
			->addText('checkout_info', [
				'label' => 'Custom checkout information',
				'wrapper' => ['width' => 100],
			])
			->addWysiwyg( 'have_a_question', [
				'label'        => 'Have a question',
				'media_upload' => true,
				'tabs'         => 'all',
				'wrapper'      => [ 'width' => 100 ],
			] )

			->addWysiwyg( 'need_help', [
				'label'        => 'Need help section',
				'media_upload' => true,
				'tabs'         => 'all',
				'wrapper'      => [ 'width' => 100 ],
			] )
			
		

			->addText('left_help_title', [
				'label' => 'Left Help Title',
				'wrapper' => ['width' => 100],
			])

			->addText('right_help_title', [
				'label' => 'Right Help Title',
				'wrapper' => ['width' => 100],
			])

			->addRepeater( 'product_badges', [
				'label'        => 'Product Badges',
				'min'          => 1,
				'max'          => 1,  // exactly one row that holds the six images
				'layout'       => 'row',
				'button_label' => 'Set images',
			] )
				->addImage( 'argon', [
					'label'         => 'Argon',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
				->addImage( 'ed_glass', [
					'label'         => 'ED Glass',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
				->addImage( 'magnification', [
					'label'         => 'Magnification',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
				->addImage( 'ip67', [
					'label'         => 'IP67',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
				->addImage( 'warranty', [
					'label'         => 'Warranty',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
				->addImage( 'ballistic_turret_alert', [
					'label'         => 'Ballistic Turret Alert',
					'return_format' => 'array',
					'preview_size'  => 'thumbnail',
					'wrapper'       => [ 'width' => 16 ],
				] )
			->endRepeater();

		// Show this group on our custom options page.
		$telsonInfo->setLocation( 'options_page', '==', 'telson-info-product' );

		acf_add_local_field_group( $telsonInfo->build() );
	}
}

// Bootstrap it.

