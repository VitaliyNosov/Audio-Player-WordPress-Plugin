<?php
/*
Plugin Name: Better Audio Player
Plugin URI: http://example.com/better-audio-player
Description: A customizable audio player for WordPress
Version: 1.0
Author: Your Name
Author URI: http://example.com
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BetterAudioPlayer {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_player'));
        add_shortcode('better_audio_player', array($this, 'player_shortcode'));
        
        // Include admin page
        require_once plugin_dir_path(__FILE__) . 'admin-page.php';
    }

    public function register_post_type() {
        register_post_type('bap_track', array(
            'labels' => array(
                'name' => 'Tracks',
                'singular_name' => 'Track',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'custom-fields'),
        ));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('better-audio-player', plugin_dir_url(__FILE__) . 'css/player-styles.css');
        wp_enqueue_script('better-audio-player', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
        
        $appearance_settings = get_option('better_audio_player_appearance_settings');
        $custom_css = "
            .player { background-color: " . ($appearance_settings['background_color'] ?? '#1e2125') . "; }
            .player h1, .player h2, .player h4 { color: " . ($appearance_settings['text_color'] ?? '#ffffff') . "; }
            #elapsed, .volume, #play:hover g, #pause:hover rect, .expend svg:hover g polygon, input[type='range']::-webkit-slider-thumb:hover { 
                background-color: " . ($appearance_settings['accent_color'] ?? '#ef6dbc') . "; 
            }
        ";
        wp_add_inline_style('better-audio-player', $custom_css);

        // Добавьте эти строки для передачи данных в JavaScript
        $tracks = get_posts(array(
            'post_type' => 'bap_track',
            'numberposts' => -1,
        ));

        $tracks_data = array_map(function($track) {
            return array(
                'title' => $track->post_title,
                'artist' => get_post_meta($track->ID, 'bap_artist', true),
                'audio' => get_post_meta($track->ID, 'bap_audio_url', true),
                'cover' => get_post_meta($track->ID, 'bap_cover_url', true),
            );
        }, $tracks);

        wp_localize_script('better-audio-player', 'bapData', array(
            'tracks' => $tracks_data,
            'accentColor' => $appearance_settings['accent_color'] ?? '#ef6dbc',
            'playerColor' => $appearance_settings['background_color'] ?? '#1e2125',
            'textColor' => $appearance_settings['text_color'] ?? '#ffffff',
        ));
    }

    public function render_player() {
        $general_settings = get_option('better_audio_player_general_settings');
        $tracks_settings = get_option('better_audio_player_tracks_settings');
        $appearance_settings = get_option('better_audio_player_appearance_settings');

        include(plugin_dir_path(__FILE__) . 'templates/player.php');
    }

    public function player_shortcode($atts) {
        ob_start();
        $this->render_player();
        return ob_get_clean();
    }
}

// Initialize the plugin
function better_audio_player_init() {
    BetterAudioPlayer::get_instance();
}
add_action('plugins_loaded', 'better_audio_player_init');