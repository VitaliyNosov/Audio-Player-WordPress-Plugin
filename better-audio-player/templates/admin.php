<div class="wrap">
    <h1>Better Audio Player Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('bap_options'); ?>
        <?php do_settings_sections('bap_options'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Player Position</th>
                <td>
                    <select name="bap_player_position">
                        <option value="bottom" <?php selected(get_option('bap_player_position'), 'bottom'); ?>>Bottom</option>
                        <option value="top" <?php selected(get_option('bap_player_position'), 'top'); ?>>Top</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Accent Color</th>
                <td>
                    <input type="color" name="bap_accent_color" value="<?php echo esc_attr(get_option('bap_accent_color', '#ef6dbc')); ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Player Color</th>
                <td>
                    <input type="color" name="bap_player_color" value="<?php echo esc_attr(get_option('bap_player_color', '#1e2125')); ?>" />
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>