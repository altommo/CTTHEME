# CustomTube Theme (No Plugin Import)

This is a modified version of the CustomTube theme with the import functionality removed and moved to a separate plugin.

## Important Notes

1. **Plugin Dependency**: To use video import functionality, you must install and activate the "CustomTube Video Importer" plugin.

2. **Changes Made**:
   - Removed import-related files and code
   - Added plugin dependency notice in admin panel
   - Modified functions to forward to plugin functionality when available
   - Adjusted module loading to prevent errors

3. **Modified Files**:
   - `functions.php` - Updated to remove import functionality
   - Various admin files - Removed import-related code
4. **Geo-IP Service**: The ad system now queries `https://ip-api.com` for
   country lookups over HTTPS.

## Installation

1. Install this theme in your WordPress themes directory
2. Activate the theme
3. Install and activate the "CustomTube Video Importer" plugin for import functionality

## Troubleshooting

If you encounter errors:

1. Make sure all required files are present
2. Check that the plugin is installed if you need import functionality
3. Clear WordPress cache and reset permalinks after installation
4. Check PHP error logs for specific issues

## Migration

This version was created using the migration script in the theme directory. For details on the migration process, see MIGRATION-GUIDE.md.

## Translations

The theme's translatable strings live in `languages/customtube.pot`.

1. Regenerate this POT file from the theme root when strings change:

   ```bash
   find . -path ./languages -prune -o -name '*.php' -print | \
     xargs xgettext --from-code=UTF-8 -L PHP \
       -k__ -k_e -k_x:1,2c -k_ex:1,2c -k_n:1,2 -k_nx:1,2,4c \
       -kesc_html__ -kesc_html_e -kesc_html_x:1,2c \
       -kesc_attr__ -kesc_attr_e -kesc_attr_x:1,2c \
       -o languages/customtube.pot
   ```

2. Use Poedit or WP‑CLI to create `.po` and `.mo` files from `customtube.pot`.
3. Save the resulting `.mo` files in the `languages/` directory using locale-based filenames (e.g. `customtube-fr_FR.mo`).

WordPress automatically loads the correct translation based on the site language.
