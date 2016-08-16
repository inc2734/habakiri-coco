<?php
/**
 * Plugin Name: Coco - Habakiri design skin
 * Plugin URI: https://github.com/inc2734/habakiri-coco
 * Description: Coco is a design skin of Habakiri. This plugin needs Habakiri 2.0.0 or later.
 * Version: 2.1.0
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : July 17, 2015
 * Modified: August 16, 2016
 * Text Domain: habakiri-coco
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( defined( 'HABAKIRI_DESIGN_SKIN' ) && get_template() === 'habakiri' ) {
	return;
}

define( 'HABAKIRI_DESIGN_SKIN', true );

include_once( plugin_dir_path( __FILE__ ) . 'classes/class.config.php' );

if ( ! class_exists( 'Habakiri_Plugin_GitHub_Updater' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'classes/class.github-updater.php' );
}
new Habakiri_Plugin_GitHub_Updater( 'habakiri-coco', __FILE__, 'inc2734' );

class Habakiri_Coco {

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * 言語ファイルの読み込み
	 */
	public function plugins_loaded() {
		load_plugin_textdomain(
			Habakiri_Coco_Config::NAME,
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);

		add_filter(
			'habakiri_custom_background_defaults',
			array( $this, 'habakiri_custom_background_defaults' )
		);
	}

	/**
	 * 初期化処理
	 */
	public function init() {
		add_filter(
			'habakiri_theme_mods_defaults',
			array( $this, 'habakiri_theme_mods_defaults' )
		);

		add_filter(
			'mce_css',
			array( $this, 'mce_css' )
		);

		add_filter(
			'habakiri_post_thumbnail_size',
			array( $this, 'habakiri_post_thumbnail_size' )
		);

		add_filter(
			'theme_mod_page_header_bg_color',
			array( $this, 'theme_mod_page_header_bg_color' )
		);

		add_filter(
			'theme_mod_blog_template',
			array( $this, 'theme_mod_blog_template' )
		);

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'wp_enqueue_scripts' )
		);

		add_filter(
			'post_class',
			array( $this, 'post_class_for_archive' )
		);

		add_action(
			'customize_register',
			array( $this, 'customize_register' ),
			99999
		);

		add_filter(
			'dynamic_sidebar_params',
			array( $this, 'dynamic_sidebar_params' )
		);
	}

	/**
	 * CSS の読み込み
	 */
	public function wp_enqueue_scripts() {
		$url = plugins_url( Habakiri_Coco_Config::NAME );
		wp_enqueue_style(
			Habakiri_Coco_Config::NAME,
			$url . '/style.min.css',
			array( 'habakiri' )
		);
	}

	/**
	 * エディタに CSS を適用
	 *
	 * @param string $mce_css CSS のURL
	 * @return string
	 */
	public function mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}
		$mce_css .= get_stylesheet_directory_uri() . '/editor-style.min.css';
		return $mce_css;
	}

	/**
	 * サムネイルサイズ
	 *
	 * @param string $size
	 * @return string
	 */
	public function habakiri_post_thumbnail_size( $size ) {
		if ( is_archive() || is_home() ) {
			return 'large';
		}
		return $size;
	}

	/**
	 * アーカイブページで記事を囲むクラスを変更
	 *
	 * @param array $classes
	 * @return array
	 */
	public function post_class_for_archive( $classes ) {
		if ( is_archive() || is_home() ) {
			$classes[] = 'col-md-4';
		}
		return $classes;
	}

	/**
	 * ヘッダー背景色
	 *
	 * @param string $mod
	 * @return string
	 */
	public function theme_mod_header_bg_color( $mod ) {
		return '#fafafa';
	}

	/**
	 * ページヘッダー背景色
	 *
	 * @param string $mod
	 * @return string
	 */
	public function theme_mod_page_header_bg_color( $mod ) {
		return '#fafafa';
	}

	/**
	 * ブログテンプレート
	 *
	 * @param string $mod
	 * @return string
	 */
	public function theme_mod_blog_template( $mod ) {
		return 'full-width-fixed';
	}

	/**
	 * デフォルトのテーマオプションを定義して返す
	 *
	 * @param array $args
	 * @return array
	 */
	public function habakiri_theme_mods_defaults( $args ) {
		return shortcode_atts( $args, array(
			'page_header_text_color'   => '#333',
			'header_bg_color'          => '#fafafa',
			'link_color'               => '#09afdf',
			'link_hover_color'         => '#0794BD',
			'gnav_bg_color'            => '#fafafa',
			'gnav_link_hover_color'    => '#0794BD',
			'gnav_link_bg_color'       => '#fafafa',
			'gnav_link_bg_hover_color' => '#fafafa',
			'footer_bg_color'          => '#0794BD',
			'footer_text_color'        => '#fff',
			'footer_link_color'        => '#fff',
		) );
	}

	public function habakiri_custom_background_defaults( $args ) {
		return array(
			'default-color' => '#fafafa',
		);
	}

	/**
	 * Customizer の設定
	 *
	 * @param WP_Customizer $wp_customize
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->remove_control( 'page_header_bg_color' );
		$wp_customize->remove_control( 'blog_template' );
	}

	/**
	 * 検索、404、固定ページ ( page.php, templates/left-sidebar.php ) のとき以外は
	 * フッターのカラム設定にあわせてカラムを分割する
	 *
	 * @param array $params ウィジェットエリア設定の配列
	 * @return array
	 */
	public function dynamic_sidebar_params( $params ) {
		global $template;
		$template_name = basename( $template );
		if ( !is_home() && !is_archive() && !is_single() && !is_page() || in_array( $template_name, array( 'left-sidebar.php', 'page.php' ) ) ) {
			return $params;
		}
		if ( isset( $params[0]['id'] ) && $params[0]['id'] === 'sidebar' ) {
			$class = Habakiri::get( 'footer_columns' );
			$params[0]['before_widget'] = str_replace(
				'class="widget',
				'class="' . $class . ' widget',
				$params[0]['before_widget']
			);
		}
		return $params;
	}
}

$Habakiri_Coco = new Habakiri_Coco();
