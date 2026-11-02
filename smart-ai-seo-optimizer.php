<?php
/**
 * Plugin Name: Smart AI SEO Optimizer
 * Plugin URI:  https://github.com/reza/smart-ai-seo-optimizer
 * Description: افزونه هوشمند سئو وردپرس متصل به انواع هوش مصنوعی (OpenAI, Gemini, DeepSeek, مدل شخصی و Custom REST API) جهت تحلیل و بهینه‌سازی محتوا برای کلمات کلیدی هدف با امکان اعمال خودکار پیشنهادات.
 * Version:     1.0.3
 * Author:      Reza
 * Text Domain: smart-ai-seo
 * Domain Path: /languages
 * License:     GPL-2.0+
 */

// جلوگیری از دسترسی مستقیم
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// تعریف ثوابت مسیرها و نسخه‌ها
define( 'SMART_AI_SEO_VERSION', '1.0.3' );
define( 'SMART_AI_SEO_FILE', __FILE__ );
define( 'SMART_AI_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMART_AI_SEO_URL', plugin_dir_url( __FILE__ ) );

// بارگذاری کلاس‌های افزونه
require_once SMART_AI_SEO_DIR . 'includes/class-ai-service.php';
require_once SMART_AI_SEO_DIR . 'includes/class-settings.php';
require_once SMART_AI_SEO_DIR . 'includes/class-metabox.php';
require_once SMART_AI_SEO_DIR . 'includes/class-ajax-handler.php';
require_once SMART_AI_SEO_DIR . 'includes/class-schema.php';
require_once SMART_AI_SEO_DIR . 'includes/class-columns.php';
require_once SMART_AI_SEO_DIR . 'includes/class-generator.php';
require_once SMART_AI_SEO_DIR . 'includes/class-image-service.php';
require_once SMART_AI_SEO_DIR . 'includes/class-frontend-optimizer.php';
require_once SMART_AI_SEO_DIR . 'includes/class-bulk-optimizer.php';

/**
 * راه‌اندازی افزونه
 */
function smart_ai_seo_init() {
    Smart_AI_SEO_Settings::init();
    Smart_AI_SEO_Metabox::init();
    Smart_AI_SEO_Ajax_Handler::init();
    Smart_AI_SEO_Schema::init();
    Smart_AI_SEO_Columns::init();
    Smart_AI_SEO_Generator::init();
    Smart_AI_SEO_Frontend_Optimizer::init();
    Smart_AI_SEO_Bulk_Optimizer::init();
}
add_action( 'plugins_loaded', 'smart_ai_seo_init' );

/**
 * بارگذاری استایل‌ها و اسکریپت‌های ادمین
 */
function smart_ai_seo_enqueue_admin_assets( $hook ) {
    // فقط در صفحه نوشته‌ها، برگه تنظیمات یا برگه تولید محتوا بارگذاری شود
    $allowed_hooks = array( 'post.php', 'post-new.php', 'toplevel_page_smart-ai-seo', 'smart-ai-seo_page_smart-ai-seo-generator' );
    if ( ! in_array( $hook, $allowed_hooks, true ) && strpos( $hook, 'smart-ai-seo' ) === false ) {
        return;
    }

    wp_enqueue_style(
        'smart-ai-seo-admin-css',
        SMART_AI_SEO_URL . 'assets/css/admin.css',
        array(),
        SMART_AI_SEO_VERSION
    );

    wp_enqueue_script(
        'smart-ai-seo-admin-js',
        SMART_AI_SEO_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        SMART_AI_SEO_VERSION,
        true
    );

    wp_localize_script( 'smart-ai-seo-admin-js', 'smartAiSeoData', array(
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'smart_ai_seo_nonce' ),
        'i18n'      => array(
            'analyzing'        => 'در حال تحلیل هوشمند سئو و استخراج پیشنهادات...',
            'applied'          => 'با موفقیت بر روی نوشته اعمال شد!',
            'applyConfirm'     => 'آیا می‌خواهید محتوای جدید بهینه‌شده جایگزین متن نوشته فعلی شود؟',
            'errorOccurred'    => 'خطایی رخ داد، لطفاً دوباره بررسی کنید.',
            'noKeyword'        => 'لطفاً کلمه کلیدی یا عبارت مورد نظرتان را وارد کنید.',
            'applying'         => 'در حال اعمال...',
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'smart_ai_seo_enqueue_admin_assets' );
