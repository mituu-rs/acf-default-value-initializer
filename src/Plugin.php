<?php

declare(strict_types=1);

namespace Mituu\ACFDefaultValueInitializer;

/**
 * Main plugin class
 */
final class Plugin
{
    private static ?self $instance = null;
    private bool $initialized = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->loadTextdomain();
        $this->initializeComponents();
        $this->initialized = true;
    }

    private function loadTextdomain(): void
    {
        load_plugin_textdomain(
            'acf-default-value-initializer',
            false,
            dirname(ACF_DVI_PLUGIN_BASENAME) . '/languages'
        );
    }

    private function initializeComponents(): void
    {
        $fieldSettings = new FieldSettings();
        $processor = new DefaultValueProcessor();
        $hooks = new Hooks($processor);

        $fieldSettings->init();
        $hooks->init();
    }

    public function getVersion(): string
    {
        return ACF_DVI_VERSION;
    }

    public function getPluginDir(): string
    {
        return ACF_DVI_PLUGIN_DIR;
    }

    public function getPluginUrl(): string
    {
        return ACF_DVI_PLUGIN_URL;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}