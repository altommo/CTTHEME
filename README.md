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
