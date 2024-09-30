<?php
function better_audio_player_admin_menu() {
    add_menu_page(
        'Better Audio Player',
        'Better Audio Player',
        'manage_options',
        'better-audio-player-settings',
        'better_audio_player_settings_page',
        'dashicons-playlist-audio'
    );
}
add_action('admin_menu', 'better_audio_player_admin_menu');

function better_audio_player_settings_page() {
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    ?>
    <div class="wrap">
        <h1>Better Audio Player Settings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=better-audio-player-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=better-audio-player-settings&tab=tracks" class="nav-tab <?php echo $active_tab == 'tracks' ? 'nav-tab-active' : ''; ?>">Tracks</a>
            <a href="?page=better-audio-player-settings&tab=appearance" class="nav-tab <?php echo $active_tab == 'appearance' ? 'nav-tab-active' : ''; ?>">Appearance</a>
        </h2>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php
            if ($active_tab == 'general') {
                settings_fields('better_audio_player_general_settings');
                do_settings_sections('better_audio_player_general_settings');
            } elseif ($active_tab == 'tracks') {
                settings_fields('better_audio_player_tracks_settings');
                do_settings_sections('better_audio_player_tracks_settings');
            } elseif ($active_tab == 'appearance') {
                settings_fields('better_audio_player_appearance_settings');
                do_settings_sections('better_audio_player_appearance_settings');
            }
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function better_audio_player_settings_init() {
    // General Settings
    register_setting('better_audio_player_general_settings', 'better_audio_player_general_settings');
    add_settings_section('better_audio_player_general_section', 'General Settings', 'better_audio_player_general_section_callback', 'better_audio_player_general_settings');
    add_settings_field('player_position', 'Player Position', 'better_audio_player_player_position_callback', 'better_audio_player_general_settings', 'better_audio_player_general_section');

    // Tracks Settings
    register_setting('better_audio_player_tracks_settings', 'better_audio_player_tracks_settings');
    add_settings_section('better_audio_player_tracks_section', 'Tracks Settings', 'better_audio_player_tracks_section_callback', 'better_audio_player_tracks_settings');
    add_settings_field('tracks', 'Manage Tracks', 'better_audio_player_tracks_callback', 'better_audio_player_tracks_settings', 'better_audio_player_tracks_section');

    // Appearance Settings
    register_setting('better_audio_player_appearance_settings', 'better_audio_player_appearance_settings');
    add_settings_section('better_audio_player_appearance_section', 'Appearance Settings', 'better_audio_player_appearance_section_callback', 'better_audio_player_appearance_settings');
    add_settings_field('background_color', 'Background Color', 'better_audio_player_color_callback', 'better_audio_player_appearance_settings', 'better_audio_player_appearance_section', array('label_for' => 'background_color'));
    add_settings_field('text_color', 'Text Color', 'better_audio_player_color_callback', 'better_audio_player_appearance_settings', 'better_audio_player_appearance_section', array('label_for' => 'text_color'));
    add_settings_field('accent_color', 'Accent Color', 'better_audio_player_color_callback', 'better_audio_player_appearance_settings', 'better_audio_player_appearance_section', array('label_for' => 'accent_color'));
}
add_action('admin_init', 'better_audio_player_settings_init');

// Callback functions for sections
function better_audio_player_general_section_callback() {
    echo '<p>Configure general settings for the audio player.</p>';
}

function better_audio_player_tracks_section_callback() {
    echo '<p>Manage your audio tracks and their cover images here.</p>';
}

function better_audio_player_appearance_section_callback() {
    echo '<p>Customize the appearance of your audio player.</p>';
}

// Callback functions for fields
function better_audio_player_player_position_callback() {
    $options = get_option('better_audio_player_general_settings');
    $position = isset($options['player_position']) ? $options['player_position'] : 'bottom';
    ?>
    <select name="better_audio_player_general_settings[player_position]">
        <option value="bottom" <?php selected($position, 'bottom'); ?>>Bottom</option>
        <option value="top" <?php selected($position, 'top'); ?>>Top</option>
    </select>
    <?php
}

function better_audio_player_tracks_callback() {
    $tracks = get_posts(array(
        'post_type' => 'bap_track',
        'numberposts' => -1,
    ));
    ?>
    <div id="better-audio-player-tracks">
        <?php foreach ($tracks as $track) : 
            $audio_url = get_post_meta($track->ID, 'bap_audio_url', true);
            $cover_url = get_post_meta($track->ID, 'bap_cover_url', true);
        ?>
        <div class="track-item">
            <input type="text" name="track_title[]" value="<?php echo esc_attr($track->post_title); ?>" placeholder="Track Title">
            <input type="text" name="track_artist[]" value="<?php echo esc_attr(get_post_meta($track->ID, 'bap_artist', true)); ?>" placeholder="Artist">
            <input type="hidden" name="track_audio[]" value="<?php echo esc_attr($audio_url); ?>">
            <input type="hidden" name="track_cover[]" value="<?php echo esc_attr($cover_url); ?>">
            <span><?php echo basename($audio_url); ?></span>
            <?php if ($cover_url) : ?>
                <img src="<?php echo esc_url($cover_url); ?>" alt="Cover" style="max-width: 50px; max-height: 50px;">
            <?php else : ?>
                <button type="button" class="button add-cover">Add Cover</button>
            <?php endif; ?>
            <input type="hidden" name="track_id[]" value="<?php echo $track->ID; ?>">
            <button type="button" class="button remove-track">Remove</button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="add-track">Add New Track</button>
    <?php
    wp_localize_script('better-audio-player-admin', 'bapAdminData', array(
        'nonce' => wp_create_nonce('bap_track_nonce')
    ));
}

function better_audio_player_color_callback($args) {
    $options = get_option('better_audio_player_appearance_settings');
    $color = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '#000000';
    ?>
    <input type="color" id="<?php echo esc_attr($args['label_for']); ?>" name="better_audio_player_appearance_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($color); ?>">
    <?php
}

// Handle file upload
function better_audio_player_save_settings() {
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
        $upload_dir = wp_upload_dir();
        $file_name = basename($_FILES['audio_file']['name']);
        $target_file = $upload_dir['path'] . '/' . $file_name;

        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $target_file)) {
            $file_url = $upload_dir['url'] . '/' . $file_name;
            $options = get_option('better_audio_player_tracks_settings');
            $options['audio_url'] = $file_url;
            update_option('better_audio_player_tracks_settings', $options);
        }
    }
}
add_action('admin_init', 'better_audio_player_save_settings');

// Добавьте эти функции в конец файла

function bap_save_track() {
    check_ajax_referer('bap_track_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }

    $track_id = wp_insert_post(array(
        'post_title' => sanitize_text_field($_POST['title']),
        'post_type' => 'bap_track',
        'post_status' => 'publish',
    ));

    if ($track_id) {
        $audio_id = intval($_POST['audio_id']);
        $cover_id = !empty($_POST['cover_id']) ? intval($_POST['cover_id']) : 0;

        update_post_meta($track_id, 'bap_audio_url', wp_get_attachment_url($audio_id));
        update_post_meta($track_id, 'bap_audio_id', $audio_id);

        if ($cover_id) {
            update_post_meta($track_id, 'bap_cover_url', wp_get_attachment_url($cover_id));
            update_post_meta($track_id, 'bap_cover_id', $cover_id);
        }

        wp_send_json_success(array('track_id' => $track_id));
    } else {
        wp_send_json_error(array('message' => 'Failed to create track'));
    }
}
add_action('wp_ajax_bap_save_track', 'bap_save_track');

function bap_remove_track() {
    check_ajax_referer('bap_track_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }

    $track_id = intval($_POST['track_id']);
    $result = wp_delete_post($track_id, true);

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to remove track'));
    }
}
add_action('wp_ajax_bap_remove_track', 'bap_remove_track');

function better_audio_player_admin_enqueue_scripts($hook) {
    if ('toplevel_page_better-audio-player-settings' !== $hook) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('better-audio-player-admin', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'better_audio_player_admin_enqueue_scripts');

// Add this new function at the end of the file
function bap_update_cover() {
    check_ajax_referer('bap_track_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }

    $track_id = intval($_POST['track_id']);
    $cover_id = intval($_POST['cover_id']);

    if ($cover_id) {
        update_post_meta($track_id, 'bap_cover_url', wp_get_attachment_url($cover_id));
        update_post_meta($track_id, 'bap_cover_id', $cover_id);
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Invalid cover image'));
    }
}
add_action('wp_ajax_bap_update_cover', 'bap_update_cover');