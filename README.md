
# WPTelegram-Post-Notifier

**WPTelegram-Post-Notifier** is a WordPress plugin that sends notifications to a Telegram channel when a new post is published on your WordPress site. This can be useful for keeping your Telegram audience updated with your latest content automatically.

## Features

- Sends a notification to a specified Telegram channel whenever a new post is published.
- Easy configuration through the WordPress admin panel.
- Customizable message format for notifications.

## Installation

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/CryptoJoma/WPTelegram-Post-Notifier.git
   ```

2. **Upload the Plugin to WordPress:**

   - Compress the `WPTelegram-Post-Notifier` folder into a `.zip` file.
   - Go to the WordPress admin panel.
   - Navigate to **Plugins > Add New**.
   - Click **Upload Plugin** and choose the `.zip` file you created.
   - Click **Install Now** and then **Activate**.

## Configuration

1. **Obtain Your Telegram Bot Token:**

   - Create a new bot on Telegram by talking to the [BotFather](https://core.telegram.org/bots#botfather).
   - Note down the bot token provided.

2. **Get Your Telegram Channel ID:**

   - Add your bot to your Telegram channel.
   - Send a message to your channel.
   - Use the Telegram API to get the channel ID by making a request to `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`.

3. **Configure the Plugin in WordPress:**

   - Go to **Settings > WP Telegram Post Notifier** in your WordPress admin panel.
   - Enter your Telegram Bot Token and Channel ID.
   - Customize the notification message if needed.
   - Save your settings.

## Usage

1. **Create a New Post:**

   - Write a new post or edit an existing one in WordPress.
   - Publish or update the post.

2. **Receive Notifications:**

   - Once the post is published, a notification will be sent to the specified Telegram channel with the details of the new post.

## Customizing Notifications

You can customize the notification message by adjusting the message format in the plugin settings. Use placeholders to include dynamic content from your posts:

- `{post_title}` - The title of the post.
- `{post_url}` - The URL of the post.
- `{post_excerpt}` - The excerpt of the post.

## Troubleshooting

- **Bot Not Sending Messages:** Ensure that the bot has been added to the channel and has permissions to send messages.
- **Invalid Token or Channel ID:** Double-check that you've entered the correct bot token and channel ID in the plugin settings.

## Contributing

Feel free to submit pull requests or open issues if you encounter any problems or have suggestions for improvements.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any questions or support, please contact [CryptoJoma](mailto:coffee@joma.dev).
