<?php

declare(strict_types=1);

namespace Mituu\ACFDefaultValueInitializer;

/**
 * Manages plugin hooks and actions
 */
class Hooks
{
    private DefaultValueProcessor $processor;

    public function __construct(DefaultValueProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function init(): void
    {
        // Hook into ACF field group save
        add_action('acf/update_field_group', [$this, 'onFieldGroupUpdate']);

        // Hook into ACF JSON sync
        add_action('acf/include_fields', [$this, 'onFieldsInclude'], 20);

        // AJAX handler for manual processing
        add_action('wp_ajax_acf_dvi_process_field_group', [$this, 'ajaxProcessFieldGroup']);

        // Process all field groups on plugin activation
        add_action('init', [$this, 'processAllFieldGroups'], 25);
    }

    /**
     * Handle field group update
     */
    public function onFieldGroupUpdate(array $fieldGroup): void
    {
        $this->processor->processFieldGroup($fieldGroup);
    }

    /**
     * Handle ACF JSON sync
     */
    public function onFieldsInclude(): void
    {
        // Get all field groups that might have been synced
        $fieldGroups = acf_get_field_groups();

        foreach ($fieldGroups as $fieldGroup) {
            // Check if this field group was recently synced
            $modified = get_post_modified_time('U', true, $fieldGroup['ID']);
            $now = time();

            // If modified within the last minute, likely from sync
            if (($now - $modified) < 60) {
                $fullFieldGroup = acf_get_field_group($fieldGroup['key']);
                if ($fullFieldGroup) {
                    $this->processor->processFieldGroup($fullFieldGroup);
                }
            }
        }
    }

    /**
     * Process all field groups that have fields with init_default_values enabled
     */
    public function processAllFieldGroups(): void
    {
        // Only run once per request and only when necessary
        static $processed = false;
        if ($processed) {
            return;
        }
        $processed = true;

        $fieldGroups = acf_get_field_groups();

        foreach ($fieldGroups as $fieldGroup) {
            // Load the full field group with fields
            $fullFieldGroup = acf_get_field_group($fieldGroup['key']);
            if ($fullFieldGroup) {
                $this->processor->processFieldGroup($fullFieldGroup);
            }
        }
    }

    /**
     * AJAX handler for manual field group processing
     */
    public function ajaxProcessFieldGroup(): void
    {
        check_ajax_referer('acf_dvi_process', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        $fieldGroupKey = sanitize_text_field($_POST['field_group_key'] ?? '');

        if (empty($fieldGroupKey)) {
            wp_send_json_error('Invalid field group key');
        }

        $fieldGroup = acf_get_field_group($fieldGroupKey);

        if (!$fieldGroup) {
            wp_send_json_error('Field group not found');
        }

        try {
            $this->processor->processFieldGroup($fieldGroup);
            wp_send_json_success('Default values processed successfully');
        } catch (\Exception $e) {
            wp_send_json_error('Error processing default values: ' . $e->getMessage());
        }
    }
}
