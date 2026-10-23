<?php
/**
 * Plugin Name: WP Benison StickyFX
 * Description: Free Elementor sticky header effects with responsive controls, shrinking, logo swapping, blur, opacity, and scroll animations.
 * Version: 0.0.1
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: WP Benison
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: stickyfx-header-effects
 * Domain Path: /languages
 *
 * @package WPBenisonStickyFX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'STICKYFX_VERSION' ) ) {
	define( 'STICKYFX_VERSION', '0.0.1' );
}

/**
 * Main plugin class.
 */
final class StickyFX_Plugin {

	/**
	 * Register Elementor controls and frontend hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'register_elementor_controls' ) );
		add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'register_elementor_controls' ) );

		add_action( 'elementor/frontend/container/before_render', array( $this, 'apply_elementor_settings' ) );
		add_action( 'elementor/frontend/section/before_render', array( $this, 'apply_elementor_settings' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register StickyFX controls in Elementor's Advanced tab.
	 *
	 * @param \Elementor\Element_Base $element Elementor element instance.
	 * @return void
	 */
	public function register_elementor_controls( $element ) {
		if ( ! class_exists( '\Elementor\Controls_Manager' ) || ! is_object( $element ) ) {
			return;
		}

		$element->start_controls_section(
			'stickyfx_advanced_section',
			array(
				'label' => esc_html__( 'WP Benison: StickyFX', 'stickyfx-header-effects' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'stickyfx_element_enabled',
			array(
				'label'        => esc_html__( 'Enable StickyFX', 'stickyfx-header-effects' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'stickyfx-header-effects' ),
				'label_off'    => esc_html__( 'No', 'stickyfx-header-effects' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$element->add_control(
			'stickyfx_scroll_threshold',
			array(
				'label'     => esc_html__( 'Scroll threshold (px)', 'stickyfx-header-effects' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 5000,
				'step'      => 1,
				'default'   => 50,
				'condition' => array( 'stickyfx_element_enabled' => 'yes' ),
			)
		);


		$element->add_control(
			'stickyfx_scrolled_background',
			array(
				'label'     => esc_html__( 'Background after scrolling', 'stickyfx-header-effects' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => array( 'stickyfx_element_enabled' => 'yes' ),
			)
		);


		$element->add_control(
			'stickyfx_pro_hide_on_scroll',
			array(
				'label' => esc_html__( 'Hide on scroll down, show on scroll up', 'stickyfx-header-effects' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'stickyfx-header-effects' ),
				'label_off' => esc_html__( 'No', 'stickyfx-header-effects' ),
				'return_value' => 'yes',
				'default' => '',
				'description' => esc_html__( 'Hides this element while scrolling down and reveals it while scrolling up.', 'stickyfx-header-effects' ),
			)
		);

		$element->add_control(
			'stickyfx_pro_device_heading',
			array(
				'label' => esc_html__( 'Device-specific behavior', 'stickyfx-header-effects' ),
				'type' => \Elementor\Controls_Manager::HEADING,
			)
		);

		foreach ( array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ) as $device_key => $device_label ) {
			$element->add_control(
				'stickyfx_pro_' . $device_key . '_heading',
				array(
					'label' => esc_html( $device_label ),
					'type' => \Elementor\Controls_Manager::HEADING,
				)
			);
			$element->add_control(
				'stickyfx_pro_' . $device_key . '_enabled',
				array(
					'label' => sprintf( esc_html__( 'Enable on %s', 'stickyfx-header-effects' ), $device_label ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Yes', 'stickyfx-header-effects' ),
					'label_off' => esc_html__( 'No', 'stickyfx-header-effects' ),
					'return_value' => 'yes',
					'default' => 'yes',
				)
			);
			$element->add_control(
				'stickyfx_pro_' . $device_key . '_threshold',
				array(
					'label' => sprintf( esc_html__( '%s scroll threshold (px)', 'stickyfx-header-effects' ), $device_label ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0, 'max' => 5000, 'step' => 1, 'default' => '',
					'condition' => array( 'stickyfx_pro_' . $device_key . '_enabled' => 'yes' ),
				)
			);
			$element->add_control(
				'stickyfx_pro_' . $device_key . '_offset',
				array(
					'label' => sprintf( esc_html__( '%s top offset (px)', 'stickyfx-header-effects' ), $device_label ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0, 'max' => 500, 'step' => 1, 'default' => 0,
					'condition' => array( 'stickyfx_pro_' . $device_key . '_enabled' => 'yes' ),
				)
			);
		}


		$element->add_control('stickyfx_pro_effects_heading', array(
			'label' => esc_html__( 'Scroll Effects', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::HEADING,
		));
		$element->add_control('stickyfx_pro_shrink', array(
			'label' => esc_html__( 'Shrink when scrolled', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes', 'default' => '',
		));
		$element->add_control('stickyfx_pro_shrink_scale', array(
			'label' => esc_html__( 'Scrolled size (%)', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 100, 'step' => 1, 'placeholder' => '85',
			'condition' => array('stickyfx_pro_shrink' => 'yes'),
		));
		$element->add_control('stickyfx_pro_scrolled_logo', array(
			'label' => esc_html__( 'Scrolled logo URL', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::TEXT, 'input_type' => 'url', 'label_block' => true,
			'description' => esc_html__( 'Optional image URL to use after scrolling.', 'stickyfx-header-effects' ),
		));
		$element->add_control('stickyfx_pro_mobile_logo', array(
			'label' => esc_html__( 'Mobile logo URL', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::TEXT, 'input_type' => 'url', 'label_block' => true,
			'description' => esc_html__( 'Optional image URL to use on mobile.', 'stickyfx-header-effects' ),
		));
		$element->add_control('stickyfx_pro_logo_selector', array(
			'label' => esc_html__( 'Logo image CSS selector', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true,
			'default' => '.elementor-widget-theme-site-logo img',
			'description' => esc_html__( 'Target only your logo image. Use a specific selector if needed.', 'stickyfx-header-effects' ),
		));
		$element->add_control('stickyfx_pro_blur', array(
			'label' => esc_html__( 'Background blur (px)', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'max' => 30, 'step' => 1, 'default' => 0,
		));
		$element->add_control('stickyfx_pro_opacity', array(
			'label' => esc_html__( 'Scrolled opacity (%)', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 10, 'max' => 100, 'step' => 1, 'default' => 100,
		));
		$element->add_control('stickyfx_pro_transition', array(
			'label' => esc_html__( 'Transition duration (ms)', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'max' => 2000, 'step' => 50, 'default' => 250,
		));
		$element->add_control('stickyfx_pro_animation', array(
			'label' => esc_html__( 'Scroll animation', 'stickyfx-header-effects' ),
			'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'fade',
			'options' => array('none' => 'None', 'fade' => 'Fade', 'slide' => 'Slide', 'zoom' => 'Zoom'),
		));


		$element->end_controls_section();
	}

	/**
	 * Add frontend attributes to enabled Elementor elements.
	 *
	 * @param \Elementor\Element_Base $element Elementor element.
	 * @return void
	 */
	public function apply_elementor_settings( $element ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_settings_for_display' ) || ! method_exists( $element, 'add_render_attribute' ) ) {
			return;
		}

		if ( 'yes' !== $element->get_settings_for_display( 'stickyfx_element_enabled' ) ) {
			return;
		}

		$threshold = absint( $element->get_settings_for_display( 'stickyfx_scroll_threshold' ) );
		$threshold = min( $threshold, 5000 );

		$background = sanitize_hex_color( $element->get_settings_for_display( 'stickyfx_scrolled_background' ) );
		if ( ! $background ) {
			$background = '#ffffff';
		}

		$element->add_render_attribute( '_wrapper', 'class', 'stickyfx-enabled' );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-threshold', (string) $threshold );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-background', $background );

		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro', 'yes' );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-shrink', 'yes' === $element->get_settings_for_display( 'stickyfx_pro_shrink' ) ? 'yes' : 'no' );
		$shrink_scale = $element->get_settings_for_display( 'stickyfx_pro_shrink_scale' );
		$shrink_scale = ( '' === $shrink_scale || null === $shrink_scale ) ? 85 : absint( $shrink_scale );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-shrink-scale', (string) min( 100, max( 1, $shrink_scale ) ) );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-blur', (string) min( 30, absint( $element->get_settings_for_display( 'stickyfx_pro_blur' ) ) ) );
		$opacity = $element->get_settings_for_display( 'stickyfx_pro_opacity' );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-opacity', (string) min( 100, max( 10, ( '' === $opacity || null === $opacity ) ? 100 : absint( $opacity ) ) ) );
		$transition = $element->get_settings_for_display( 'stickyfx_pro_transition' );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-transition', (string) min( 2000, absint( $transition ? $transition : 250 ) ) );
		$animation = $element->get_settings_for_display( 'stickyfx_pro_animation' );
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-animation', in_array( $animation, array( 'none', 'fade', 'slide', 'zoom' ), true ) ? $animation : 'fade' );
		foreach ( array( 'scrolled_logo' => 'scrolled-logo', 'mobile_logo' => 'mobile-logo', 'logo_selector' => 'logo-selector' ) as $setting => $attribute ) {
			$value = $element->get_settings_for_display( 'stickyfx_pro_' . $setting );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$element->add_render_attribute( '_wrapper', 'data-stickyfx-pro-' . $attribute, esc_attr( trim( $value ) ) );
			}
		}
		$element->add_render_attribute( '_wrapper', 'data-stickyfx-hide-on-scroll', 'yes' === $element->get_settings_for_display( 'stickyfx_pro_hide_on_scroll' ) ? 'yes' : 'no' );
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device_key ) {
			$enabled_setting = $element->get_settings_for_display( 'stickyfx_pro_' . $device_key . '_enabled' );
			$enabled = ( 'yes' === $enabled_setting ) ? 'yes' : 'no';
			$threshold_value = $element->get_settings_for_display( 'stickyfx_pro_' . $device_key . '_threshold' );
			$offset_value = $element->get_settings_for_display( 'stickyfx_pro_' . $device_key . '_offset' );
			$threshold = ( '' === $threshold_value || null === $threshold_value ) ? absint( $element->get_settings_for_display( 'stickyfx_scroll_threshold' ) ) : min( absint( $threshold_value ), 5000 );
			$offset = min( absint( $offset_value ), 500 );
			$element->add_render_attribute( '_wrapper', 'data-stickyfx-' . $device_key . '-enabled', $enabled );
			$element->add_render_attribute( '_wrapper', 'data-stickyfx-' . $device_key . '-threshold', (string) $threshold );
			$element->add_render_attribute( '_wrapper', 'data-stickyfx-' . $device_key . '-offset', (string) $offset );
			$element->add_render_attribute( '_wrapper', 'data-stickyfx-' . $device_key . '-hide-on-scroll', 'yes' === $element->get_settings_for_display( 'stickyfx_pro_hide_on_scroll' ) ? 'yes' : 'no' );
		}

	}

	/**
	 * Enqueue frontend CSS and JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'stickyfx-frontend',
			plugin_dir_url( __FILE__ ) . 'assets/css/stickyfx-frontend.css',
			array(),
			STICKYFX_VERSION
		);

		wp_enqueue_script(
			'stickyfx-frontend',
			plugin_dir_url( __FILE__ ) . 'assets/js/stickyfx-frontend.js',
			array(),
			STICKYFX_VERSION,
			true
		);

		wp_enqueue_style(
			'stickyfx-effects',
			plugin_dir_url( __FILE__ ) . 'assets/css/stickyfx-pro.css',
			array( 'stickyfx-frontend' ),
			STICKYFX_VERSION
		);
		wp_enqueue_script(
			'stickyfx-effects',
			plugin_dir_url( __FILE__ ) . 'assets/js/stickyfx-pro.js',
			array( 'stickyfx-frontend' ),
			STICKYFX_VERSION,
			true
		);
	}

}

/**
 * Initialize StickyFX only when Elementor is available.
 *
 * @return void
 */
function stickyfx_initialize_plugin() {
	if ( ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}

	$plugin = new StickyFX_Plugin();
	$plugin->init();
}
add_action( 'plugins_loaded', 'stickyfx_initialize_plugin' );
