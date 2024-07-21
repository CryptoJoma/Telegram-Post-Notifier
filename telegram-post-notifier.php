<?php
/*
Plugin Name: Telegram Post Notifier
Description: Posts to a Telegram channel when a new blog post is published.
Version: 1.0.4
Author: CryptoJoma
*/

/*
 * Plugin Name: Telegram Post Notifier
 * Plugin URI: http://wpfront.com/scroll-top-plugin/ 
 * Description: Posts to a Telegram channel when a new blog post is published.
 * Version: 2.21.0.4
 * Requires at least: 5.0
 * Requires PHP: 5.3
 * Author: CryptoJoma
 * Author URI: https://joma.dev
 * License: GPL v3 
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add settings menu to WP Admin
add_action('admin_menu', 'tpn_add_admin_menu');
add_action('admin_init', 'tpn_settings_init');

function tpn_add_admin_menu() {
    add_options_page('Telegram Post Notifier', 'Telegram Post Notifier', 'manage_options', 'telegram_post_notifier', 'tpn_options_page');
}

function tpn_settings_init() {
    register_setting('tpn_settings', 'tpn_options', 'tpn_options_validate');
    
    add_settings_section(
        'tpn_section', 
        __('Telegram Settings', 'wordpress'), 
        'tpn_settings_section_callback', 
        'tpn_settings'
    );
    
    add_settings_field(
        'tpn_api_token', 
        __('API Token', 'wordpress'), 
        'tpn_api_token_render', 
        'tpn_settings', 
        'tpn_section'
    );
    
    add_settings_field(
        'tpn_channel', 
        __('Channel ID', 'wordpress'), 
        'tpn_channel_render', 
        'tpn_settings', 
        'tpn_section'
    );

    add_settings_field(
        'tpn_message_template', 
        __('Message Template', 'wordpress'), 
        'tpn_message_template_render', 
        'tpn_settings', 
        'tpn_section'
    );
}

function tpn_api_token_render() {
    $options = get_option('tpn_options');
    ?>
    <input type="text" name="tpn_options[tpn_api_token]" value="<?php echo esc_attr($options['tpn_api_token'] ?? ''); ?>" />
    <?php
}

function tpn_channel_render() {
    $options = get_option('tpn_options');
    ?>
    <input type="text" name="tpn_options[tpn_channel]" value="<?php echo esc_attr($options['tpn_channel'] ?? ''); ?>" />
    <?php
}

function tpn_message_template_render() {
    $options = get_option('tpn_options');
    ?>
    <textarea name="tpn_options[tpn_message_template]" rows="5" cols="50"><?php echo esc_textarea($options['tpn_message_template'] ?? "New post published: {post_title}\n{post_url}"); ?></textarea>
    <p><?php _e('Use {post_title}, {post_url}, and {post_thumbnail_url} as placeholders.', 'wordpress'); ?></p>
    <?php
}

function tpn_settings_section_callback() {
    echo __('Enter your Telegram API token, channel ID, and message template below.', 'wordpress');
}

function tpn_options_page() {
    ?>
    <form action="options.php" method="post">
        <?php
        settings_fields('tpn_settings');
        do_settings_sections('tpn_settings');
        submit_button();
        ?>
    </form>
    <?php
}

function tpn_options_validate($input) {
    $output = array();
    $output['tpn_api_token'] = sanitize_text_field($input['tpn_api_token']);
    $output['tpn_channel'] = sanitize_text_field($input['tpn_channel']);
    $output['tpn_message_template'] = wp_kses_post($input['tpn_message_template']);
    
    if (empty($output['tpn_api_token']) || empty($output['tpn_channel'])) {
        add_settings_error(
            'tpn_options',
            'tpn_options_error',
            __('API Token and Channel ID are required.', 'wordpress'),
            'error'
        );
    }
    
    return $output;
}

// Post to Telegram when a new post is published
add_action('transition_post_status', 'tpn_notify_telegram_on_publish', 10, 3);

function tpn_notify_telegram_on_publish($new_status, $old_status, $post) {
    if ($old_status !== 'publish' && $new_status === 'publish' && $post->post_type === 'post') {
        tpn_notify_telegram($post->ID);
    }
}

function tpn_notify_telegram($post_id) {
    $options = get_option('tpn_options');
    $api_token = $options['tpn_api_token'] ?? '';
    $channel_id = $options['tpn_channel'] ?? '';
    $message_template = $options['tpn_message_template'] ?? "New post published: {post_title}\n{post_url}";
    
    if (empty($api_token) || empty($channel_id)) {
        error_log("Telegram API token or channel ID is missing.");
        return;
    }
    
    $post_title = get_the_title($post_id);
    $post_url = get_permalink($post_id);
    $post_thumbnail_id = get_post_thumbnail_id($post_id);
    $post_thumbnail_url = $post_thumbnail_id ? wp_get_attachment_image_url($post_thumbnail_id, 'full') : '';

    $message = str_replace(
        array('{post_title}', '{post_url}', '{post_thumbnail_url}'),
        array($post_title, $post_url, $post_thumbnail_url),
        $message_template
    );

    $url = "https://api.telegram.org/bot$api_token/sendMessage";
    $response = wp_remote_post($url, array(
        'method'    => 'POST',
        'body'      => array(
            'chat_id' => $channel_id,
            'text'    => $message,
        ),
    ));
    
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("Telegram API request failed: $error_message");
        return;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code == 429) {
        error_log("Rate limit exceeded. Telegram API returned 429.");
        return;
    }
    
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    
    if (!$response_data['ok']) {
        $error_description = $response_data['description'];
        error_log("Telegram API error: $error_description");
    }
}

// Deactivation cleanup
function tpn_plugin_deactivate() {
    delete_option('tpn_options');
    tpn_clear_scheduled_events();
}
register_deactivation_hook(__FILE__, 'tpn_plugin_deactivate');

function tpn_clear_scheduled_events() {
    $timestamp = wp_next_scheduled('tpn_cron_event');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'tpn_cron_event');
    }
}
?>
