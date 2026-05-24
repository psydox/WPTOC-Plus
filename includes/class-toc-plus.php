<?php

if ( ! class_exists( 'TOC_Plus' ) ) :
	class TOC_Plus {

		private $path;      // eg /wp-content/plugins/toc
		private $options;
		private $show_toc;  // allows to override the display (eg through [no_toc] shortcode)
		private $exclude_post_types;
		private $collision_collector;  // keeps a track of used anchors for collision detecting
		private $defaults;
		private $post_override_meta_key = '_wptoc_plus_override';
		private $post_heading_meta_key  = '_wptoc_plus_heading_text';

		public function __construct() {
			$this->show_toc            = true;
			$this->exclude_post_types  = [ 'attachment', 'revision', 'nav_menu_item', 'safecss' ];
			$this->collision_collector = [];

			// get options
			$this->defaults = [  // default options
				// XTEC ************ MODIFICAT - Change default settings
				// 2017.05.04 @xaviernietosanchez
				'position' => TOC_POSITION_TOP,
				// ************ ORIGINAL
				/*
				'position'                           => TOC_POSITION_BEFORE_FIRST_HEADING,
				*/
				// ************ FI

				'start'                              => 4,
				'show_heading_text'                  => true,

				// XTEC ************ MODIFICAT - Change default settings
				// 2017.05.16 @xaviernietosanchez
				'heading_text' => __('Contents','table-of-contents-plus'),
				// ************ ORIGINAL
				/*
				'heading_text'                       => 'Contents',
				*/
				// ************ FI

				'auto_insert_post_types'             => [ 'page' ],
				'show_heirarchy'                     => true,
				'collapsible_subsections'            => false,
				'collapse_subsections_by_default'    => true,

				// XTEC ************ MODIFICAT - Change default settings
				// 2017.05.04 @xaviernietosanchez
				'ordered_list' => false,
				'smooth_scroll' => true,
				// ************ ORIGINAL
				/*
				'ordered_list'                       => true,
				'smooth_scroll'                      => false,
				*/
				// ************ FI

				'smooth_scroll_offset'               => TOC_SMOOTH_SCROLL_OFFSET,

				// XTEC ************ MODIFICAT - Change default settings
				// 2017.05.04 @xaviernietosanchez
				'visibility' => false,
				// ************ ORIGINAL
				/*
				'visibility'                         => true,
				*/
				//************ FI

				'visibility_show'                    => 'show',
				'visibility_hide'                    => 'hide',
				'visibility_hide_by_default'         => false,
				'display_mode'                       => 'inline',
				'mobile_mode'                        => 'inline',
				'floating_side'                      => 'right',
				'display_top_offset'                 => 96,
				'exclude_selectors'                  => '',
				'width'                              => 'Auto',
				'width_custom'                       => '275',
				'width_custom_units'                 => 'px',

				// XTEC ************ MODIFICAT - Change default settings
				// 2017.05.04 @xaviernietosanchez
				'wrapping' => TOC_WRAPPING_RIGHT,
				// ************ ORIGINAL
				/*
				'wrapping'                           => TOC_WRAPPING_NONE,
				*/
				// ************ FI

				'font_size'                          => '95',
				'font_size_units'                    => '%',
				'design_preset'                      => 'default',
				'theme'                              => TOC_THEME_GREY,
				'custom_background_colour'           => TOC_DEFAULT_BACKGROUND_COLOUR,
				'custom_border_colour'               => TOC_DEFAULT_BORDER_COLOUR,
				'custom_title_colour'                => TOC_DEFAULT_TITLE_COLOUR,
				'custom_links_colour'                => TOC_DEFAULT_LINKS_COLOUR,
				'custom_links_hover_colour'          => TOC_DEFAULT_LINKS_HOVER_COLOUR,
				'custom_links_visited_colour'        => TOC_DEFAULT_LINKS_VISITED_COLOUR,
				'lowercase'                          => false,
				'hyphenate'                          => false,
				'exclude_css'                        => false,
				'exclude'                            => '',
				'heading_levels'                     => [ 1, 2, 3, 4, 5, 6 ],

				'css_container_class'                => '',
				'show_toc_in_widget_only'            => false,
				'show_toc_in_widget_only_post_types' => [ 'page' ],
			];

			$options       = get_option( 'toc-options', $this->defaults );
			$this->options = wp_parse_args( $options, $this->defaults );

			if ( ! in_array( $this->options['display_mode'], [ 'inline', 'floating', 'sticky', 'sticky-column' ], true ) ) {
				$this->options['display_mode'] = $this->defaults['display_mode'];
			}

			unset(
				$this->options['sidebar_injection_selector'],
				$this->options['sidebar_injection_behavior']
			);

			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
			add_action( 'save_post', [ $this, 'save_post_meta' ] );
			add_action( 'widgets_init', [ $this, 'widgets_init' ] );
			add_action( 'delete_widget', [ $this, 'sidebar_admin_setup' ], 10, 3 );
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'admin_footer-plugins.php', [ $this, 'admin_footer_plugins' ] );

			add_filter( 'the_content', [ $this, 'the_content' ], 100 );  // run after shortcodes are interpreted (level 10)
			add_filter( 'all_plugins', [ $this, 'all_plugins' ] );
			add_filter( 'plugin_action_links', [ $this, 'plugin_action_links' ], 10, 2 );
			add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 4 );
			add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
			add_filter( 'site_transient_update_plugins', [ $this, 'site_transient_update_plugins' ] );
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'site_transient_update_plugins' ] );
			add_filter( 'widget_text', 'do_shortcode' );

			add_shortcode( 'toc', [ $this, 'shortcode_toc' ] );
			add_shortcode( 'no_toc', [ $this, 'shortcode_no_toc' ] );
		}


		public function __destruct() {}


		public function get_options() {
			return $this->options;
		}


		public function set_option( $options ) {
			$this->options = array_merge( $this->options, $options );
		}


		/**
		 * Allows the developer to disable TOC execution
		 */
		public function disable() {
			$this->show_toc = false;
		}


		/**
		 * Allows the developer to enable TOC execution
		 */
		public function enable() {
			$this->show_toc = true;
		}


		public function set_show_toc_in_widget_only( $value = false ) {
			if ( $value ) {
				$this->options['show_toc_in_widget_only'] = true;
			} else {
				$this->options['show_toc_in_widget_only'] = false;
			}

			update_option( 'toc-options', $this->options );
		}


		public function set_show_toc_in_widget_only_post_types( $value = false ) {
			if ( $value ) {
				$this->options['show_toc_in_widget_only_post_types'] = $value;
			} else {
				$this->options['show_toc_in_widget_only_post_types'] = [];
			}

			update_option( 'toc-options', $this->options );
		}


		public function get_exclude_post_types() {
			return $this->exclude_post_types;
		}


		private function get_supported_post_types() {
			return array_values(
				array_filter(
					get_post_types( [ 'show_ui' => true ], 'names' ),
					function ( $post_type ) {
						return ! in_array( $post_type, $this->exclude_post_types, true );
					}
				)
			);
		}


		private function get_current_post() {
			global $post;

			if ( $post instanceof WP_Post ) {
				return $post;
			}

			$post_id = get_the_ID();
			if ( $post_id ) {
				return get_post( $post_id );
			}

			return null;
		}


		private function get_post_override( $post = null ) {
			if ( ! $post instanceof WP_Post ) {
				$post = $this->get_current_post();
			}

			if ( ! $post instanceof WP_Post ) {
				return 'default';
			}

			$override = get_post_meta( $post->ID, $this->post_override_meta_key, true );

			if ( ! in_array( $override, [ 'default', 'show', 'hide' ], true ) ) {
				return 'default';
			}

			return $override;
		}


		private function get_post_heading_text( $post = null ) {
			if ( ! $post instanceof WP_Post ) {
				$post = $this->get_current_post();
			}

			if ( ! $post instanceof WP_Post ) {
				return '';
			}

			return trim( (string) get_post_meta( $post->ID, $this->post_heading_meta_key, true ) );
		}


		private function get_effective_heading_text( $post = null ) {
			$heading_text = $this->get_post_heading_text( $post );

			if ( '' !== $heading_text ) {
				return $heading_text;
			}

			return $this->options['heading_text'];
		}


		public function add_meta_boxes() {
			foreach ( $this->get_supported_post_types() as $post_type ) {
				add_meta_box(
					'wptoc-plus-overrides',
					__( 'WPTOC+ Overrides', 'table-of-contents-plus' ),
					[ $this, 'render_post_meta_box' ],
					$post_type,
					'side',
					'default'
				);
			}
		}


		public function render_post_meta_box( $post ) {
			$override     = $this->get_post_override( $post );
			$heading_text = $this->get_post_heading_text( $post );

			wp_nonce_field( 'wptoc_plus_post_meta', 'wptoc_plus_post_meta_nonce' );
			?>
			<p>
				<label for="wptoc-plus-override"><strong><?php esc_html_e( 'TOC display', 'table-of-contents-plus' ); ?></strong></label>
				<select id="wptoc-plus-override" name="wptoc_plus_override" class="widefat">
					<option value="default"<?php selected( $override, 'default' ); ?>><?php esc_html_e( 'Use global settings', 'table-of-contents-plus' ); ?></option>
					<option value="show"<?php selected( $override, 'show' ); ?>><?php esc_html_e( 'Force show TOC', 'table-of-contents-plus' ); ?></option>
					<option value="hide"<?php selected( $override, 'hide' ); ?>><?php esc_html_e( 'Force hide TOC', 'table-of-contents-plus' ); ?></option>
				</select>
			</p>
			<p>
				<label for="wptoc-plus-heading-text"><strong><?php esc_html_e( 'Custom TOC title', 'table-of-contents-plus' ); ?></strong></label>
				<input type="text" id="wptoc-plus-heading-text" name="wptoc_plus_heading_text" class="widefat" value="<?php echo esc_attr( $heading_text ); ?>" />
			</p>
			<p class="description"><?php esc_html_e( 'Override the TOC visibility or title for this post only.', 'table-of-contents-plus' ); ?></p>
			<?php
		}


		public function save_post_meta( $post_id ) {
			if ( ! isset( $_POST['wptoc_plus_post_meta_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wptoc_plus_post_meta_nonce'] ) ), 'wptoc_plus_post_meta' ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$override = isset( $_POST['wptoc_plus_override'] ) ? sanitize_text_field( wp_unslash( $_POST['wptoc_plus_override'] ) ) : 'default';
			$title    = isset( $_POST['wptoc_plus_heading_text'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['wptoc_plus_heading_text'] ) ) ) : '';

			if ( ! in_array( $override, [ 'default', 'show', 'hide' ], true ) ) {
				$override = 'default';
			}

			if ( 'default' === $override ) {
				delete_post_meta( $post_id, $this->post_override_meta_key );
			} else {
				update_post_meta( $post_id, $this->post_override_meta_key, $override );
			}

			if ( '' === $title ) {
				delete_post_meta( $post_id, $this->post_heading_meta_key );
			} else {
				update_post_meta( $post_id, $this->post_heading_meta_key, $title );
			}
		}


		public function plugin_action_links( $links, $file ) {
			if ( plugin_basename( dirname( __DIR__ ) . '/toc.php' ) === $file ) {
				$settings_link = '<a href="options-general.php?page=toc">' . __( 'Settings', 'table-of-contents-plus' ) . '</a>';
				$links         = array_merge( [ $settings_link ], $links );
			}
			return $links;
		}


		public function all_plugins( $plugins ) {
			$plugin_file = plugin_basename( dirname( __DIR__ ) . '/toc.php' );

			if ( ! isset( $plugins[ $plugin_file ] ) || ! is_array( $plugins[ $plugin_file ] ) ) {
				return $plugins;
			}

			unset( $plugins[ $plugin_file ]['slug'] );
			unset( $plugins[ $plugin_file ]['update'] );
			$plugins[ $plugin_file ]['update-supported'] = false;

			return $plugins;
		}


		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( plugin_basename( dirname( __DIR__ ) . '/toc.php' ) !== $plugin_file ) {
				return $plugin_meta;
			}

			foreach ( $plugin_meta as $index => $meta_link ) {
				if (
					false !== strpos( $meta_link, 'plugin-install.php?tab=plugin-information' ) ||
					false !== strpos( $meta_link, 'wordpress.org/plugins/' )
				) {
					unset( $plugin_meta[ $index ] );
				}
			}

			return array_values( $plugin_meta );
		}


		public function plugins_api( $result, $action, $args ) {
			if ( 'plugin_information' !== $action || ! is_object( $args ) || empty( $args->slug ) ) {
				return $result;
			}

			if ( 'table-of-contents-plus' !== $args->slug ) {
				return $result;
			}

			return new WP_Error( 'plugins_api_failed', __( 'Plugin details are not available for this fork.', 'table-of-contents-plus' ) );
		}


		public function site_transient_update_plugins( $transient ) {
			if ( ! is_object( $transient ) ) {
				return $transient;
			}

			$plugin_file = plugin_basename( dirname( __DIR__ ) . '/toc.php' );

			if ( isset( $transient->response[ $plugin_file ] ) ) {
				unset( $transient->response[ $plugin_file ] );
			}

			if ( isset( $transient->no_update[ $plugin_file ] ) ) {
				unset( $transient->no_update[ $plugin_file ] );
			}

			return $transient;
		}


		public function admin_footer_plugins() {
			$plugin_slug = 'table-of-contents-plus';
			?>
<script>
jQuery(function($) {
	const pluginSlug = <?php echo wp_json_encode( $plugin_slug ); ?>;
	$('a.thickbox[href*="plugin-install.php?tab=plugin-information"]').filter(function() {
		return $(this).attr('href').indexOf('plugin=' + pluginSlug) !== -1;
	}).remove();
});
</script>
			<?php
		}


		public function shortcode_toc( $attributes ) {
			$atts = shortcode_atts(
				[
					'label'          => $this->options['heading_text'],
					'label_show'     => $this->options['visibility_show'],
					'label_hide'     => $this->options['visibility_hide'],
					'no_label'       => false,
					'class'          => false,
					'wrapping'       => $this->options['wrapping'],
					'heading_levels' => $this->options['heading_levels'],
					'exclude'        => $this->options['exclude'],
					'exclude_selectors' => $this->options['exclude_selectors'],
					'collapse'       => false,
					'no_numbers'     => false,
					'start'          => $this->options['start'],
				],
				$attributes
			);

			$re_enqueue_scripts = false;

			if ( $atts['no_label'] ) {
				$this->options['show_heading_text'] = false;
			}
			if ( $atts['label'] ) {
				$this->options['heading_text'] = wp_kses_post( html_entity_decode( $atts['label'] ) );
			}
			if ( $atts['label_show'] ) {
				$this->options['visibility_show'] = wp_kses_post( $atts['label_show'] );
				$re_enqueue_scripts               = true;
			}
			if ( $atts['label_hide'] ) {
				$this->options['visibility_hide'] = wp_kses_post( $atts['label_hide'] );
				$re_enqueue_scripts               = true;
			}
			if ( $atts['class'] ) {
				$this->options['css_container_class'] = wp_kses_post( html_entity_decode( $atts['class'] ) );
			}
			if ( $atts['wrapping'] ) {
				switch ( strtolower( trim( $atts['wrapping'] ) ) ) {
					case 'left':
						$this->options['wrapping'] = TOC_WRAPPING_LEFT;
						break;

					case 'right':
						$this->options['wrapping'] = TOC_WRAPPING_RIGHT;
						break;

					default:
						// do nothing
				}
			}

			if ( $atts['exclude'] ) {
				$this->options['exclude'] = $atts['exclude'];
			}
			if ( $atts['exclude_selectors'] ) {
				$this->options['exclude_selectors'] = $atts['exclude_selectors'];
			}
			if ( $atts['collapse'] ) {
				$this->options['visibility_hide_by_default'] = true;
				$re_enqueue_scripts                          = true;
			}

			if ( $atts['no_numbers'] ) {
				$this->options['ordered_list'] = false;
			}

			if ( is_numeric( $atts['start'] ) ) {
				$this->options['start'] = $atts['start'];
			}

			if ( $re_enqueue_scripts ) {
				wp_deregister_script( 'toc-front' );
				do_action( 'wp_enqueue_scripts' );
			}

			// if $atts['heading_levels'] is an array, then it came from the global options
			// and wasn't provided by per instance
			if ( $atts['heading_levels'] && ! is_array( $atts['heading_levels'] ) ) {
				// make sure they are numbers between 1 and 6 and put into
				// the $clean_heading_levels array if not already
				$clean_heading_levels = [];
				foreach ( explode( ',', $atts['heading_levels'] ) as $heading_level ) {
					if ( is_numeric( $heading_level ) ) {
						$heading_level = (int) $heading_level;
						if ( 1 <= $heading_level && $heading_level <= 6 ) {
							if ( ! in_array( $heading_level, $clean_heading_levels, true ) ) {
								$clean_heading_levels[] = (int) $heading_level;
							}
						}
					}
				}

				if ( count( $clean_heading_levels ) > 0 ) {
					$this->options['heading_levels'] = $clean_heading_levels;
				}
			}

			if ( ! is_search() && ! is_archive() && ! is_feed() ) {
				return '<!--TOC-->';
			} else {
				return '';
			}
		}


		public function shortcode_no_toc( $atts ) {
			$this->show_toc = false;

			return '';
		}


		/**
		 * Register and load CSS and javascript files for frontend.
		 */
		public function wp_enqueue_scripts() {
			$js_vars = [];

			wp_register_style( 'toc-screen', TOC_PLUGIN_PATH . '/screen.css', [], TOC_VERSION );
			wp_register_script( 'toc-front', TOC_PLUGIN_PATH . '/front.js', [ 'jquery' ], TOC_VERSION, true );

			// enqueue them!
			if ( ! $this->options['exclude_css'] ) {
				wp_enqueue_style( 'toc-screen' );

				// add any admin GUI customisations
				$custom_css = $this->get_custom_css();
				if ( $custom_css ) {
					wp_add_inline_style( 'toc-screen', $custom_css );
				}
			}

			if ( $this->options['smooth_scroll'] ) {
				$js_vars['smooth_scroll'] = true;
			}
			wp_enqueue_script( 'toc-front' );
			if ( $this->options['show_heading_text'] && $this->options['visibility'] ) {
				$width                      = ( 'User defined' !== $this->options['width'] ) ? $this->options['width'] : $this->options['width_custom'] . $this->options['width_custom_units'];
				$js_vars['visibility_show'] = esc_js( wp_kses_post( $this->options['visibility_show'] ) );
				$js_vars['visibility_hide'] = esc_js( wp_kses_post( $this->options['visibility_hide'] ) );
				if ( $this->options['visibility_hide_by_default'] ) {
					$js_vars['visibility_hide_by_default'] = true;
				}
				$js_vars['width'] = esc_js( $width );
			}
			if ( TOC_SMOOTH_SCROLL_OFFSET !== $this->options['smooth_scroll_offset'] ) {
				$js_vars['smooth_scroll_offset'] = esc_js( $this->options['smooth_scroll_offset'] );
			}
			if ( $this->defaults['display_top_offset'] !== $this->options['display_top_offset'] ) {
				$js_vars['display_top_offset'] = esc_js( $this->options['display_top_offset'] );
			}
			if ( count( $js_vars ) > 0 ) {
				wp_localize_script(
					'toc-front',
					'tocplus',
					$js_vars
				);
			}
		}


		public function plugins_loaded() {
			load_plugin_textdomain( 'table-of-contents-plus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}


		public function admin_init() {
			wp_register_script( 'toc_admin_script', TOC_PLUGIN_PATH . '/admin.js', [], TOC_VERSION, true );
			wp_register_style( 'toc_admin_style', TOC_PLUGIN_PATH . '/admin.css', [], TOC_VERSION );
		}


		public function admin_menu() {
			$page = add_submenu_page(
				'options-general.php',
				__( 'WPTOC+', 'table-of-contents-plus' ),
				__( 'WPTOC+', 'table-of-contents-plus' ),
				'manage_options',
				'toc',
				[ $this, 'admin_options' ]
			);

			add_action( 'admin_print_styles-' . $page, [ $this, 'admin_options_head' ] );
		}


		public function widgets_init() {
			register_widget( 'toc_widget' );
		}


		/**
		 * Remove widget options on widget deletion
		 */
		public function sidebar_admin_setup( $widget_id, $sidebar_id, $id_base ) {
			// If we aren't trying to delete a TOC widget, return early.
			if ( 'toc-widget' !== $id_base ) {
				return;
			}

			// this action is loaded at the start of the widget screen
			// so only do the following when a form action has been initiated
			$this->set_show_toc_in_widget_only( false );
			$this->set_show_toc_in_widget_only_post_types( [ 'page' ] );
		}


		public function init() {
			// Add compatibility with Rank Math SEO
			if ( class_exists( 'RankMath' ) ) {
				add_filter(
					'rank_math/researches/toc_plugins',
					function ( $toc_plugins ) {
						$toc_plugins[ plugin_basename( dirname( __DIR__ ) . '/toc.php' ) ] = 'WPTOC+';
						return $toc_plugins;
					}
				);
			}
		}


		/**
		 * Load needed scripts and styles only on the toc administration interface.
		 */
		public function admin_options_head() {
			wp_enqueue_style( 'farbtastic' );
			wp_enqueue_script( 'farbtastic' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'toc_admin_script' );
			wp_enqueue_style( 'toc_admin_style' );
		}


		/**
		 * Tries to convert $input into a valid hex colour.
		 * Returns $default if $input is not a hex value, otherwise returns verified hex.
		 */
		private function hex_value( $input = '', $default = '#' ) {
			$return = $default;

			if ( $input ) {
				// strip out non hex chars
				$return = preg_replace( '/[^a-fA-F0-9]*/', '', $input );

				switch ( strlen( $return ) ) {
					case 3:  // do next
					case 6:
						$return = '#' . $return;
						break;

					default:
						if ( strlen( $return ) > 6 ) {
							$return = '#' . substr( $return, 0, 6 );  // if > 6 chars, then take the first 6
						} elseif ( strlen( $return ) > 3 && strlen( $return ) < 6 ) {
							$return = '#' . substr( $return, 0, 3 );  // if between 3 and 6, then take first 3
						} else {
							$return = $default;  // not valid, return $default
						}
				}
			}

			return $return;
		}


		private function save_admin_options() {
			global $post_id;

			// security check
			if ( ! isset( $_POST['toc-admin-options'] ) ) {
				return false;
			}
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['toc-admin-options'] ) ), plugin_basename( __FILE__ ) ) ) {
				return false;
			}

			// require an administrator level to save
			if ( ! current_user_can( 'manage_options', $post_id ) ) {
				return false;
			}

			// use stripslashes on free text fields that can have ' " \
			// WordPress automatically slashes these characters as part of
			// wp-includes/load.php::wp_magic_quotes()

			$custom_background_colour    = ! empty( $_POST['custom_background_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_background_colour'] ) ), TOC_DEFAULT_BACKGROUND_COLOUR ) : TOC_DEFAULT_BACKGROUND_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$custom_border_colour        = ! empty( $_POST['custom_border_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_border_colour'] ) ), TOC_DEFAULT_BORDER_COLOUR ) : TOC_DEFAULT_BORDER_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$custom_title_colour         = ! empty( $_POST['custom_title_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_title_colour'] ) ), TOC_DEFAULT_TITLE_COLOUR ) : TOC_DEFAULT_TITLE_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$custom_links_colour         = ! empty( $_POST['custom_links_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_links_colour'] ) ), TOC_DEFAULT_LINKS_COLOUR ) : TOC_DEFAULT_LINKS_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$custom_links_hover_colour   = ! empty( $_POST['custom_links_hover_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_links_hover_colour'] ) ), TOC_DEFAULT_LINKS_HOVER_COLOUR ) : TOC_DEFAULT_LINKS_HOVER_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$custom_links_visited_colour = ! empty( $_POST['custom_links_visited_colour'] ) ? $this->hex_value( trim( wp_unslash( $_POST['custom_links_visited_colour'] ) ), TOC_DEFAULT_LINKS_VISITED_COLOUR ) : TOC_DEFAULT_LINKS_VISITED_COLOUR; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$position                      = isset( $_POST['position'] ) ? intval( $_POST['position'] ) : $this->defaults['position'];
			$start                         = isset( $_POST['start'] ) ? intval( $_POST['start'] ) : $this->defaults['start'];
			$show_heading_text             = isset( $_POST['show_heading_text'] ) && (bool) $_POST['show_heading_text'];
			$heading_text                  = isset( $_POST['heading_text'] ) ? stripslashes( trim( sanitize_text_field( wp_unslash( $_POST['heading_text'] ) ) ) ) : $this->defaults['heading_text'];
			$auto_insert_post_types        = isset( $_POST['auto_insert_post_types'] ) ? array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) $_POST['auto_insert_post_types'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$show_heirarchy                = isset( $_POST['show_heirarchy'] ) && (bool) $_POST['show_heirarchy'];
			$collapsible_subsections       = isset( $_POST['collapsible_subsections'] ) && (bool) $_POST['collapsible_subsections'];
			$collapse_subsections_by_default = isset( $_POST['collapse_subsections_by_default'] ) && (bool) $_POST['collapse_subsections_by_default'];
			$ordered_list                  = isset( $_POST['ordered_list'] ) && (bool) $_POST['ordered_list'];
			$smooth_scroll                 = isset( $_POST['smooth_scroll'] ) && (bool) $_POST['smooth_scroll'];
			$smooth_scroll_offset          = isset( $_POST['smooth_scroll_offset'] ) ? intval( $_POST['smooth_scroll_offset'] ) : $this->defaults['smooth_scroll_offset'];
			$visibility                    = isset( $_POST['visibility'] ) && (bool) $_POST['visibility'];
			$visibility_show               = isset( $_POST['visibility_show'] ) ? stripslashes( trim( sanitize_text_field( wp_unslash( $_POST['visibility_show'] ) ) ) ) : $this->defaults['visibility_show'];
			$visibility_hide               = isset( $_POST['visibility_hide'] ) ? stripslashes( trim( sanitize_text_field( wp_unslash( $_POST['visibility_hide'] ) ) ) ) : $this->defaults['visibility_hide'];
			$visibility_hide_by_default    = isset( $_POST['visibility_hide_by_default'] ) && (bool) $_POST['visibility_hide_by_default'];
			$display_mode                  = isset( $_POST['display_mode'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['display_mode'] ) ) ) : $this->defaults['display_mode'];
			$mobile_mode                   = isset( $_POST['mobile_mode'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['mobile_mode'] ) ) ) : $this->defaults['mobile_mode'];
			$floating_side                 = isset( $_POST['floating_side'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['floating_side'] ) ) ) : $this->defaults['floating_side'];
			$display_top_offset            = isset( $_POST['display_top_offset'] ) ? max( 0, intval( $_POST['display_top_offset'] ) ) : $this->defaults['display_top_offset'];
			$exclude_selectors             = isset( $_POST['exclude_selectors'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['exclude_selectors'] ) ) ) : $this->defaults['exclude_selectors'];
			$width                         = isset( $_POST['width'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['width'] ) ) ) : $this->defaults['width'];
			$width_custom                  = isset( $_POST['width_custom'] ) ? floatval( $_POST['width_custom'] ) : $this->defaults['width_custom'];
			$width_custom_units            = isset( $_POST['width_custom_units'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['width_custom_units'] ) ) ) : $this->defaults['width_custom_units'];
			$wrapping                      = isset( $_POST['wrapping'] ) ? intval( $_POST['wrapping'] ) : $this->defaults['wrapping'];
			$font_size                     = isset( $_POST['font_size'] ) ? floatval( $_POST['font_size'] ) : $this->defaults['font_size'];
			$font_size_units               = isset( $_POST['font_size_units'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['font_size_units'] ) ) ) : $this->defaults['font_size_units'];
			$design_preset                 = isset( $_POST['design_preset'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['design_preset'] ) ) ) : $this->defaults['design_preset'];
			$theme                         = isset( $_POST['theme'] ) ? intval( $_POST['theme'] ) : $this->defaults['theme'];
			$lowercase                     = isset( $_POST['lowercase'] ) && (bool) $_POST['lowercase'];
			$hyphenate                     = isset( $_POST['hyphenate'] ) && (bool) $_POST['hyphenate'];
			$exclude_css                   = isset( $_POST['exclude_css'] ) && (bool) $_POST['exclude_css'];
			$heading_levels                = isset( $_POST['heading_levels'] ) ? array_map( 'intval', (array) $_POST['heading_levels'] ) : [];
			$exclude                       = isset( $_POST['exclude'] ) ? stripslashes( trim( sanitize_text_field( wp_unslash( $_POST['exclude'] ) ) ) ) : $this->defaults['exclude'];

			if ( ! in_array( $display_mode, [ 'inline', 'floating', 'sticky', 'sticky-column' ], true ) ) {
				$display_mode = $this->defaults['display_mode'];
			}

			if ( ! in_array( $mobile_mode, [ 'inline', 'compact' ], true ) ) {
				$mobile_mode = $this->defaults['mobile_mode'];
			}

			if ( ! in_array( $floating_side, [ 'left', 'right' ], true ) ) {
				$floating_side = $this->defaults['floating_side'];
			}

			if ( ! in_array( $design_preset, $this->get_allowed_design_presets(), true ) ) {
				$design_preset = $this->defaults['design_preset'];
			}

			$this->options = array_merge(
				$this->options,
				[
					'position'                      => $position,
					'start'                         => $start,
					'show_heading_text'             => $show_heading_text,
					'heading_text'                  => $heading_text,
					'auto_insert_post_types'        => $auto_insert_post_types,
					'show_heirarchy'                => $show_heirarchy,
					'collapsible_subsections'       => $collapsible_subsections,
					'collapse_subsections_by_default' => $collapse_subsections_by_default,
					'ordered_list'                  => $ordered_list,
					'smooth_scroll'                 => $smooth_scroll,
					'smooth_scroll_offset'          => $smooth_scroll_offset,
					'visibility'                    => $visibility,
					'visibility_show'               => $visibility_show,
					'visibility_hide'               => $visibility_hide,
					'visibility_hide_by_default'    => $visibility_hide_by_default,
					'display_mode'                  => $display_mode,
					'mobile_mode'                   => $mobile_mode,
					'floating_side'                 => $floating_side,
					'display_top_offset'            => $display_top_offset,
					'exclude_selectors'             => $exclude_selectors,
					'width'                         => $width,
					'width_custom'                  => $width_custom,
					'width_custom_units'            => $width_custom_units,
					'wrapping'                      => $wrapping,
					'font_size'                     => $font_size,
					'font_size_units'               => $font_size_units,
					'design_preset'                 => $design_preset,
					'theme'                         => $theme,
					'custom_background_colour'      => $custom_background_colour,
					'custom_border_colour'          => $custom_border_colour,
					'custom_title_colour'           => $custom_title_colour,
					'custom_links_colour'           => $custom_links_colour,
					'custom_links_hover_colour'     => $custom_links_hover_colour,
					'custom_links_visited_colour'   => $custom_links_visited_colour,
					'lowercase'                     => $lowercase,
					'hyphenate'                     => $hyphenate,
					'exclude_css'                   => $exclude_css,
					'heading_levels'                => $heading_levels,
					'exclude'                       => $exclude,
				]
			);

			unset(
				$this->options['fragment_prefix'],
				$this->options['bullet_spacing'],
				$this->options['include_homepage'],
				$this->options['restrict_path'],
				$this->options['rest_toc_output'],
				$this->options['sidebar_injection_selector'],
				$this->options['sidebar_injection_behavior']
			);

			// update_option will return false if no changes were made
			update_option( 'toc-options', $this->options );

			return true;
		}


		private function get_export_settings_payload() {
			$export_options = $this->sanitize_imported_options( $this->options );

			if ( false === $export_options ) {
				$export_options = $this->sanitize_imported_options( $this->defaults );
			}

			$payload = [
				'plugin'      => 'WPTOC+',
				'version'     => TOC_VERSION,
				'exported_at' => current_time( 'mysql' ),
				'options'     => $export_options,
			];

			return wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		}


		private function get_toc_width_value() {
			if ( 'User defined' !== $this->options['width'] ) {
				return $this->options['width'];
			}

			return $this->options['width_custom'] . $this->options['width_custom_units'];
		}


		private function get_allowed_design_presets() {
			return [ 'default', 'minimal', 'editorial', 'docs', 'card' ];
		}


		private function sanitize_imported_options( $imported_options ) {
			if ( ! is_array( $imported_options ) ) {
				return false;
			}

			$raw_options = wp_parse_args( $imported_options, $this->defaults );

			$custom_background_colour    = ! empty( $raw_options['custom_background_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_background_colour'] ), TOC_DEFAULT_BACKGROUND_COLOUR ) : TOC_DEFAULT_BACKGROUND_COLOUR;
			$custom_border_colour        = ! empty( $raw_options['custom_border_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_border_colour'] ), TOC_DEFAULT_BORDER_COLOUR ) : TOC_DEFAULT_BORDER_COLOUR;
			$custom_title_colour         = ! empty( $raw_options['custom_title_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_title_colour'] ), TOC_DEFAULT_TITLE_COLOUR ) : TOC_DEFAULT_TITLE_COLOUR;
			$custom_links_colour         = ! empty( $raw_options['custom_links_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_links_colour'] ), TOC_DEFAULT_LINKS_COLOUR ) : TOC_DEFAULT_LINKS_COLOUR;
			$custom_links_hover_colour   = ! empty( $raw_options['custom_links_hover_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_links_hover_colour'] ), TOC_DEFAULT_LINKS_HOVER_COLOUR ) : TOC_DEFAULT_LINKS_HOVER_COLOUR;
			$custom_links_visited_colour = ! empty( $raw_options['custom_links_visited_colour'] ) ? $this->hex_value( trim( (string) $raw_options['custom_links_visited_colour'] ), TOC_DEFAULT_LINKS_VISITED_COLOUR ) : TOC_DEFAULT_LINKS_VISITED_COLOUR;

			$position                   = isset( $raw_options['position'] ) ? intval( $raw_options['position'] ) : $this->defaults['position'];
			$start                      = isset( $raw_options['start'] ) ? intval( $raw_options['start'] ) : $this->defaults['start'];
			$show_heading_text          = ! empty( $raw_options['show_heading_text'] );
			$heading_text               = isset( $raw_options['heading_text'] ) ? trim( sanitize_text_field( (string) $raw_options['heading_text'] ) ) : $this->defaults['heading_text'];
			$auto_insert_post_types     = isset( $raw_options['auto_insert_post_types'] ) ? array_map( 'sanitize_text_field', (array) $raw_options['auto_insert_post_types'] ) : $this->defaults['auto_insert_post_types'];
			$show_heirarchy             = ! empty( $raw_options['show_heirarchy'] );
			$collapsible_subsections    = ! empty( $raw_options['collapsible_subsections'] );
			$collapse_subsections_by_default = ! empty( $raw_options['collapse_subsections_by_default'] );
			$ordered_list               = ! empty( $raw_options['ordered_list'] );
			$smooth_scroll              = ! empty( $raw_options['smooth_scroll'] );
			$smooth_scroll_offset       = isset( $raw_options['smooth_scroll_offset'] ) ? intval( $raw_options['smooth_scroll_offset'] ) : $this->defaults['smooth_scroll_offset'];
			$visibility                 = ! empty( $raw_options['visibility'] );
			$visibility_show            = isset( $raw_options['visibility_show'] ) ? trim( sanitize_text_field( (string) $raw_options['visibility_show'] ) ) : $this->defaults['visibility_show'];
			$visibility_hide            = isset( $raw_options['visibility_hide'] ) ? trim( sanitize_text_field( (string) $raw_options['visibility_hide'] ) ) : $this->defaults['visibility_hide'];
			$visibility_hide_by_default = ! empty( $raw_options['visibility_hide_by_default'] );
			$display_mode               = isset( $raw_options['display_mode'] ) ? trim( sanitize_text_field( (string) $raw_options['display_mode'] ) ) : $this->defaults['display_mode'];
			$mobile_mode                = isset( $raw_options['mobile_mode'] ) ? trim( sanitize_text_field( (string) $raw_options['mobile_mode'] ) ) : $this->defaults['mobile_mode'];
			$floating_side              = isset( $raw_options['floating_side'] ) ? trim( sanitize_text_field( (string) $raw_options['floating_side'] ) ) : $this->defaults['floating_side'];
			$display_top_offset         = isset( $raw_options['display_top_offset'] ) ? max( 0, intval( $raw_options['display_top_offset'] ) ) : $this->defaults['display_top_offset'];
			$exclude_selectors          = isset( $raw_options['exclude_selectors'] ) ? trim( sanitize_text_field( (string) $raw_options['exclude_selectors'] ) ) : $this->defaults['exclude_selectors'];
			$width                      = isset( $raw_options['width'] ) ? trim( sanitize_text_field( (string) $raw_options['width'] ) ) : $this->defaults['width'];
			$width_custom               = isset( $raw_options['width_custom'] ) ? floatval( $raw_options['width_custom'] ) : $this->defaults['width_custom'];
			$width_custom_units         = isset( $raw_options['width_custom_units'] ) ? trim( sanitize_text_field( (string) $raw_options['width_custom_units'] ) ) : $this->defaults['width_custom_units'];
			$wrapping                   = isset( $raw_options['wrapping'] ) ? intval( $raw_options['wrapping'] ) : $this->defaults['wrapping'];
			$font_size                  = isset( $raw_options['font_size'] ) ? floatval( $raw_options['font_size'] ) : $this->defaults['font_size'];
			$font_size_units            = isset( $raw_options['font_size_units'] ) ? trim( sanitize_text_field( (string) $raw_options['font_size_units'] ) ) : $this->defaults['font_size_units'];
			$design_preset              = isset( $raw_options['design_preset'] ) ? trim( sanitize_text_field( (string) $raw_options['design_preset'] ) ) : $this->defaults['design_preset'];
			$theme                      = isset( $raw_options['theme'] ) ? intval( $raw_options['theme'] ) : $this->defaults['theme'];
			$lowercase                  = ! empty( $raw_options['lowercase'] );
			$hyphenate                  = ! empty( $raw_options['hyphenate'] );
			$exclude_css                = ! empty( $raw_options['exclude_css'] );
			$heading_levels             = isset( $raw_options['heading_levels'] ) ? array_map( 'intval', (array) $raw_options['heading_levels'] ) : $this->defaults['heading_levels'];
			$exclude                    = isset( $raw_options['exclude'] ) ? trim( sanitize_text_field( (string) $raw_options['exclude'] ) ) : $this->defaults['exclude'];
			$css_container_class        = isset( $raw_options['css_container_class'] ) ? trim( sanitize_text_field( (string) $raw_options['css_container_class'] ) ) : $this->defaults['css_container_class'];
			$show_toc_in_widget_only    = ! empty( $raw_options['show_toc_in_widget_only'] );
			$show_toc_widget_post_types = isset( $raw_options['show_toc_in_widget_only_post_types'] ) ? array_map( 'sanitize_text_field', (array) $raw_options['show_toc_in_widget_only_post_types'] ) : $this->defaults['show_toc_in_widget_only_post_types'];

			if ( ! in_array( $display_mode, [ 'inline', 'floating', 'sticky', 'sticky-column' ], true ) ) {
				$display_mode = $this->defaults['display_mode'];
			}

			if ( ! in_array( $mobile_mode, [ 'inline', 'compact' ], true ) ) {
				$mobile_mode = $this->defaults['mobile_mode'];
			}

			if ( ! in_array( $floating_side, [ 'left', 'right' ], true ) ) {
				$floating_side = $this->defaults['floating_side'];
			}

			if ( ! in_array( $design_preset, $this->get_allowed_design_presets(), true ) ) {
				$design_preset = $this->defaults['design_preset'];
			}

			return [
				'position'                      => $position,
				'start'                         => $start,
				'show_heading_text'             => $show_heading_text,
				'heading_text'                  => $heading_text,
				'auto_insert_post_types'        => $auto_insert_post_types,
				'show_heirarchy'                => $show_heirarchy,
				'collapsible_subsections'       => $collapsible_subsections,
				'collapse_subsections_by_default' => $collapse_subsections_by_default,
				'ordered_list'                  => $ordered_list,
				'smooth_scroll'                 => $smooth_scroll,
				'smooth_scroll_offset'          => $smooth_scroll_offset,
				'visibility'                    => $visibility,
				'visibility_show'               => $visibility_show,
				'visibility_hide'               => $visibility_hide,
				'visibility_hide_by_default'    => $visibility_hide_by_default,
				'display_mode'                  => $display_mode,
				'mobile_mode'                   => $mobile_mode,
				'floating_side'                 => $floating_side,
				'display_top_offset'            => $display_top_offset,
				'exclude_selectors'             => $exclude_selectors,
				'width'                         => $width,
				'width_custom'                  => $width_custom,
				'width_custom_units'            => $width_custom_units,
				'wrapping'                      => $wrapping,
				'font_size'                     => $font_size,
				'font_size_units'               => $font_size_units,
				'design_preset'                 => $design_preset,
				'theme'                         => $theme,
				'custom_background_colour'      => $custom_background_colour,
				'custom_border_colour'          => $custom_border_colour,
				'custom_title_colour'           => $custom_title_colour,
				'custom_links_colour'           => $custom_links_colour,
				'custom_links_hover_colour'     => $custom_links_hover_colour,
				'custom_links_visited_colour'   => $custom_links_visited_colour,
				'lowercase'                     => $lowercase,
				'hyphenate'                     => $hyphenate,
				'exclude_css'                   => $exclude_css,
				'exclude'                       => $exclude,
				'heading_levels'                => $heading_levels,
				'css_container_class'           => $css_container_class,
				'show_toc_in_widget_only'       => $show_toc_in_widget_only,
				'show_toc_in_widget_only_post_types' => $show_toc_widget_post_types,
			];
		}


		private function import_admin_options() {
			global $post_id;

			if ( ! isset( $_POST['toc-admin-options'] ) ) {
				return new WP_Error( 'missing_nonce', __( 'Import failed security check.', 'table-of-contents-plus' ) );
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['toc-admin-options'] ) ), plugin_basename( __FILE__ ) ) ) {
				return new WP_Error( 'invalid_nonce', __( 'Import failed security check.', 'table-of-contents-plus' ) );
			}

			if ( ! current_user_can( 'manage_options', $post_id ) ) {
				return new WP_Error( 'invalid_permissions', __( 'You do not have permission to import settings.', 'table-of-contents-plus' ) );
			}

			$import_blob = isset( $_POST['settings_import_blob'] ) ? trim( wp_unslash( $_POST['settings_import_blob'] ) ) : '';

			if ( '' === $import_blob ) {
				return new WP_Error( 'empty_import', __( 'Paste a WPTOC+ settings export before importing.', 'table-of-contents-plus' ) );
			}

			$decoded = json_decode( $import_blob, true );

			if ( ! is_array( $decoded ) ) {
				return new WP_Error( 'invalid_json', __( 'The import data is not valid JSON.', 'table-of-contents-plus' ) );
			}

			$imported_options = isset( $decoded['options'] ) && is_array( $decoded['options'] ) ? $decoded['options'] : $decoded;
			$sanitized        = $this->sanitize_imported_options( $imported_options );

			if ( false === $sanitized ) {
				return new WP_Error( 'invalid_import', __( 'The import data does not contain a valid WPTOC+ settings bundle.', 'table-of-contents-plus' ) );
			}

			$this->options = array_merge( $this->defaults, $sanitized );

			unset(
				$this->options['fragment_prefix'],
				$this->options['bullet_spacing'],
				$this->options['include_homepage'],
				$this->options['restrict_path'],
				$this->options['rest_toc_output']
			);

			update_option( 'toc-options', $this->options );

			return true;
		}


		private function restore_default_admin_options() {
			global $post_id;

			if ( ! isset( $_POST['toc-admin-options'] ) ) {
				return new WP_Error( 'missing_nonce', __( 'Default restore failed security check.', 'table-of-contents-plus' ) );
			}

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['toc-admin-options'] ) ), plugin_basename( __FILE__ ) ) ) {
				return new WP_Error( 'invalid_nonce', __( 'Default restore failed security check.', 'table-of-contents-plus' ) );
			}

			if ( ! current_user_can( 'manage_options', $post_id ) ) {
				return new WP_Error( 'invalid_permissions', __( 'You do not have permission to restore default settings.', 'table-of-contents-plus' ) );
			}

			$this->options = $this->defaults;

			unset(
				$this->options['fragment_prefix'],
				$this->options['bullet_spacing'],
				$this->options['include_homepage'],
				$this->options['restrict_path'],
				$this->options['rest_toc_output'],
				$this->options['sidebar_injection_selector'],
				$this->options['sidebar_injection_behavior']
			);

			update_option( 'toc-options', $this->options );

			return true;
		}


		public function admin_options() {
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$msg  = '';
			$active_tab = isset( $_POST['wptoc_active_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['wptoc_active_tab'] ) ) : 'tab1';
			$export_settings_payload = $this->get_export_settings_payload();

			// was there a form submission, if so, do security checks and try to save form
			if ( isset( $_POST['import_settings'] ) ) {
				$active_tab = 'tab4';
				$result = $this->import_admin_options();

				if ( is_wp_error( $result ) ) {
					$msg = '<div id="message" class="wptoc-admin-alert is-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
				} else {
					$export_settings_payload = $this->get_export_settings_payload();
					$msg = '<div id="message" class="wptoc-admin-alert is-success"><p>' . __( 'Settings imported.', 'table-of-contents-plus' ) . '</p></div>';
				}
			} elseif ( isset( $_POST['restore_default_settings'] ) ) {
				$active_tab = 'tab4';
				$result = $this->restore_default_admin_options();

				if ( is_wp_error( $result ) ) {
					$msg = '<div id="message" class="wptoc-admin-alert is-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
				} else {
					$export_settings_payload = $this->get_export_settings_payload();
					$msg = '<div id="message" class="wptoc-admin-alert is-success"><p>' . __( 'Default settings restored.', 'table-of-contents-plus' ) . '</p></div>';
				}
			} elseif ( isset( $_GET['update'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $this->save_admin_options() ) {
					$msg = '<div id="message" class="wptoc-admin-alert is-success"><p>' . __( 'Options saved.', 'table-of-contents-plus' ) . '</p></div>';
				} else {
					$msg = '<div id="message" class="wptoc-admin-alert is-error"><p>' . __( 'Save failed.', 'table-of-contents-plus' ) . '</p></div>';
				}
			}

			?>
<div id='toc' class='wrap wptoc-admin-page'>
	<div class="wptoc-admin-hero">
		<h1>WPTOC+</h1>
		<p><?php esc_html_e( 'Configure how the table of contents is inserted, displayed, and styled across your site.', 'table-of-contents-plus' ); ?></p>
	</div>
			<?php echo wp_kses_post( $msg ); ?>
<form class="wptoc-admin-form" method="post" action="<?php echo esc_url( '?page=' . $page . '&update' ); ?>">
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'toc-admin-options' ); ?>
			<input type="hidden" name="wptoc_active_tab" id="wptoc_active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />
	<div class="wptoc-admin-save-overlay" aria-hidden="true">
		<div class="wptoc-admin-save-overlay__dialog" role="status" aria-live="polite">
			<span class="wptoc-admin-save-spinner"></span>
			<span class="wptoc-admin-save-overlay__title" data-default-title="<?php esc_attr_e( 'Saving changes', 'table-of-contents-plus' ); ?>"><?php esc_html_e( 'Saving changes', 'table-of-contents-plus' ); ?></span>
			<span class="wptoc-admin-save-overlay__text" data-default-text="<?php esc_attr_e( 'Please wait while WPTOC+ updates your settings.', 'table-of-contents-plus' ); ?>"><?php esc_html_e( 'Please wait while WPTOC+ updates your settings.', 'table-of-contents-plus' ); ?></span>
		</div>
	</div>

<ul id="tabbed-nav">
	<li><a href="#tab1"><?php esc_html_e( 'Options', 'table-of-contents-plus' ); ?></a></li>
	<li><a href="#tab2"><?php esc_html_e( 'Advanced Options', 'table-of-contents-plus' ); ?></a></li>
	<li><a href="#tab3"><?php esc_html_e( 'Appearance', 'table-of-contents-plus' ); ?></a></li>
	<li><a href="#tab4"><?php esc_html_e( 'Import / Export', 'table-of-contents-plus' ); ?></a></li>

	<?php
	// XTEC ************ AFEGIT - Change default settings
	// 2017.05.04 @xaviernietosanchez

	if ( is_xtec_super_admin() ) {

	// ************ FI
	?>

	<li class="url"><a href="https://github.com/psydox/WPTOC-Plus" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help', 'table-of-contents-plus' ); ?></a></li>

	<?php
	// XTEC ************ AFEGIT - Change default settings
	// 2017.05.04 @xaviernietosanchez

	}

	// ************ FI
	?>
</ul>
<div class="tab_container">
	<div id="tab1" class="tab_content">

<table class="form-table">
<tbody>
<tr>
	<th><label for="position"><?php esc_html_e( 'Position', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<select name="position" id="position">
			<option value="<?php echo esc_attr( TOC_POSITION_BEFORE_FIRST_HEADING ); ?>"<?php if ( TOC_POSITION_BEFORE_FIRST_HEADING === $this->options['position'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Before first heading (default)', 'table-of-contents-plus' ); ?></option>
			<option value="<?php echo esc_attr( TOC_POSITION_AFTER_FIRST_HEADING ); ?>"<?php if ( TOC_POSITION_AFTER_FIRST_HEADING === $this->options['position'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'After first heading', 'table-of-contents-plus' ); ?></option>
			<option value="<?php echo esc_attr( TOC_POSITION_TOP ); ?>"<?php if ( TOC_POSITION_TOP === $this->options['position'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Top', 'table-of-contents-plus' ); ?></option>
			<option value="<?php echo esc_attr( TOC_POSITION_BOTTOM ); ?>"<?php if ( TOC_POSITION_BOTTOM === $this->options['position'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Bottom', 'table-of-contents-plus' ); ?></option>
		</select>
	</td>
</tr>
<tr>
	<th><label for="start"><?php esc_html_e( 'Show when', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<select name="start" id="start">
			<?php
			for ( $i = TOC_MIN_START; $i <= TOC_MAX_START; $i++ ) {
				echo '<option value="' . esc_attr( $i ) . '"';
				if ( $i === $this->options['start'] ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( $i ) . '</option>' . "\n";
			}
			?>
		</select>
			<?php
			/* translators: text follows drop down list of numbers */
			esc_html_e( 'or more headings are present', 'table-of-contents-plus' );
			?>
	</td>
</tr>
<tr>
	<th><?php esc_html_e( 'Auto insert for the following content types', 'table-of-contents-plus' ); ?></th>
	<td><?php
	foreach ( get_post_types() as $post_type ) {

		// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
		// 2017.05.11 @xaviernietosanchez

		if ( $post_type == 'post' || $post_type == 'page' || is_xtec_super_admin() ) {

		// ************ FI

		// make sure the post type isn't on the exclusion list
		if ( ! in_array( $post_type, $this->exclude_post_types, true ) ) {
			echo '<input type="checkbox" value="' . esc_attr( $post_type ) . '" id="auto_insert_post_types_' . esc_attr( $post_type ) . '" name="auto_insert_post_types[]"';
			if ( in_array( $post_type, $this->options['auto_insert_post_types'], true ) ) echo ' checked="checked"';
			echo ' /><label for="auto_insert_post_types_' . esc_attr( $post_type ) . '"> ' . esc_html( $post_type ) . '</label><br>';
		}
		// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
		// 2017.05.11 @xaviernietosanchez

		}

		// ************ FI
	}
	?>
	</td>
</tr>
<tr>
	<th><label for="show_heading_text"><?php
	/* translators: this is the title of the table of contents */
	esc_html_e( 'Heading text', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<input type="checkbox" value="1" id="show_heading_text" name="show_heading_text"<?php if ( $this->options['show_heading_text'] ) echo ' checked="checked"'; ?> /><label for="show_heading_text"> <?php esc_html_e( 'Show title on top of the table of contents', 'table-of-contents-plus' ); ?></label><br>
		<div class="more_toc_options<?php if ( ! $this->options['show_heading_text'] ) echo ' disabled'; ?>">
			<input type="text" class="regular-text" value="<?php echo esc_attr( $this->options['heading_text'] ); ?>" id="heading_text" name="heading_text">
			<span class="description"><label for="heading_text"><?php esc_html_e( 'Eg: Contents, Table of Contents, Page Contents', 'table-of-contents-plus' ); ?></label></span><br><br>

			<input type="checkbox" value="1" id="visibility" name="visibility"<?php if ( $this->options['visibility'] ) echo ' checked="checked"'; ?> /><label for="visibility"> <?php esc_html_e( 'Allow the user to toggle the visibility of the table of contents', 'table-of-contents-plus' ); ?></label><br>
			<div class="more_toc_options<?php if ( ! $this->options['visibility'] ) echo ' disabled'; ?>">
				<table class="more_toc_options_table">
				<tbody>
				<tr>
					<th><label for="visibility_show"><?php esc_html_e( 'Show text', 'table-of-contents-plus' ); ?></label></th>
					<td><input type="text" class="" value="<?php echo esc_attr( $this->options['visibility_show'] ); ?>" id="visibility_show" name="visibility_show">
					<span class="description"><label for="visibility_show"><?php
					/* translators: example text to display when you want to expand the table of contents */
					esc_html_e( 'Eg: show', 'table-of-contents-plus' ); ?></label></span></td>
				</tr>
				<tr>
					<th><label for="visibility_hide"><?php esc_html_e( 'Hide text', 'table-of-contents-plus' ); ?></label></th>
					<td><input type="text" class="" value="<?php echo esc_attr( $this->options['visibility_hide'] ); ?>" id="visibility_hide" name="visibility_hide">
					<span class="description"><label for="visibility_hide"><?php
					/* translators: example text to display when you want to collapse the table of contents */
					esc_html_e( 'Eg: hide', 'table-of-contents-plus' ); ?></label></span></td>
				</tr>
				</tbody>
				</table>
				<input type="checkbox" value="1" id="visibility_hide_by_default" name="visibility_hide_by_default"<?php if ( $this->options['visibility_hide_by_default'] ) echo ' checked="checked"'; ?> /><label for="visibility_hide_by_default"> <?php esc_html_e( 'Hide the table of contents initially', 'table-of-contents-plus' ); ?></label>
			</div>
		</div>
	</td>
</tr>
<tr>
	<th><label for="display_mode"><?php esc_html_e( 'Display mode', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<select name="display_mode" id="display_mode">
			<option value="inline"<?php if ( 'inline' === $this->options['display_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Standard inline', 'table-of-contents-plus' ); ?></option>
			<option value="floating"<?php if ( 'floating' === $this->options['display_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Floating panel (desktop)', 'table-of-contents-plus' ); ?></option>
			<option value="sticky"<?php if ( 'sticky' === $this->options['display_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Sticky full width (desktop)', 'table-of-contents-plus' ); ?></option>
			<option value="sticky-column"<?php if ( 'sticky-column' === $this->options['display_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Sticky side column (desktop)', 'table-of-contents-plus' ); ?></option>
		</select>
		<br>
		<span class="description"><label for="display_mode"><?php esc_html_e( 'Floating and sticky desktop modes automatically fall back to the standard inline TOC on smaller screens. Sticky side column mode keeps the TOC width but reserves that side column for the rest of the article.', 'table-of-contents-plus' ); ?></label></span>
	</td>
</tr>
<tr>
	<th><label for="mobile_mode"><?php esc_html_e( 'Mobile mode', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<select name="mobile_mode" id="mobile_mode">
			<option value="inline"<?php if ( 'inline' === $this->options['mobile_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Standard inline', 'table-of-contents-plus' ); ?></option>
			<option value="compact"<?php if ( 'compact' === $this->options['mobile_mode'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Compact expandable panel', 'table-of-contents-plus' ); ?></option>
		</select>
		<br>
		<span class="description"><label for="mobile_mode"><?php esc_html_e( 'Adds a mobile-only compact toggle so the TOC stays out of the way on smaller screens.', 'table-of-contents-plus' ); ?></label></span>
	</td>
</tr>
<tr>
	<th><label for="floating_side"><?php esc_html_e( 'Floating side', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<select name="floating_side" id="floating_side">
			<option value="right"<?php if ( 'right' === $this->options['floating_side'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Right side', 'table-of-contents-plus' ); ?></option>
			<option value="left"<?php if ( 'left' === $this->options['floating_side'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Left side', 'table-of-contents-plus' ); ?></option>
		</select>
		<br>
			<span class="description"><label for="floating_side"><?php esc_html_e( 'Applies to Floating panel and Sticky side column display modes.', 'table-of-contents-plus' ); ?></label></span>
	</td>
</tr>
<tr>
	<th><label for="display_top_offset"><?php esc_html_e( 'Display top offset', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<input type="text" class="regular-text" value="<?php echo intval( $this->options['display_top_offset'] ); ?>" id="display_top_offset" name="display_top_offset"> px<br>
		<span class="description"><label for="display_top_offset"><?php esc_html_e( 'Adds top spacing for floating and sticky desktop modes so the TOC clears your theme\'s sticky header.', 'table-of-contents-plus' ); ?></label></span>
	</td>
</tr>
<tr>
	<th><label for="show_heirarchy"><?php esc_html_e( 'Show hierarchy', 'table-of-contents-plus' ); ?></label></th>
	<td><input type="checkbox" value="1" id="show_heirarchy" name="show_heirarchy"<?php if ( $this->options['show_heirarchy'] ) echo ' checked="checked"'; ?> /></td>
</tr>
<tr>
	<th><label for="collapsible_subsections"><?php esc_html_e( 'Collapsible nested sections', 'table-of-contents-plus' ); ?></label></th>
	<td>
		<input type="checkbox" value="1" id="collapsible_subsections" name="collapsible_subsections"<?php if ( $this->options['collapsible_subsections'] ) echo ' checked="checked"'; ?> />
		<label for="collapsible_subsections"> <?php esc_html_e( 'Allow nested TOC branches to collapse and expand', 'table-of-contents-plus' ); ?></label><br>
		<label for="collapse_subsections_by_default">
			<input type="checkbox" value="1" id="collapse_subsections_by_default" name="collapse_subsections_by_default"<?php if ( $this->options['collapse_subsections_by_default'] ) echo ' checked="checked"'; ?> />
			<?php esc_html_e( 'Collapse subsections by default', 'table-of-contents-plus' ); ?>
		</label><br>
		<span class="description"><label for="collapsible_subsections"><?php esc_html_e( 'Only applies when hierarchy is enabled. Active parent sections open automatically, and you can choose whether other branches start collapsed or expanded.', 'table-of-contents-plus' ); ?></label></span>
	</td>
</tr>
<tr>
	<th><label for="ordered_list"><?php esc_html_e( 'Number list items', 'table-of-contents-plus' ); ?></label></th>
	<td><input type="checkbox" value="1" id="ordered_list" name="ordered_list"<?php if ( $this->options['ordered_list'] ) echo ' checked="checked"'; ?> /></td>
</tr>
<tr>
	<th><label for="smooth_scroll"><?php esc_html_e( 'Enable smooth scroll effect', 'table-of-contents-plus' ); ?></label></th>
	<td><input type="checkbox" value="1" id="smooth_scroll" name="smooth_scroll"<?php if ( $this->options['smooth_scroll'] ) echo ' checked="checked"'; ?> /><label for="smooth_scroll"> <?php esc_html_e( 'Scroll rather than jump to the anchor link', 'table-of-contents-plus' ); ?></label></td>
</tr>
</tbody>
</table>

	</div>

	<div id="tab2" class="tab_content">
	<h4><?php esc_html_e( 'Power options', 'table-of-contents-plus' ); ?></h4>
	<table class="form-table">
	<tbody>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.04 @xaviernietosanchez

	if ( is_xtec_super_admin() ) {

	// ************ FI
	?>

	<tr>
		<th><label for="lowercase"><?php esc_html_e( 'Lowercase', 'table-of-contents-plus' ); ?></label></th>
		<td><input type="checkbox" value="1" id="lowercase" name="lowercase"<?php if ( $this->options['lowercase'] ) echo ' checked="checked"'; ?> /><label for="lowercase"> <?php esc_html_e( 'Ensure anchors are in lowercase', 'table-of-contents-plus' ); ?></label></td>
	</tr>
	<tr>
		<th><label for="hyphenate"><?php esc_html_e( 'Hyphenate', 'table-of-contents-plus' ); ?></label></th>
		<td><input type="checkbox" value="1" id="hyphenate" name="hyphenate"<?php if ( $this->options['hyphenate'] ) echo ' checked="checked"'; ?> /><label for="hyphenate"> <?php esc_html_e( 'Use - rather than _ in anchors', 'table-of-contents-plus' ); ?></label></td>
	</tr>
	<tr>
		<th><label for="exclude_css"><?php esc_html_e( 'Exclude CSS file', 'table-of-contents-plus' ); ?></label></th>
		<td><input type="checkbox" value="1" id="exclude_css" name="exclude_css"<?php if ( $this->options['exclude_css'] ) echo ' checked="checked"'; ?> /><label for="exclude_css"> <?php esc_html_e( "Prevent the loading of this plugin's CSS styles. When selected, the appearance options from above will also be ignored.", 'table-of-contents-plus' ); ?></label></td>
	</tr>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.04 @xaviernietosanchez

	}

	// ************ FI
	?>

	<tr>
		<th><?php esc_html_e( 'Heading levels', 'table-of-contents-plus' ); ?></th>
		<td>
		<p><?php esc_html_e( 'Include the following heading levels. Deselecting a heading will exclude it.', 'table-of-contents-plus' ); ?></p><?php
		// show heading 1 to 6 options
		for ( $i = 1; $i <= 6; $i++ ) {
			echo '<input type="checkbox" value="' . esc_attr( $i ) . '" id="heading_levels' . esc_attr( $i ) . '" name="heading_levels[]"';
			if ( in_array( $i, $this->options['heading_levels'], true ) ) {
				echo ' checked="checked"';
			}
			echo '><label for="heading_levels' . esc_attr( $i ) . '"> ' . esc_html( __( 'heading ', 'table-of-contents-plus' ) . $i . ' - h' . $i ) . '</label><br>';
		}
		?>
		</td>
	</tr>
	<tr>
		<th><label for="exclude"><?php esc_html_e( 'Exclude headings', 'table-of-contents-plus' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" value="<?php echo esc_attr( $this->options['exclude'] ); ?>" id="exclude" name="exclude" style="width: 100%;" /><br>
			<label for="exclude"><?php echo wp_kses_post( __( 'Specify headings to be excluded from appearing in the table of contents. Separate multiple headings with a pipe <code>|</code>. Use an asterisk <code>*</code> as a wildcard to match other text. Note that this is not case sensitive. Some examples:', 'table-of-contents-plus' ) ); ?></label><br/>
			<ul>
				<li><?php echo wp_kses_post( __( '<code>Fruit*</code> ignore headings starting with "Fruit"', 'table-of-contents-plus' ) ); ?></li>
				<li><?php echo wp_kses_post( __( '<code>*Fruit Diet*</code> ignore headings with "Fruit Diet" somewhere in the heading', 'table-of-contents-plus' ) ); ?></li>
				<li><?php echo wp_kses_post( __( '<code>Apple Tree|Oranges|Yellow Bananas</code> ignore headings that are exactly "Apple Tree", "Oranges" or "Yellow Bananas"', 'table-of-contents-plus' ) ); ?></li>
			</ul>
		</td>
	</tr>
	<tr>
		<th><label for="exclude_selectors"><?php esc_html_e( 'Exclude by selector or class', 'table-of-contents-plus' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" value="<?php echo esc_attr( $this->options['exclude_selectors'] ); ?>" id="exclude_selectors" name="exclude_selectors" style="width: 100%;" /><br>
			<label for="exclude_selectors"><?php echo wp_kses_post( __( 'Remove headings that appear inside matching containers before the TOC is built. Separate multiple entries with a pipe <code>|</code> or comma. Supports simple selectors such as <code>.class-name</code>, <code>#section-id</code>, <code>tag</code>, <code>tag.class-name</code>, and <code>tag#section-id</code>. Some examples:', 'table-of-contents-plus' ) ); ?></label><br/>
			<ul>
				<li><?php echo wp_kses_post( __( '<code>.wp-block-cover</code> ignore headings inside cover blocks', 'table-of-contents-plus' ) ); ?></li>
				<li><?php echo wp_kses_post( __( '<code>.et_pb_toggle,.et_pb_accordion</code> ignore headings inside Divi toggles or accordions', 'table-of-contents-plus' ) ); ?></li>
				<li><?php echo wp_kses_post( __( '<code>section.hero|div.reusable-banner</code> ignore headings inside specific section or div containers', 'table-of-contents-plus' ) ); ?></li>
			</ul>
		</td>
	</tr>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.04 @xaviernietosanchez

	if ( is_xtec_super_admin() ) {

	// ************ FI
	?>

	<tr id="smooth_scroll_offset_tr" class="<?php if ( ! $this->options['smooth_scroll'] ) echo 'disabled'; ?>">
		<th><label for="smooth_scroll_offset"><?php esc_html_e( 'Smooth scroll top offset', 'table-of-contents-plus' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" value="<?php echo intval( $this->options['smooth_scroll_offset'] ); ?>" id="smooth_scroll_offset" name="smooth_scroll_offset"> px<br>
			<label for="smooth_scroll_offset"><?php esc_html_e( 'If you have a consistent menu across the top of your site, you can adjust the top offset to stop the headings from appearing underneath the top menu. A setting of 30 accommodates the WordPress admin bar. This setting appears after you have enabled smooth scrolling from above.', 'table-of-contents-plus' ); ?></label>
		</td>
	</tr>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.04 @xaviernietosanchez

	}

	// ************ FI
	?>

	</tbody>
	</table>

	<h4><?php
	esc_html_e( 'Usage', 'table-of-contents-plus' ); ?></h4>
	<p><?php
	/* translators: advanced usage, %s is HTML code for <code>[toc]</code> */
	echo wp_kses_post( sprintf( __( 'If you would like to fully customise the position of the table of contents, you can use the %s shortcode by placing it at the desired position of your post, page or custom post type. This method allows you to generate the table of contents despite having auto insertion disabled for its content type. Please visit the help tab for further information about this shortcode.', 'table-of-contents-plus' ), '<code>[toc]</code>' ) ); ?></p>
	</div>

	<div id="tab3" class="tab_content">
	<h3><?php esc_html_e( 'Appearance', 'table-of-contents-plus' ); ?></h3>
	<table class="form-table">
	<tbody>
	<tr>
		<th><label for="width"><?php esc_html_e( 'Width', 'table-of-contents-plus' ); ?></label></td>
		<td>
			<select name="width" id="width">
				<optgroup label="<?php esc_html_e( 'Fixed width', 'table-of-contents-plus' ); ?>">
					<option value="200px"<?php if ( '200px' === $this->options['width'] ) echo ' selected="selected"'; ?>>200px</option>
					<option value="225px"<?php if ( '225px' === $this->options['width'] ) echo ' selected="selected"'; ?>>225px</option>
					<option value="250px"<?php if ( '250px' === $this->options['width'] ) echo ' selected="selected"'; ?>>250px</option>
					<option value="275px"<?php if ( '275px' === $this->options['width'] ) echo ' selected="selected"'; ?>>275px</option>
					<option value="300px"<?php if ( '300px' === $this->options['width'] ) echo ' selected="selected"'; ?>>300px</option>
					<option value="325px"<?php if ( '325px' === $this->options['width'] ) echo ' selected="selected"'; ?>>325px</option>
					<option value="350px"<?php if ( '350px' === $this->options['width'] ) echo ' selected="selected"'; ?>>350px</option>
					<option value="375px"<?php if ( '375px' === $this->options['width'] ) echo ' selected="selected"'; ?>>375px</option>
					<option value="400px"<?php if ( '400px' === $this->options['width'] ) echo ' selected="selected"'; ?>>400px</option>
				</optgroup>
				<optgroup label="<?php esc_html_e( 'Relative', 'table-of-contents-plus' ); ?>">
					<option value="Auto"<?php if ( 'Auto' === $this->options['width'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Auto (default)', 'table-of-contents-plus' ); ?></option>
					<option value="25%"<?php if ( '25%' === $this->options['width'] ) echo ' selected="selected"'; ?>>25%</option>
					<option value="33%"<?php if ( '33%' === $this->options['width'] ) echo ' selected="selected"'; ?>>33%</option>
					<option value="50%"<?php if ( '50%' === $this->options['width'] ) echo ' selected="selected"'; ?>>50%</option>
					<option value="66%"<?php if ( '66%' === $this->options['width'] ) echo ' selected="selected"'; ?>>66%</option>
					<option value="75%"<?php if ( '75%' === $this->options['width'] ) echo ' selected="selected"'; ?>>75%</option>
					<option value="100%"<?php if ( '100%' === $this->options['width'] ) echo ' selected="selected"'; ?>>100%</option>
				</optgroup>
				<optgroup label="<?php
				/* translators: other width */
				esc_html_e( 'Other', 'table-of-contents-plus' ); ?>">
					<option value="User defined"<?php if ( 'User defined' === $this->options['width'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'User defined', 'table-of-contents-plus' ); ?></option>
				</optgroup>
			</select>
			<div class="more_toc_options<?php if ( 'User defined' !== $this->options['width'] ) echo ' disabled'; ?>">
				<label for="width_custom"><?php
				/* translators: ignore %s as it's some HTML label tags */
				echo wp_kses_post( sprintf( __( 'Please enter a number and %s select its units, eg: 100px, 10em', 'table-of-contents-plus' ), '</label><label for="width_custom_units">' ) ); ?></label><br>
				<input type="text" class="regular-text" value="<?php echo floatval( $this->options['width_custom'] ); ?>" id="width_custom" name="width_custom" />
				<select name="width_custom_units" id="width_custom_units">
					<option value="px"<?php if ( 'px' === $this->options['width_custom_units'] ) echo ' selected="selected"'; ?>>px</option>
					<option value="%"<?php if ( '%' === $this->options['width_custom_units'] ) echo ' selected="selected"'; ?>>%</option>
					<option value="em"<?php if ( 'em' === $this->options['width_custom_units'] ) echo ' selected="selected"'; ?>>em</option>
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<th><label for="wrapping"><?php esc_html_e( 'Wrapping', 'table-of-contents-plus' ); ?></label></td>
		<td>
			<select name="wrapping" id="wrapping">
				<option value="<?php echo esc_attr( TOC_WRAPPING_NONE ); ?>"<?php if ( TOC_WRAPPING_NONE === $this->options['wrapping'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'None (default)', 'table-of-contents-plus' ); ?></option>
				<option value="<?php echo esc_attr( TOC_WRAPPING_LEFT ); ?>"<?php if ( TOC_WRAPPING_LEFT === $this->options['wrapping'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Left', 'table-of-contents-plus' ); ?></option>
				<option value="<?php echo esc_attr( TOC_WRAPPING_RIGHT ); ?>"<?php if ( TOC_WRAPPING_RIGHT === $this->options['wrapping'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Right', 'table-of-contents-plus' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="font_size"><?php esc_html_e( 'Font size', 'table-of-contents-plus' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" value="<?php echo floatval( $this->options['font_size'] ); ?>" id="font_size" name="font_size" />
			<select name="font_size_units" id="font_size_units">
				<option value="px"<?php if ( 'pt' === $this->options['font_size_units'] ) echo ' selected="selected"'; ?>>pt</option>
				<option value="%"<?php if ( '%' === $this->options['font_size_units'] ) echo ' selected="selected"'; ?>>%</option>
				<option value="em"<?php if ( 'em' === $this->options['font_size_units'] ) echo ' selected="selected"'; ?>>em</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="design_preset"><?php esc_html_e( 'Design preset', 'table-of-contents-plus' ); ?></label></th>
		<td>
			<select name="design_preset" id="design_preset">
				<option value="default"<?php if ( 'default' === $this->options['design_preset'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Default', 'table-of-contents-plus' ); ?></option>
				<option value="minimal"<?php if ( 'minimal' === $this->options['design_preset'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Minimal', 'table-of-contents-plus' ); ?></option>
				<option value="editorial"<?php if ( 'editorial' === $this->options['design_preset'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Editorial', 'table-of-contents-plus' ); ?></option>
				<option value="docs"<?php if ( 'docs' === $this->options['design_preset'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Docs', 'table-of-contents-plus' ); ?></option>
				<option value="card"<?php if ( 'card' === $this->options['design_preset'] ) echo ' selected="selected"'; ?>><?php esc_html_e( 'Card', 'table-of-contents-plus' ); ?></option>
			</select><br>
			<span class="description"><label for="design_preset"><?php esc_html_e( 'Applies structural styling such as spacing, borders, and title treatment. Presentation colours still apply on top.', 'table-of-contents-plus' ); ?></label></span>
		</td>
	</tr>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.11 @xaviernietosanchez

	if ( is_xtec_super_admin() ) {

	// ************ FI
	?>

	<tr>
		<th><?php
		/* translators: appearance / colour / look and feel options */
		esc_html_e( 'Presentation', 'table-of-contents-plus' ); ?>
		</th>
		<td><?php

		$theme_options = [
			[
				'id'   => TOC_THEME_GREY,
				'name' => __( 'Grey (default)', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/grey.png',
			],
			[
				'id'   => TOC_THEME_LIGHT_BLUE,
				'name' => __( 'Light blue', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/blue.png',
			],
			[
				'id'   => TOC_THEME_WHITE,
				'name' => __( 'White', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/white.png',
			],
			[
				'id'   => TOC_THEME_BLACK,
				'name' => __( 'Black', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/black.png',
			],
			[
				'id'   => TOC_THEME_TRANSPARENT,
				'name' => __( 'Transparent', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/transparent.png',
			],
			[
				'id'   => TOC_THEME_CUSTOM,
				'name' => __( 'Custom', 'table-of-contents-plus' ),
				'url'  => TOC_PLUGIN_PATH . '/images/custom.png',
			],
		];

		foreach ( $theme_options as $theme_option ) {
			printf(
				'<div class="toc_theme_option">' .
					'<input type="radio" name="theme" id="theme_%1$s" value="%1$s"%2$s>' .
					'<label for="theme_%1$s"> %3$s<br><img src="%4$s"" alt="" /></label>' .
				'</div>',
				esc_attr( $theme_option['id'] ),
				( $theme_option['id'] === $this->options['theme'] ) ? ' checked="checked"' : '',
				esc_html( $theme_option['name'] ),
				esc_url( $theme_option['url'] )
			);
		}
		?>
			<div class="clear"></div>

			<div class="more_toc_options<?php if ( TOC_THEME_CUSTOM !== $this->options['theme'] ) echo ' disabled'; ?>">
				<table id="theme_custom" class="more_toc_options_table">
					<tbody>

				<?php
				$custom_theme_props = [
					[
						'id'    => 'custom_background_colour',
						'name'  => __( 'Background', 'table-of-contents-plus' ),
						'value' => $this->options['custom_background_colour'],
					],
					[
						'id'    => 'custom_border_colour',
						'name'  => __( 'Border', 'table-of-contents-plus' ),
						'value' => $this->options['custom_border_colour'],
					],
					[
						'id'    => 'custom_title_colour',
						'name'  => __( 'Title', 'table-of-contents-plus' ),
						'value' => $this->options['custom_title_colour'],
					],
					[
						'id'    => 'custom_links_colour',
						'name'  => __( 'Links', 'table-of-contents-plus' ),
						'value' => $this->options['custom_links_colour'],
					],
					[
						'id'    => 'custom_links_hover_colour',
						'name'  => __( 'Links (hover)', 'table-of-contents-plus' ),
						'value' => $this->options['custom_links_hover_colour'],
					],
					[
						'id'    => 'custom_links_visited_colour',
						'name'  => __( 'Links (visited)', 'table-of-contents-plus' ),
						'value' => $this->options['custom_links_visited_colour'],
					],
				];

				foreach ( $custom_theme_props as $custom_theme_prop ) {
					printf(
						'<tr>' .
							'<th><label for="%1$s">%2$s</label></th>' .
							'<td><input type="text" class="custom_colour_option" value="%3$s" id="%1$s" name="%1$s"> <img src="%4$s/images/colour-wheel.png" alt=""></td>' .
						'</tr>',
						esc_attr( $custom_theme_prop['id'] ),
						esc_html( $custom_theme_prop['name'] ),
						esc_attr( $custom_theme_prop['value'] ),
						esc_url( TOC_PLUGIN_PATH )
					);
				}

				?>
				</tbody>
				</table>
				<div id="farbtastic_colour_wheel"></div>
				<div class="clear"></div>
				<p><?php
				/* translators: %s translates to <code>#</code> */
				echo wp_kses_post( sprintf( __( "Leaving the value as %s will inherit your theme's styles", 'table-of-contents-plus' ), '<code>#</code>' ) ); ?></p>
			</div>
		</td>
	</tr>

	<?php
	// XTEC ************ AFEGIT - Restrict access to all users but xtecadmin
	// 2017.05.04 @xaviernietosanchez

	}

	// ************ FI
	?>

	</tbody>
	</table>
	</div>

	<div id="tab4" class="tab_content">
	<h4><?php esc_html_e( 'Export Settings', 'table-of-contents-plus' ); ?></h4>
	<p><?php esc_html_e( 'Copy this JSON bundle to back up your WPTOC+ settings or move them to another site running this fork.', 'table-of-contents-plus' ); ?></p>
	<textarea class="large-text code wptoc-admin-export-field" rows="16" readonly="readonly"><?php echo esc_textarea( $export_settings_payload ); ?></textarea>

	<h4><?php esc_html_e( 'Import Settings', 'table-of-contents-plus' ); ?></h4>
	<p><?php esc_html_e( 'Paste a previously exported WPTOC+ JSON bundle below. Importing replaces the current plugin settings on this site.', 'table-of-contents-plus' ); ?></p>
	<textarea class="large-text code wptoc-admin-import-field" rows="16" name="settings_import_blob" placeholder="<?php esc_attr_e( 'Paste exported WPTOC+ settings JSON here', 'table-of-contents-plus' ); ?>"><?php echo isset( $_POST['settings_import_blob'] ) ? esc_textarea( wp_unslash( $_POST['settings_import_blob'] ) ) : ''; ?></textarea>
	<p class="submit wptoc-admin-import-submit">
		<input
			type="submit"
			name="import_settings"
			class="button button-secondary"
			value="<?php esc_attr_e( 'Import Settings', 'table-of-contents-plus' ); ?>"
			data-busy-title="<?php esc_attr_e( 'Importing settings', 'table-of-contents-plus' ); ?>"
			data-busy-text="<?php esc_attr_e( 'Please wait while WPTOC+ validates and imports your settings.', 'table-of-contents-plus' ); ?>"
		/>
	</p>

	</div>


	</div>
</div>


<div class="submit wptoc-admin-submit-row">
	<input
		type="submit"
		name="restore_default_settings"
		class="button button-secondary wptoc-admin-reset-button"
		value="<?php esc_attr_e( 'Reset Settings', 'table-of-contents-plus' ); ?>"
		data-busy-title="<?php esc_attr_e( 'Restoring default settings', 'table-of-contents-plus' ); ?>"
		data-busy-text="<?php esc_attr_e( 'Please wait while WPTOC+ restores the default settings.', 'table-of-contents-plus' ); ?>"
	/>
	<input type="submit" name="submit" class="button-primary" value="<?php esc_html_e( 'Update Options', 'table-of-contents-plus' ); ?>" />
</div>
</form>
</div>
			<?php
		}


		/**
		 * Returns a string of custom CSS based on appearance options the
		 * user selected in the admin GUI.
		 */
		private function get_custom_css() {
			$css = '';

			if ( ! $this->options['exclude_css'] ) {
				if ( TOC_THEME_CUSTOM === $this->options['theme'] || 'Auto' !== $this->options['width'] ) {
					$css .= 'div#toc_container {';
					if ( TOC_THEME_CUSTOM === $this->options['theme'] ) {
						$css .= 'background: ' . $this->options['custom_background_colour'] . ';border: 1px solid ' . $this->options['custom_border_colour'] . ';';
					}
					if ( 'Auto' !== $this->options['width'] ) {
						$css .= '--wptoc-container-width: ' . $this->get_toc_width_value() . ';';
					}
					$css .= '}';
				}

				if ( '95%' !== $this->options['font_size'] . $this->options['font_size_units'] ) {
					$css .= 'div#toc_container ul li {font-size: ' . $this->options['font_size'] . $this->options['font_size_units'] . ';}';
				}

				if ( TOC_THEME_CUSTOM === $this->options['theme'] ) {
					if ( TOC_DEFAULT_TITLE_COLOUR !== $this->options['custom_title_colour'] ) {
						$css .= 'div#toc_container p.toc_title {color: ' . $this->options['custom_title_colour'] . ';}';
					}
					if ( TOC_DEFAULT_LINKS_COLOUR !== $this->options['custom_links_colour'] ) {
						$css .= 'div#toc_container p.toc_title a,div#toc_container ul.toc_list a {color: ' . $this->options['custom_links_colour'] . ';}';
					}
					if ( TOC_DEFAULT_LINKS_HOVER_COLOUR !== $this->options['custom_links_hover_colour'] ) {
						$css .= 'div#toc_container p.toc_title a:hover,div#toc_container ul.toc_list a:hover {color: ' . $this->options['custom_links_hover_colour'] . ';}';
					}
					if ( TOC_DEFAULT_LINKS_HOVER_COLOUR !== $this->options['custom_links_hover_colour'] ) {
						$css .= 'div#toc_container p.toc_title a:hover,div#toc_container ul.toc_list a:hover {color: ' . $this->options['custom_links_hover_colour'] . ';}';
					}
					if ( TOC_DEFAULT_LINKS_VISITED_COLOUR !== $this->options['custom_links_visited_colour'] ) {
						$css .= 'div#toc_container p.toc_title a:visited,div#toc_container ul.toc_list a:visited {color: ' . $this->options['custom_links_visited_colour'] . ';}';
					}
				}
			}

			return $css;
		}


		private function build_toc_html( $items, $css_classes, $toc_title_template ) {
			$html = '<div id="toc_container" class="' . htmlentities( $css_classes, ENT_COMPAT, 'UTF-8' ) . '">';

			if ( $this->options['show_heading_text'] ) {
				$toc_title = htmlentities( $toc_title_template, ENT_COMPAT, 'UTF-8' );
				if ( false !== strpos( $toc_title, '%PAGE_TITLE%' ) ) {
					$toc_title = str_replace( '%PAGE_TITLE%', get_the_title(), $toc_title );
				}
				if ( false !== strpos( $toc_title, '%PAGE_NAME%' ) ) {
					$toc_title = str_replace( '%PAGE_NAME%', get_the_title(), $toc_title );
				}
				$html .= '<p class="toc_title">' . $toc_title . '</p>';
			}

			$html .= '<ul class="toc_list">' . $items . '</ul></div>' . "\n";

			return $html;
		}


		private function build_sticky_column_layout( $toc_html, $body_html ) {
			$layout_class = 'left' === $this->options['floating_side'] ? 'toc_sticky_column_left' : 'toc_sticky_column_right';
			$layout_style = '';

			if ( 'Auto' !== $this->options['width'] ) {
				$layout_style = ' style="--wptoc-column-width:' . esc_attr( $this->get_toc_width_value() ) . ';"';
			}

			$toc_column  = '<div class="toc_sticky_column_rail">' . $toc_html . '</div>';
			$main_column = '<div class="toc_sticky_column_main">' . $body_html . '</div>';

			if ( 'toc_sticky_column_left' === $layout_class ) {
				return '<div class="toc_sticky_column_layout ' . $layout_class . '"' . $layout_style . '>' . $toc_column . $main_column . '</div>';
			}

			return '<div class="toc_sticky_column_layout ' . $layout_class . '"' . $layout_style . '>' . $main_column . $toc_column . '</div>';
		}


		/**
		 * Returns a clean url to be used as the destination anchor target
		 */
		private function url_anchor_target( $title ) {
			$return = false;

			if ( $title ) {
				$return = trim( wp_strip_all_tags( $title ) );

				// convert accented characters to ASCII
				$return = remove_accents( $return );

				// replace newlines with spaces (eg when headings are split over multiple lines)
				$return = str_replace( [ "\r", "\n", "\n\r", "\r\n" ], ' ', $return );

				// remove &amp;
				$return = str_replace( '&amp;', '', $return );

				// remove non alphanumeric chars
				$return = preg_replace( '/[^a-zA-Z0-9 \-_]*/', '', $return );

				// convert spaces to _
				$return = str_replace(
					[ '  ', ' ' ],
					'_',
					$return
				);

				// remove trailing - and _
				$return = rtrim( $return, '-_' );

				// lowercase everything?
				if ( $this->options['lowercase'] ) {
					$return = strtolower( $return );
				}

				// if blank, then prepend with the fragment prefix
				// blank anchors normally appear on sites that don't use the latin charset
				if ( ! $return ) {
					$return = '_';
				}

				// hyphenate?
				if ( $this->options['hyphenate'] ) {
					$return = str_replace( '_', '-', $return );
					$return = str_replace( '--', '-', $return );
				}
			}

			if ( array_key_exists( $return, $this->collision_collector ) ) {
				$this->collision_collector[ $return ]++;
				$return .= '-' . $this->collision_collector[ $return ];
			} else {
				$this->collision_collector[ $return ] = 1;
			}

			return apply_filters( 'toc_url_anchor_target', $return );
		}


		private function build_hierarchy( &$matches ) {
			$html           = '';
			$heading_stack  = [];
			$numbered_items = [];
			$count_matches  = count( $matches );

			for ( $i = 0; $i < $count_matches; $i++ ) {
				$depth = (int) $matches[ $i ][2];

				if ( empty( $heading_stack ) ) {
					$heading_stack[]    = $depth;
					$numbered_items[1] = 0;
					$html              .= '<li>';
				} elseif ( $depth > end( $heading_stack ) ) {
					// Collapse skipped heading levels into a single nested level to keep the markup valid.
					$heading_stack[] = $depth;
					if ( ! isset( $numbered_items[ count( $heading_stack ) ] ) ) {
						$numbered_items[ count( $heading_stack ) ] = 0;
					}
					$html .= '<ul><li>';
				} else {
					while ( ! empty( $heading_stack ) && $depth < end( $heading_stack ) ) {
						$numbered_items[ count( $heading_stack ) ] = 0;
						array_pop( $heading_stack );
						$html .= '</li></ul>';
					}

					if ( empty( $heading_stack ) ) {
						$heading_stack[]    = $depth;
						$numbered_items[1] = 0;
						$html              .= '<li>';
					} elseif ( $depth > end( $heading_stack ) ) {
						$heading_stack[] = $depth;
						if ( ! isset( $numbered_items[ count( $heading_stack ) ] ) ) {
							$numbered_items[ count( $heading_stack ) ] = 0;
						}
						$html .= '<ul><li>';
					} else {
						$html .= '</li><li>';
					}
				}

				$display_depth = count( $heading_stack );
				$anchor        = isset( $matches[ $i ]['anchor'] ) ? $matches[ $i ]['anchor'] : $this->url_anchor_target( $matches[ $i ][0] );
				$html         .= '<a href="#' . $anchor . '">';

				if ( $this->options['ordered_list'] ) {
					$html .= '<span class="toc_number toc_depth_' . $display_depth . '">';
					for ( $j = 1; $j < $display_depth; $j++ ) {
						$number = isset( $numbered_items[ $j ] ) ? $numbered_items[ $j ] : 0;
						$html  .= $number . '.';
					}

					$current_number = isset( $numbered_items[ $display_depth ] ) ? $numbered_items[ $display_depth ] + 1 : 1;
					$html          .= $current_number . '</span> ';
					$numbered_items[ $display_depth ] = $current_number;
				}

				$html .= wp_strip_all_tags( $matches[ $i ][0] ) . '</a>';
			}

			while ( ! empty( $heading_stack ) ) {
				$html .= '</li>';
				if ( count( $heading_stack ) > 1 ) {
					$html .= '</ul>';
				}
				array_pop( $heading_stack );
			}

			return $html;
		}


		/**
		 * Returns a string with all items from the $find array replaced with their matching
		 * items in the $replace array.  This does a one to one replacement (rather than
		 * globally).
		 *
		 * This function is multibyte safe.
		 *
		 * $find and $replace are arrays, $string is the haystack.  All variables are
		 * passed by reference.
		 */
		private function mb_find_replace( &$find = false, &$replace = false, &$string = '' ) {
			if ( is_array( $find ) && is_array( $replace ) && $string ) {
				$count_find = count( $find );
				// check if multibyte strings are supported
				if ( function_exists( 'mb_strpos' ) ) {
					for ( $i = 0; $i < $count_find; $i++ ) {
						$string =
							mb_substr( $string, 0, mb_strpos( $string, $find[ $i ] ) ) . // everything before $find
							$replace[ $i ] . // its replacement
							mb_substr( $string, mb_strpos( $string, $find[ $i ] ) + mb_strlen( $find[ $i ] ) ); // everything after $find
					}
				} else {
					for ( $i = 0; $i < $count_find; $i++ ) {
						$string = substr_replace(
							$string,
							$replace[ $i ],
							strpos( $string, $find[ $i ] ),
							strlen( $find[ $i ] )
						);
					}
				}
			}

			return $string;
		}


		private function get_excluded_selectors() {
			if ( ! $this->options['exclude_selectors'] ) {
				return [];
			}

			return array_values(
				array_filter(
					array_map(
						'trim',
						preg_split( '/[\r\n|,]+/', $this->options['exclude_selectors'] )
					)
				)
			);
		}


		private function get_selector_xpath( $selector ) {
			$selector = trim( $selector );

			if ( ! $selector ) {
				return false;
			}

			if ( preg_match( '/^\.([A-Za-z0-9_-]+)$/', $selector, $matches ) ) {
				return '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $matches[1] . ' ")]';
			}

			if ( preg_match( '/^#([A-Za-z0-9_-]+)$/', $selector, $matches ) ) {
				return '//*[@id="' . $matches[1] . '"]';
			}

			if ( preg_match( '/^([A-Za-z][A-Za-z0-9_-]*)\.([A-Za-z0-9_-]+)$/', $selector, $matches ) ) {
				return '//' . strtolower( $matches[1] ) . '[contains(concat(" ", normalize-space(@class), " "), " ' . $matches[2] . ' ")]';
			}

			if ( preg_match( '/^([A-Za-z][A-Za-z0-9_-]*)#([A-Za-z0-9_-]+)$/', $selector, $matches ) ) {
				return '//' . strtolower( $matches[1] ) . '[@id="' . $matches[2] . '"]';
			}

			if ( preg_match( '/^[A-Za-z][A-Za-z0-9_-]*$/', $selector ) ) {
				return '//' . strtolower( $selector );
			}

			return false;
		}


		private function strip_excluded_heading_containers( $content ) {
			$selectors = $this->get_excluded_selectors();

			if ( empty( $selectors ) || ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
				return $content;
			}

			$internal_errors = libxml_use_internal_errors( true );
			$dom             = new DOMDocument();
			$wrapper_id      = 'toc-exclude-root';
			$html            = '<?xml encoding="utf-8" ?><!DOCTYPE html><html><body><div id="' . $wrapper_id . '">' . $content . '</div></body></html>';

			if ( ! $dom->loadHTML( $html ) ) {
				libxml_clear_errors();
				libxml_use_internal_errors( $internal_errors );
				return $content;
			}

			$xpath = new DOMXPath( $dom );

			foreach ( $selectors as $selector ) {
				$query = $this->get_selector_xpath( $selector );

				if ( ! $query ) {
					continue;
				}

				$nodes = $xpath->query( $query );

				if ( ! $nodes ) {
					continue;
				}

				$to_remove = [];

				foreach ( $nodes as $node ) {
					if ( ! $node instanceof DOMElement || $wrapper_id === $node->getAttribute( 'id' ) ) {
						continue;
					}

					$to_remove[] = $node;
				}

				foreach ( $to_remove as $node ) {
					if ( $node->parentNode ) {
						$node->parentNode->removeChild( $node );
					}
				}
			}

			$root = $xpath->query( '//*[@id="' . $wrapper_id . '"]' )->item( 0 );

			if ( ! $root ) {
				libxml_clear_errors();
				libxml_use_internal_errors( $internal_errors );
				return $content;
			}

			$filtered_content = '';

			foreach ( $root->childNodes as $child ) {
				$filtered_content .= $dom->saveHTML( $child );
			}

			libxml_clear_errors();
			libxml_use_internal_errors( $internal_errors );

			return $filtered_content;
		}


		private function get_heading_matches( $content = '', $apply_extract_filter = true ) {
			$matches = [];

			if ( ! $content ) {
				return $matches;
			}

			if ( $apply_extract_filter ) {
				$content = apply_filters( 'toc_extract_headings', $content );
			}

			$content = $this->strip_excluded_heading_containers( $content );

			if ( ! preg_match_all( '/(<h([1-6]{1})[^>]*>).*<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER ) ) {
				return [];
			}

			if ( count( $this->options['heading_levels'] ) !== 6 ) {
				$matches = array_values(
					array_filter(
						$matches,
						function ( $match ) {
							return in_array( (int) $match[2], $this->options['heading_levels'], true );
						}
					)
				);
			}

			if ( $this->options['exclude'] ) {
				$excluded_headings = array_map(
					function ( $heading ) {
						return str_replace( [ '*' ], [ '.*' ], trim( $heading ) );
					},
					explode( '|', $this->options['exclude'] )
				);

				$matches = array_values(
					array_filter(
						$matches,
						function ( $match ) use ( $excluded_headings ) {
							$heading_text = wp_strip_all_tags( $match[0] );

							foreach ( $excluded_headings as $excluded_heading ) {
								if ( @preg_match( '/^' . $excluded_heading . '$/imU', $heading_text ) ) {
									return false;
								}
							}

							return true;
						}
					)
				);
			}

			return array_values(
				array_filter(
					$matches,
					function ( $match ) {
						return trim( wp_strip_all_tags( $match[0] ) ) !== '';
					}
				)
			);
		}


		private function count_content_headings( $content = '' ) {
			return count( $this->get_heading_matches( $content, false ) );
		}


		private function should_render_toc( $content ) {
			$post_override = $this->get_post_override();

			if ( 'hide' === $post_override ) {
				return false;
			}

			if ( 'show' === $post_override ) {
				return true;
			}

			if ( $this->is_eligible() ) {
				return true;
			}

			if ( false !== strpos( $content, '[no_toc]' ) ) {
				return false;
			}

			return in_array( get_post_type(), $this->options['auto_insert_post_types'], true )
				&& $this->count_content_headings( $content ) >= $this->options['start'];
		}


		/**
		 * This function extracts headings from the html formatted $content.  It will pull out
		 * only the required headings as specified in the options.  For all qualifying headings,
		 * this function populates the $find and $replace arrays (both passed by reference)
		 * with what to search and replace with.
		 *
		 * Returns a html formatted string of list items for each qualifying heading.  This
		 * is everything between and NOT including <ul> and </ul>
		 */
		public function extract_headings( &$find, &$replace, $content = '' ) {
			$matches = [];
			$anchor  = '';
			$items   = false;

			// reset the internal collision collection as the_content may have been triggered elsewhere
			// eg by themes or other plugins that need to read in content such as metadata fields in
			// the head html tag, or to provide descriptions to twitter/facebook
			$this->collision_collector = [];

			if ( is_array( $find ) && is_array( $replace ) && $content ) {
				$matches = $this->get_heading_matches( $content );

				if ( count( $matches ) >= $this->options['start'] ) {
					$count_matches = count( $matches );
					for ( $i = 0; $i < $count_matches; $i++ ) {
						$anchor                 = $this->url_anchor_target( $matches[ $i ][0] );
						$matches[ $i ]['anchor'] = $anchor;
						$find[]                 = $matches[ $i ][0];
						$replace[]              = str_replace(
							[
								$matches[ $i ][1],
								'</h' . $matches[ $i ][2] . '>',
							],
							[
								$matches[ $i ][1] . '<span id="' . $anchor . '">',
								'</span></h' . $matches[ $i ][2] . '>',
							],
							$matches[ $i ][0]
						);

						if ( ! $this->options['show_heirarchy'] ) {
							$items .= '<li><a href="#' . $anchor . '">';
							if ( $this->options['ordered_list'] ) {
								$items .= count( $replace ) . ' ';
							}
							$items .= wp_strip_all_tags( $matches[ $i ][0] ) . '</a></li>';
						}
					}

					if ( $this->options['show_heirarchy'] ) {
						$items = $this->build_hierarchy( $matches );
					}
				}
			}

			return $items;
		}


		/**
		 * Returns true if the table of contents is eligible to be printed, false otherwise.
		 */
		public function is_eligible() {
			global $post;

			$custom_toc_position = isset( $post->post_content ) ? has_shortcode( $post->post_content, 'toc' ) : false;

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return false;
			}

			// do not trigger the TOC when displaying an XML/RSS feed
			if ( is_feed() ) {
				return false;
			}

			// if the shortcode was used, this bypasses many of the global options
			if ( false !== $custom_toc_position ) {
				return ! is_front_page();
			} else {
				if (
					in_array( get_post_type( $post ), $this->options['auto_insert_post_types'], true ) && $this->show_toc && ! is_search() && ! is_archive() && ! is_front_page()
				) {
					return true;
				} else {
					return false;
				}
			}
		}

		public function the_content( $content ) {
			global $post;
			$items               = '';
			$css_classes         = '';
			$find                = [];
			$replace             = [];
			$custom_toc_position = strpos( $content, '<!--TOC-->' );
			$toc_title_template  = $this->get_effective_heading_text( $post );

			if ( $this->should_render_toc( $content ) ) {

				$items = $this->extract_headings( $find, $replace, $content );

				if ( $items ) {
					// do we display the toc within the content or has the user opted
					// to only show it in the widget?  if so, then we still need to
					// make the find/replace call to insert the anchors
					if ( $this->options['show_toc_in_widget_only'] && ( in_array( get_post_type(), $this->options['show_toc_in_widget_only_post_types'], true ) ) ) {
						$content = $this->mb_find_replace( $find, $replace, $content );
					} else {

						// wrapping css classes
						if ( 'sticky-column' !== $this->options['display_mode'] ) {
							switch ( $this->options['wrapping'] ) {
								case TOC_WRAPPING_LEFT:
									$css_classes .= ' toc_wrap_left';
									break;

								case TOC_WRAPPING_RIGHT:
									$css_classes .= ' toc_wrap_right';
									break;

								case TOC_WRAPPING_NONE:
								default:
									// do nothing
							}
						}

						// colour themes
						switch ( $this->options['theme'] ) {
							case TOC_THEME_LIGHT_BLUE:
								$css_classes .= ' toc_light_blue';
								break;

							case TOC_THEME_WHITE:
								$css_classes .= ' toc_white';
								break;

							case TOC_THEME_BLACK:
								$css_classes .= ' toc_black';
								break;

							case TOC_THEME_TRANSPARENT:
								$css_classes .= ' toc_transparent';
								break;

							case TOC_THEME_GREY:
							default:
								// do nothing
						}

						switch ( $this->options['display_mode'] ) {
							case 'floating':
								$css_classes .= ' toc_display_floating toc_floating_' . $this->options['floating_side'];
								break;

							case 'sticky':
								$css_classes .= ' toc_display_sticky';
								break;

							case 'sticky-column':
								$css_classes .= ' toc_display_sticky_column';
								break;

							case 'inline':
							default:
								// do nothing
						}

						$css_classes .= ' toc_preset_' . sanitize_html_class( $this->options['design_preset'] );

							if ( 'compact' === $this->options['mobile_mode'] ) {
								$css_classes .= ' toc_mobile_compact';
							}

							if ( $this->options['show_heirarchy'] && $this->options['collapsible_subsections'] ) {
								$css_classes .= ' toc_collapsible_subsections';

								if ( ! $this->options['collapse_subsections_by_default'] ) {
									$css_classes .= ' toc_collapsible_default_open';
								}
							}

						if ( $this->options['css_container_class'] ) {
							$css_classes .= ' ' . $this->options['css_container_class'];
						}

						$css_classes = trim( $css_classes );

						// an empty class="" is invalid markup!
						if ( ! $css_classes ) {
							$css_classes = ' ';
						}

						$html = $this->build_toc_html( $items, $css_classes, $toc_title_template );

						if ( 'sticky-column' === $this->options['display_mode'] ) {
							$content_with_anchors = $content;

							if ( count( $find ) > 0 ) {
								$content_with_anchors = $this->mb_find_replace( $find, $replace, $content_with_anchors );
							}

							if ( false !== $custom_toc_position ) {
								$parts = explode( '<!--TOC-->', $content_with_anchors, 2 );

								if ( 2 === count( $parts ) ) {
									$content = $parts[0] . $this->build_sticky_column_layout( $html, $parts[1] );
								} else {
									$content = str_replace( '<!--TOC-->', $html, $content_with_anchors );
								}
							} elseif ( count( $find ) > 0 ) {
								switch ( $this->options['position'] ) {
									case TOC_POSITION_TOP:
										$content = $this->build_sticky_column_layout( $html, $content_with_anchors );
										break;

									case TOC_POSITION_BOTTOM:
										$content = $content_with_anchors . $html;
										break;

									case TOC_POSITION_AFTER_FIRST_HEADING:
										$first_heading = $replace[0];
										$split_at      = strpos( $content_with_anchors, $first_heading );

										if ( false === $split_at ) {
											$content = $this->build_sticky_column_layout( $html, $content_with_anchors );
										} else {
											$split_at += strlen( $first_heading );
											$content   = substr( $content_with_anchors, 0, $split_at ) . $this->build_sticky_column_layout( $html, substr( $content_with_anchors, $split_at ) );
										}
										break;

									case TOC_POSITION_BEFORE_FIRST_HEADING:
									default:
										$first_heading = $replace[0];
										$split_at      = strpos( $content_with_anchors, $first_heading );

										if ( false === $split_at ) {
											$content = $this->build_sticky_column_layout( $html, $content_with_anchors );
										} else {
											$content = substr( $content_with_anchors, 0, $split_at ) . $this->build_sticky_column_layout( $html, substr( $content_with_anchors, $split_at ) );
										}
							}
							} else {
								$content = $this->build_sticky_column_layout( $html, $content_with_anchors );
							}
						} elseif ( false !== $custom_toc_position ) {
							$find[]    = '<!--TOC-->';
							$replace[] = $html;
							$content   = $this->mb_find_replace( $find, $replace, $content );
						} else {
							if ( count( $find ) > 0 ) {
								switch ( $this->options['position'] ) {
									case TOC_POSITION_TOP:
										$content = $html . $this->mb_find_replace( $find, $replace, $content );
										break;

									case TOC_POSITION_BOTTOM:
										$content = $this->mb_find_replace( $find, $replace, $content ) . $html;
										break;

									case TOC_POSITION_AFTER_FIRST_HEADING:
										$replace[0] = $replace[0] . $html;
										$content    = $this->mb_find_replace( $find, $replace, $content );
										break;

									case TOC_POSITION_BEFORE_FIRST_HEADING:
									default:
										$replace[0] = $html . $replace[0];
										$content    = $this->mb_find_replace( $find, $replace, $content );
								}
							}
						}
					}
				}
			} else {
				// remove <!--TOC--> (inserted from shortcode) from content
				$content = str_replace( '<!--TOC-->', '', $content );
			}

			return $content;
		}

	} // end class
endif;

