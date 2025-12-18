# Directorist - Seller Verification

Adds **seller verification fields** to WordPress user profiles and exposes a **Documents** tab in the Directorist user dashboard where sellers can upload/select verification documents using the **WordPress Media Uploader**.

- **Plugin URI**: `https://wpxplore.com/tools/directorist-seller-verification`
- **Author**: wpXplore (`https://wpxplore.com/`)

## Requirements

- WordPress 5.2+
- Directorist plugin active

## Installation

1. Copy the plugin folder to:
   - `wp-content/plugins/directorist-seller-verification/`
2. Activate **Directorist - Seller Verification** from **Plugins → Installed Plugins**.

## What it adds

### 1) Admin user profile fields (wp-admin)

On **Users → Edit User** and **Users → Profile**:

- **Seller Verification** section
  - **Seller document type** (dropdown)
  - **Upload documents** (Front + Back) via WordPress media uploader
  - **Verified** checkbox: “Vefiry the seller”

### 2) Directorist dashboard tab (frontend)

Adds a Directorist dashboard tab:

- **My Documents**
  - Select **Document Type**
  - Upload/select **Front** and **Back** documents using WordPress media modal
  - **Save** via AJAX (no page reload)

> Note: This plugin template directory is `templates/` (spelling matches the current plugin code).

## Stored user meta keys

These values are saved as user meta:

- **`_seller_document_type`**: string key (document type slug)
- **`_seller_document_front`**: attachment ID (int)
- **`_seller_document_back`**: attachment ID (int)
- **`verify_seller`**: `'yes'` or `'no'`

## Security & validation

- Nonces are used for dashboard AJAX saves.
- Uploads use WordPress media/attachments (no raw file upload handling on the dashboard).
- Attachment validation includes:
  - must be an attachment post type
  - current user must be able to edit the attachment (`edit_post`)
  - allowed extensions: `jpg`, `jpeg`, `png`, `gif`, `pdf`

## Developer notes

### Key files

- `directorist-seller-verification.php`: plugin bootstrap, constants, enqueues
- `inc/class-admin.php`: user profile fields (wp-admin) + media uploader enqueue
- `inc/class-dashboard.php`: Directorist dashboard tab + AJAX endpoint + `wp_enqueue_media()`
- `templates/tab-documents.php`: dashboard tab UI
- `assets/js/admin.js`: wp-admin media uploader behavior
- `assets/js/dashboard.js`: frontend dashboard media uploader + AJAX save
- `assets/css/dashboard.css`: dashboard tab styling (no inline CSS)

### AJAX

- **Action**: `directorist_sv_save_documents`
- **Endpoint**: `admin-ajax.php`
- **Response**: JSON (`success`/`error`) with a message

### Filters

- `directorist_seller_verification_document_types`: filter document type options.

## License

GPL v2 or later


