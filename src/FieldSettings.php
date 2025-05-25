<?php

declare(strict_types=1);

namespace Mituu\ACFDefaultValueInitializer;

/**
 * Handles ACF field settings modifications
 */
class FieldSettings
{
    public function init(): void
    {
        add_action('acf/render_field_settings', [$this, 'renderFieldSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Add custom field setting to ACF fields
     */
    public function renderFieldSettings(array $field): void
    {
        // Only add to fields that can have default values
        $supportedTypes = [
            'text', 'textarea', 'number', 'email', 'url', 'password',
            'select', 'checkbox', 'radio', 'button_group', 'true_false',
            'date_picker', 'date_time_picker', 'time_picker', 'color_picker',
            'range', 'wysiwyg', 'oembed', 'user', 'post_object', 'page_link',
            'relationship', 'taxonomy', 'image', 'file', 'gallery'
        ];

        if (!in_array($field['type'], $supportedTypes, true)) {
            return;
        }

        acf_render_field_setting($field, [
            'label' => __('Initialize Default Values', 'acf-default-value-initializer'),
            'instructions' => __(
                'When enabled, the default value will be applied to all existing posts/users that don\'t have a value for this field.',
                'acf-default-value-initializer'
            ),
            'type' => 'true_false',
            'name' => 'init_default_values',
            'ui' => 1,
            'default_value' => 0,
            'class' => 'acf-dvi-init-defaults'
        ]);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueueScripts(): void
    {
        $screen = get_current_screen();

        if ($screen && in_array($screen->id, ['acf-field-group'], true)) {
            wp_enqueue_script(
                'acf-dvi-field-settings',
                ACF_DVI_PLUGIN_URL . 'assets/js/field-settings.js',
                ['jquery', 'acf-field-group'],
                ACF_DVI_VERSION,
                true
            );

            wp_localize_script('acf-dvi-field-settings', 'acfDviSettings', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('acf_dvi_process'),
                'processing' => __('Processing default values...', 'acf-default-value-initializer'),
                'completed' => __('Default values initialized successfully!', 'acf-default-value-initializer'),
                'error' => __('Error initializing default values.', 'acf-default-value-initializer'),
            ]);
        }
    }
}