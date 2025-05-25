<?php

declare(strict_types=1);

namespace Mituu\ACFDefaultValueInitializer;

/**
 * Processes default value initialization for ACF fields
 */
class DefaultValueProcessor
{
    /**
     * Process field group to initialize default values
     */
    public function processFieldGroup(array $fieldGroup): void
    {
        $fields = $this->getFlattenedFields($fieldGroup);
        $fieldsToProcess = array_filter($fields, function (array $field): bool {
            return !empty($field['init_default_values']) &&
                   isset($field['default_value']) &&
                   $field['default_value'] !== '';
        });

        if (empty($fieldsToProcess)) {
            return;
        }

        foreach ($fieldsToProcess as $field) {
            $this->processField($field, $fieldGroup);
        }
    }

    /**
     * Process individual field default value initialization
     */
    private function processField(array $field, array $fieldGroup): void
    {
        $locations = $fieldGroup['location'] ?? [];

        foreach ($locations as $locationGroup) {
            foreach ($locationGroup as $rule) {
                if ($rule['operator'] !== '==') {
                    continue;
                }

                match ($rule['param']) {
                    'post_type' => $this->processPostTypeField($field, $rule['value']),
                    'user_role' => $this->processUserRoleField($field, $rule['value']),
                    'user_form' => $this->processUserFormField($field),
                    'options_page' => $this->processOptionsField($field, $rule['value']),
                    default => null
                };
            }
        }
    }

    /**
     * Process field for posts
     */
    private function processPostTypeField(array $field, string $postType): void
    {
        global $wpdb;

        $metaKey = $field['name'];
        $defaultValue = $field['default_value'];

        // Get posts that don't have this meta field
        $posts = $wpdb->get_col($wpdb->prepare(
            "SELECT p.ID
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
             WHERE p.post_type = %s
             AND p.post_status NOT IN ('auto-draft', 'trash')
             AND pm.meta_id IS NULL",
            $metaKey,
            $postType
        ));

        foreach ($posts as $postId) {
            update_field($field['key'], $defaultValue, $postId);
        }
    }

    /**
     * Process field for users
     */
    private function processUserRoleField(array $field, string $role): void
    {
        global $wpdb;

        $metaKey = $field['name'];
        $defaultValue = $field['default_value'];

        // Get users with specific role that don't have this meta field
        $users = get_users([
            'role' => $role,
            'fields' => 'ID',
            'meta_query' => [
                [
                    'key' => $metaKey,
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);

        foreach ($users as $userId) {
            update_field($field['key'], $defaultValue, "user_$userId");
        }
    }

    /**
     * Process field for user forms (all users)
     */
    private function processUserFormField(array $field): void
    {
        global $wpdb;

        $metaKey = $field['name'];
        $defaultValue = $field['default_value'];

        $users = get_users([
            'fields' => 'ID',
            'meta_query' => [
                [
                    'key' => $metaKey,
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);

        foreach ($users as $userId) {
            update_field($field['key'], $defaultValue, "user_$userId");
        }
    }

    /**
     * Process field for options pages
     */
    private function processOptionsField(array $field, string $optionsPage): void
    {
        $metaKey = $field['name'];
        $defaultValue = $field['default_value'];

        // Check if option doesn't exist
        if (get_field($field['key'], 'option') === false) {
            update_field($field['key'], $defaultValue, 'option');
        }
    }

    /**
     * Get flattened array of all fields including nested fields
     */
    private function getFlattenedFields(array $fieldGroup): array
    {
        $fields = [];

        // If fields are not loaded, load them using ACF function
        if (!isset($fieldGroup['fields']) || empty($fieldGroup['fields'])) {
            $fieldGroup['fields'] = acf_get_fields($fieldGroup);
        }

        if (isset($fieldGroup['fields'])) {
            $fields = $this->flattenFields($fieldGroup['fields']);
        }

        return $fields;
    }

    /**
     * Recursively flatten nested fields
     */
    private function flattenFields(array $fields): array
    {
        $flattened = [];

        foreach ($fields as $field) {
            $flattened[] = $field;

            // Handle nested fields (repeater, flexible content, etc.)
            if (isset($field['sub_fields']) && is_array($field['sub_fields'])) {
                $flattened = array_merge($flattened, $this->flattenFields($field['sub_fields']));
            }

            if (isset($field['layouts']) && is_array($field['layouts'])) {
                foreach ($field['layouts'] as $layout) {
                    if (isset($layout['sub_fields']) && is_array($layout['sub_fields'])) {
                        $flattened = array_merge($flattened, $this->flattenFields($layout['sub_fields']));
                    }
                }
            }
        }

        return $flattened;
    }
}
