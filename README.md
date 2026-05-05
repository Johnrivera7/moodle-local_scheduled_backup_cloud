# Scheduled backup (cloud) — Moodle local plugin

**Component:** `local_scheduled_backup_cloud`  
**Folder (in Moodle):** `local/scheduled_backup_cloud/`  
**English UI name:** Scheduled backup (cloud) — aligns with core **Scheduled backup**.

Uploads course backup files (`.mbz`) produced by Moodle’s **scheduled backup** task to **Google Drive** using OAuth 2.0. Backup **timing and `.mbz` generation** are configured only on the core page **Site administration → Courses → Backups → Scheduled backup**. At the **bottom** of that page, this plugin adds a short section with a link to **OAuth**, remote paths, and upload options.

## Requirements

- Moodle **4.5+** (`$plugin->requires` in `version.php`)
- PHP compatible with your Moodle version
- Cron enabled  
- Google Cloud: OAuth **Web application** client, Google Drive API enabled, redirect URI registered (see plugin settings for the exact URL)

## Installation

1. Copy this directory into your Moodle tree as **`moodle/local/scheduled_backup_cloud/`** (folder name must match).
2. Visit **Site administration → Notifications** and complete the upgrade.
3. Configure **Google OAuth** and enable the scheduled task under **Site administration → Courses → Backups → Scheduled backup (cloud)** (or `admin/settings.php?section=local_scheduled_backup_cloud`). Use **Scheduled backup** for when backups run and where `.mbz` files are stored locally.

## Repository

Suggested GitHub repository name: **`moodle-local_scheduled_backup_cloud`**  
Short alternative: **`scheduled_backup_cloud`**

Clone URL pattern: `https://github.com/<your-account>/moodle-local_scheduled_backup_cloud.git`

## Remote file naming

Pattern: `shortname_courseid_{id}_YYYYMMDD-HHMMSS.mbz` (the substring `courseid` is literal in the filename).

## Microsoft / SharePoint

Microsoft Graph upload is not implemented in this version; only Google Drive is supported end-to-end.

## License

GPL v3 or later (see `version.php`).

## Author

John Rivera Gonzalez
