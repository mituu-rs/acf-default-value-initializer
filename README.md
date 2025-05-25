# ACF Default Value Initializer

A WordPress plugin that automatically initializes default values for Advanced Custom Fields (ACF) on existing posts and users.

## Description

When you add new ACF fields with default values to existing content, WordPress typically only applies these defaults to new posts/users created after the field was added. This plugin solves that problem by automatically applying default values to existing content that doesn't have a value for the field.

## Features

- 🔄 **Automatic Default Value Application**: Applies default values to existing posts/users when fields are saved
- ⚙️ **Field-Level Control**: Enable/disable default value initialization per field
- 🎯 **Selective Processing**: Only processes content that doesn't already have a value for the field
- 🔧 **Manual Processing**: Admin interface button for manual initialization
- 📝 **Multiple Post Types**: Works with posts, pages, custom post types, and users
- 🛡️ **Safe Processing**: Only updates empty fields, preserves existing data
- 🎨 **Clean Admin UI**: Seamless integration with ACF field settings

## Requirements

- **WordPress**: 6.4 or higher
- **PHP**: 8.1 or higher  
- **ACF**: Advanced Custom Fields (free or Pro version)

## Installation

1. Download or clone this repository
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Ensure ACF is installed and activated

### Composer Installation

Comming soon.

## Usage

### Enabling Default Value Initialization

1. Go to **Custom Fields > Field Groups** in your WordPress admin
2. Edit any field group
3. For each field you want to initialize defaults:
   - Open the field settings
   - Enable the **"Initialize Default Values"** checkbox
   - Set your desired default value for the field
4. Save the field group

### Supported Field Types

The plugin supports default value initialization for:

- Text, Textarea, Number, Email, URL, Password
- Select, Checkbox, Radio, Button Group, True/False
- Date Picker, Date Time Picker, Time Picker, Color Picker
- Range, WYSIWYG, oEmbed
- User, Post Object, Page Link, Relationship, Taxonomy
- Image, File, Gallery

### Manual Processing

If you need to manually trigger default value initialization:

1. Edit a field group in the ACF admin
2. Look for the **"🔄 Initialize Default Values"** button
3. Click the button to manually process all existing content for this field group

## How It Works

### Automatic Processing

When you save a field group with fields that have "Initialize Default Values" enabled:

1. The plugin identifies all posts/users that match the field group's location rules
2. For each field with initialization enabled, it checks if the post/user has a value
3. If no value exists, it applies the field's default value
4. Existing values are never overwritten

### Processing Flow

```
Field Group Save → Check Enabled Fields → Find Target Content → Apply Defaults
```

## Technical Details

### Plugin Structure

```
acf-default-value-initializer/
├── acf-default-value-initializer.php  # Main plugin file
├── composer.json                      # Composer configuration
├── assets/js/                         # JavaScript files
│   └── field-settings.js             # Admin UI enhancements
└── src/                              # PHP classes
    ├── Plugin.php                    # Main plugin class
    ├── Hooks.php                     # WordPress hooks
    ├── FieldSettings.php             # ACF field settings
    └── DefaultValueProcessor.php     # Core processing logic
```

### Key Classes

- **`Plugin`**: Main plugin initialization and coordination
- **`Hooks`**: WordPress action/filter hooks management  
- **`FieldSettings`**: ACF field settings modifications
- **`DefaultValueProcessor`**: Core logic for applying default values

### Hooks and Filters

The plugin uses these WordPress hooks:

- `acf/save_post` - Triggers default value processing
- `acf/render_field_settings` - Adds the initialization checkbox
- `wp_ajax_acf_dvi_process_field_group` - Handles manual processing

## Configuration

### Field-Level Settings

Each ACF field can be individually configured:

```php
// Field setting added by the plugin
'init_default_values' => 1  // Enable default value initialization
```

### Location Rules

The plugin respects ACF location rules, so default values are only applied to content that matches the field group's location conditions.

## Development

### Local Development Setup

```bash
# Clone the repository
git clone https://github.com/mituu-rs/acf-default-value-initializer.git

# Install dependencies (if using Composer)
composer install
```

### Code Standards

- PHP 8.1+ with strict types
- PSR-4 autoloading
- WordPress coding standards
- Comprehensive error handling

## Changelog

### Version 1.0.0
- Initial release
- Automatic default value initialization
- Field-level control settings
- Manual processing interface
- Support for all major ACF field types

## Support

For issues, feature requests, or contributions:

- **Repository**: [GitHub](https://github.com/mituu-rs/acf-default-value-initializer)
- **Author**: [mituu](https://github.com/mituu-rs)

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
