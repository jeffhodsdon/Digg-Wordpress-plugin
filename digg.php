<?php
/*
Plugin Name: Digg
Plugin URI: http://digg.com/
Description: Digg plugin.
Version: 0.1.1
Author: Jeff Hodsdon
Author URI: http://jeffhodsdon.com/
*/

function digg_admin_menus() {
    add_menu_page('Digg', 'Digg', 8, basename(__FILE__), 'digg_button_options');
    add_submenu_page(basename(__FILE__), 'Button', 'Button', 8,
                     basename(__FILE__), 'digg_button_options');
    add_submenu_page(basename(__FILE__), 'Widget', 'Widget', 8,
                     basename(__FILE__) . '?widget', 'digg_widget_options');
}

function digg_button_options() {
?>
        <div class="wrap">
        <div class="icon32" id="icon-options-general"><br/></div><h2>Digg plugin settings: button</h2>
        <?php if (isset($_GET['updated'])) { echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>'; } ?>
        <p>This is the settings page for the Digg button.</p>
        <form method="post" action="options.php">
<?php
    if(function_exists('settings_fields')){
        settings_fields('digg-settings');
    } else {
        wp_nonce_field('update-options');
?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="digg_button_setting_enabled" />
<?php 
    }
?>
        <table class="form-table">
            <tr>
                <th scope="row">Display</th>
                <td>
                    <p>
                        <input type="checkbox" value="1" <?php if (get_option('digg_setting_button_enabled')) echo 'checked="checked"'; ?> name="digg_setting_button_enabled" id="digg_button_setting_enabled"/>
                        <label for="digg_button_setting_enabled">Enable the Digg button</label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">Type</th>
                <td>
                    <p>
                        <input type="radio" value="normal" <?php if (get_option('digg_setting_button_type') == 'normal') echo 'checked="checked"'; ?> name="digg_setting_button_type" id="digg_button_setting_type_normal"/>
                        <label for="digg_button_setting_type_normal">Normal Digg button</label>
                    </p>
                    <p>
                        <input type="radio" value="compact" <?php if (get_option('digg_setting_button_type') == 'compact') echo 'checked="checked"'; ?> name="digg_setting_button_type" id="digg_button_setting_type_compact"/>
                        <label for="digg_button_setting_type_compact">Compact Digg button</label>
                    </p>
                    <p>
                        <input type="radio" value="icon" <?php if (get_option('digg_setting_button_type') == 'icon') echo 'checked="checked"'; ?> name="digg_setting_button_type" id="digg_button_setting_type_icon"/>
                        <label for="digg_button_setting_type_icon">Icon Digg button</label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">Syle</th>
                <td>
                    <p>
                        <input type="text" value="<?php echo get_option('digg_setting_button_style'); ?>" name="digg_setting_button_style" id="digg_button_setting_style"/>
                        <label for="digg_button_setting_type_normal">CSS style for the Digg button</label>
                    </p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
        </form>
        </div>
<?php
}


function digg_widget_options() {

}

function add_digg_button_to_a_post($post)
{
    if (!get_option('digg_setting_button_enabled')) {
        return $post;
    }

    $record = get_post(get_the_ID());

    $post .= '<div class="digg_button" style="' . get_option('digg_setting_button_style') . '">';
    $post .= '<a class="DiggThisButton" href="http://digg.com/submit?url=' . get_page_link() . '&title=' . urlencode($record->post_title) . '" rel="external" rev="news, " style="background-color: ' . get_option('digg_setting_button_background') . '">';
    $post .= '<span style="display: none;">[bodytext]</span>';

    switch (get_option('digg_setting_button_type')) {
        case 'normal':
            $post .= '<img src="http://digg.com/img/diggThis.png" height="80" width="52"  alt="DiggThis" />';
            break;
        case 'compact':
            $post .= '<img src="http://digg.com/img/diggThisCompact.png" height="18" width="120" alt="DiggThis" />';
            break;
        case 'icon':
            $post .= '<img src="http://digg.com/img/diggThisIcon.gif" height="16" width="16"  alt="DiggThis" />';
            break;
        default:
            break;
    }

    $post .= '</a>';
    $post .= '<script src="http://digg.com/tools/diggthis.js" type="text/javascript"></script>';
    $post .= '</div>';
    $post .= '<div style="clear:both;"></div>';

    return $post;
}

function digg_activate() {
    # Button settings
    add_option('digg_setting_button_enabled', '1');
    add_option('digg_setting_button_location', 'before');
    add_option('digg_setting_button_type', 'normal');
    add_option('digg_setting_button_style', 'float: right; margin-left: 10px;');

    # Widget settings
    add_option('digg_setting_widget_enabled', '0');
    add_option('digg_setting_widget_snippet', '');
}

function digg_register_settings() {
    if (!function_exists('register_setting')) {
        return;
    }

    # Button settings
    register_setting('digg-settings', 'digg_setting_button_enabled');
    register_setting('digg-settings', 'digg_setting_button_location');
    register_setting('digg-settings', 'digg_setting_button_type');
    register_setting('digg-settings', 'digg_setting_button_style');

    # Widget settings
    register_setting('digg-settings', 'digg_setting_widget_enabled');
    register_setting('digg-settings', 'digg_setting_widget_snippet');
}

if(is_admin()){
    add_action('admin_menu', 'digg_admin_menus');
    add_action('admin_init', 'digg_register_settings');
}

add_filter('the_content', 'add_digg_button_to_a_post');

register_activation_hook( __FILE__, 'digg_activate');

?>
