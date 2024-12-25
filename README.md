# Round Robin Owners Bundle

**Version:** 1.0.0  
**Author:** Your Name  
**Author URL:** [https://deertechnology.org](https://deertechnology.org)

## Description

Assigns contacts to multiple owners in a round-robin fashion within Mautic campaigns. Optionally sends notification emails to owners upon assignment.

## Installation

1. **Download the Plugin:**
   - Clone this repository or download the ZIP file.

2. **Upload to Mautic:**
   - Log in to your Mautic instance with administrative privileges.
   - Navigate to **Settings > Plugins**.
   - Click on **Upload Plugin** and select the `RoundRobinOwnersBundle.zip` file.
   - After uploading, locate `RoundRobinOwnersBundle` in the plugin list.
   - Click **Install/Upgrade**.
   - Enable the plugin.

3. **Create Email Template:**
   - Navigate to **Channels > Emails**.
   - Create a new email with the **Alias** set to `owner-assignment`.
   - Design the email content to inform the owner about the new contact assignment.
   - **Publish** the email.

## Usage

1. **Create or Edit a Campaign:**
   - Navigate to **Campaigns**.
   - Create a new campaign or edit an existing one.

2. **Add the Custom Action:**
   - In the campaign builder, add a new action.
   - Search for **Round Robin Owners** and select it.

3. **Configure the Action:**
   - **Select Owners:** Choose multiple owners from the existing Mautic users.
   - **Send Email to Owner:** Toggle the checkbox if you want to send emails upon assignment.

4. **Save and Activate the Campaign:**
   - Save your campaign settings.
   - Activate the campaign.

5. **Test the Campaign:**
   - Add contacts to the campaign and observe if they are being assigned to owners in a round-robin fashion.
   - Check if emails are being sent to the owners when the toggle is enabled.

## Troubleshooting

- **Plugin Not Detected:**
  - Ensure the directory structure is correct.
  - Verify `composer.json` and `plugin.yml` are correctly formatted.
  - Check file permissions.
  - Review Mautic's logs for errors.

- **Owners Not Being Assigned:**
  - Ensure selected owners are active in Mautic.
  - Verify the email template with alias `owner-assignment` exists and is published.

- **Emails Not Sending:**
  - Check Mautic’s email settings.
  - Ensure the `send_email` toggle is enabled in the campaign action.

## License

MIT License
