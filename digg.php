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

function _digg_option($option) {
    return get_option('digg_setting_' . $option);
}

function _digg_checked($option, $value) {
    if (_digg_option($option) == $value) {
        echo 'checked="checked"';
    }
}

function _digg_selected($option, $value) {
    if (_digg_option($option) == $value) {
        echo 'selected';
    }
}

function _digg_topics() {
    if (function_exists('fsockopen')) {
        $socket = @fsockopen('services.digg.com', 80);
        if (!$socket) {
            return null;
        }

        fwrite($socket, "GET /1.0/endpoint?method=topic.getAll&type=json HTTP/1.1\r\n");
        fwrite($socket, "Host: services.digg.com\r\n");
        fwrite($socket, "Connection: close\r\n");
        fwrite($socket, "User-Agent: PHP Digg Wordpress plugin\r\n\r\n");
        $data = '';
        while (!feof($socket)) {
            $data .= fgets($socket, 128);
        }
        fclose($socket);
        $data = @json_decode(end(explode("\r\n\r\n", $data)), true);
        if (!is_array($data) || !isset($data['topics'])) {
            return null;
        }

        return $data['topics'];
    }

    return null;
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
                <th scope="row"></th>
                <td>
                    <fieldset>
                        <input type="checkbox" value="1" <?php _digg_checked('button_enabled', 1); ?> name="digg_setting_button_enabled" id="digg_setting_button_enabled"/>
                        <label for="digg_setting_button_enabled">Enable the Digg button</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Button Type</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_type">
                            <option value="medium" <?php _digg_selected('button_type', 'medium'); ?>>Medium</option>
                            <option value="compact" <?php _digg_selected('button_type','compact'); ?>>Compact</option>
                            <option value="large" <?php _digg_selected('button_type', 'large'); ?>>Large</option>
                        </select>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Display</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_position">
                            <option value="after" <?php _digg_selected('button_position', 'after'); ?>>After post</option>
                            <option value="before" <?php _digg_selected('button_position', 'before'); ?>>Before post</option>
                            <option value="function" <?php _digg_selected('button_position', 'function'); ?>>PHP Function in template</option>
                        </select>
                    </fieldsetp>
            <tr>
                <th scope="row">Alignment</th>
                <td>
                    <fieldset>
                        <input type="radio" value="right" name="digg_setting_button_alignment" id="digg_setting_button_alignment_right" <?php _digg_checked('button_alignment', 'right'); ?>/>
                        <label for="digg_setting_button_alignment_right">Right of content</label>
                        <br />
                        <input type="radio" value="left" name="digg_setting_button_alignment" id="digg_setting_button_alignment_left" <?php _digg_checked('button_alignment', 'left'); ?>/>
                        <label for="digg_setting_button_alignment_left">Left of content</label>
                    </fieldset>
               </td>
            </tr>
            <tr>
                <th scope="row">Custom style (optional)</th>
                <td>
                    <fieldset>
                        <input type="text" value="<?php echo get_option('digg_setting_button_style'); ?>" name="digg_setting_button_style"/>
                        <br />
                        <label>CSS style for the Digg button e.g. <code>margin-right: 10px;</code></label>
                    </fieldset>
                </td>
            </tr>
<?php
            $topics = _digg_topics();
            if (is_array($topics)) {
?>
            <tr>
                <th scope="row">Default topic</th>
                <td>
                    <fieldset>
                        <select name="digg_setting_button_topic">
                            <option value="">(none)</option>
<?php
                        foreach ($topics as $topic) {
?>
                            <option value="<?php echo $topic['short_name']; ?>" <?php _digg_selected('button_topic', $topic['short_name']); ?>><?php echo $topic['name']; ?></option>
<?php
                        }
?>
                        </select>
                        <br />
                        <label>You can choose a default topic that will be selected if your post gets submitted via the Digg button</label>
                    </fieldset>
                </td>
            </tr>
<?php
            }
?>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
        </form>
        </div>
<?php
}

function digg_widget_options() {
    $base = get_option('siteurl') . "/wp-content/plugins/digg/";
    if (isset($_GET['updated'])) {
?>
        <div id="message" style="height: 302px; background: #fff url(<?php echo $base; ?>img/now.gif) no-repeat" class="updated fade">
        </div>
        <div>
            <p><a href="?page=digg.php?widget"><< Back to creating a widget</a>
        </div>
<?php
    } else {
?>
<script type="text/javascript">
    var _digg_root = "<?php echo $base; ?>";
    var _digg_img_path = "<?php echo $base; ?>img/";
</script>

<link rel="stylesheet" href="<?php echo $base; ?>widget/diggwidget.css" />
<script src="<?php echo $base; ?>jquery-1.4.1.min.js" type="text/javascript"></script>
<script src="<?php echo $base; ?>widget/diggwidget.js" type="text/javascript"></script>
<script src="<?php echo $base; ?>widget_generator.js" type="text/javascript"></script>

<form method="post" action="options.php">
<?php
    settings_fields('digg-settings');
?>
    <input type="hidden" id="widget-code" value='<?php echo get_option('digg_setting_widget_snippet'); ?>' name="digg_setting_widget_snippet" />

    <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </p>
</form>

<?php
    }
}

function digg_fetch_button() {
    $button = '';
    if (!get_option('digg_setting_button_enabled')) {
        return $button;
    }

    $id = get_the_ID();
    if (!is_numeric($id)) {
        return $button;
    }

    $post = get_post($id);
    if (!is_object($post)) {
        return $button;
    }


    $url = get_page_link();
    if (get_post_status($record->ID) == 'publish') {
        $url = get_permalink();
    }

    $style = 'float: left; padding-right: 5px;';
    if (_digg_option('button_alignment') == 'right') {
        $style = 'float: right; padding-left: 5px;';
    }
    $style .= ' ' . _digg_option('button_style');

    $class = '';
    if (_digg_option('button_type') == 'medium') {
        $class = ' DiggThisButtonMedium';
    }

    $button .= '<div class="digg_button" style="' . $style . '">';
    $button .= '<a class="DiggThisButton' . $class . '" href="' . $url . '" rel="external" rev=", ' . _digg_option('button_topic') . '">';
    $button .= '<span style="display: none;">';
    if (empty($post->post_excerpt)) {
        # Take off the last word, Jeff style!
        $content = explode(' ', substr($post->post_content, 0, 300));
        array_pop($content);
        $button .= implode(' ', $content) . '...';
    } else {
        $button .= $post->post_excerpt;
    }
    $button .= '</span>';

    switch (_digg_option('button_type')) {
        case 'normal':
            $button .= '';
            break;
        case 'compact':
            $button .= '<img src="http://widgets.digg.com/img/button/diggThisCompact.png" alt="DiggThis" />';
            break;
        case 'icon':
            $button .= '<img src="http://digg.com/img/diggThisIcon.gif" height="16" width="16"  alt="DiggThis" />';
            break;
        default:
            break;
    }

    $button .= '</a>';
    $button .= '</div>';

    return $button;
}

function add_digg_button_script()
{
    if (!get_option('digg_setting_button_enabled')) {
        return;
    }

    echo '<script src="http://widgets.digg.com/buttons.js" type="text/javascript"></script>';
}

function add_digg_button_to_a_post($post)
{
    $position = _digg_option('button_position');
    if ($position == 'before') {
        $post = digg_fetch_button() . $post;
    } else if ($position == 'after') {
        $post = $post . digg_fetch_button();
    }

    return $post;
}

function digg_widget() {
    echo htmlspecialchars_decode(get_option('digg_setting_widget_snippet'));
}

function digg_widget_setup() {
    register_sidebar_widget('The Digg widget', 'digg_widget');
}

function digg_activate() {
    # Button settings
    add_option('digg_setting_button_enabled', '1');
    add_option('digg_setting_button_location', 'before');
    add_option('digg_setting_button_type', 'normal');
    add_option('digg_setting_button_position', 'before');
    add_option('digg_setting_button_alignment', 'left');
    add_option('digg_setting_button_style', '');
    add_option('digg_setting_button_topic', '');

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
    register_setting('digg-settings', 'digg_setting_button_position');
    register_setting('digg-settings', 'digg_setting_button_alignment');
    register_setting('digg-settings', 'digg_setting_button_style');
    register_setting('digg-settings', 'digg_setting_button_topic');

    # Widget settings
    register_setting('digg-settings', 'digg_setting_widget_enabled');
    register_setting('digg-settings', 'digg_setting_widget_snippet');
}

if(is_admin()){
    add_action('admin_menu', 'digg_admin_menus');
    add_action('admin_init', 'digg_register_settings');
}

add_filter('the_content', 'add_digg_button_to_a_post');
add_filter('get_footer', 'add_digg_button_script');
add_action('widgets_init','digg_widget_setup');

register_activation_hook( __FILE__, 'digg_activate');

?>
