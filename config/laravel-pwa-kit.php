<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable PWA
    |--------------------------------------------------------------------------
    | Globally enable or disable Progressive Web App functionality.
    */
    'enable_pwa' => true,

    /*
    |--------------------------------------------------------------------------
    | Show Install Toast on First Load
    |--------------------------------------------------------------------------
    |
    | Determines whether the PWA install toast should be displayed when a user
    | first visits the site. Once the toast is shown or dismissed, it will not
    | reappear for that user on the same day, preventing repeated interruptions
    | and improving user experience.
    |
    | Type: `bool`
    | Default: true
    |
    */
    'install-toast-show' => true,


    /*
    |--------------------------------------------------------------------------
    | PWA Manifest Configuration
    |--------------------------------------------------------------------------
    | Defines metadata for your Progressive Web App.
    | This configuration is used to generate the manifest.json file.
    | Reference: https://developer.mozilla.org/en-US/docs/Web/Manifest
    */
    'manifest' => [
    'appName' => 'MomoOpti',
    'name' => 'MomoOpti',
    'shortName' => 'MomoOpti',
    'short_name' => 'MomoOpti',
    'startUrl' => '/',
    'start_url' => '/',
    'scope' => '/',
    'author' => 'Rodrigue k APOTHEY',
    'version' => '1.0',
    'description' => 'Optimisez vos frais Mobile Money en un clic',
    'orientation' => 'portrait',
    'dir' => 'auto',
    'lang' => 'fr',
    'display' => 'standalone',
    'themeColor' => '#4F46E5',
    'theme_color' => '#4F46E5',
    'backgroundColor' => '#ffffff',
    'background_color' => '#ffffff',
    'icons' => [
        [
            'src' => '/icons-old/appstore-images/android/launchericon-512x512.png',// Place ton logo dans public/logo.png
            'sizes' => '512x512',
            'type' => 'image/png',
        ],
         [
            'src' => '/icons-old/appstore-images/android/launchericon-192x192.png', // Place ton logo dans public/logo.png
            'sizes' => '192x192',
            'type' => 'image/png',
        ],
        // Tu peux ajouter d'autres tailles ici
    ],
],
    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    | Enables verbose logging for service worker events and cache information.
    */
    'debug' => env('LARAVEL_PWA_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Toast Content
    |--------------------------------------------------------------------------
    | Title and description text for the install prompt toast.
    */
    'title' => 'Bienvenue sur ' . env('APP_NAME', 'MomoOpti') . '!',
    'description' => 'Clique sur le bouton <strong>Install Now</strong> et profite de l\'application sur ton telephone.',

    /*
    |--------------------------------------------------------------------------
    | Mobile View Position
    |--------------------------------------------------------------------------
    | Position of the PWA install toast on small devices.
    | Supported values: "top", "bottom".
    | RTL mode is supported and respects <html dir="rtl">.
    */
    'small_device_position' => 'bottom',

    /*
    |--------------------------------------------------------------------------
    | Install Now Button Text
    |--------------------------------------------------------------------------
    | Defines the text shown on the "Install Now" button inside the PWA
    | installation toast. This can be customized for localization.
    |
    | Example: 'install_now_button_text' => 'অ্যাপ ইন্সটল করুন'
    */
    'install_now_button_text' => 'Install Now',

    /*
    |--------------------------------------------------------------------------
    | Livewire Integration
    |--------------------------------------------------------------------------
    | Optimize PWA functionality for applications using Laravel Livewire.
    */
    'livewire-app' => false,
];
