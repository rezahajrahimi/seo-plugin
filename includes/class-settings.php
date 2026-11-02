<?php
/**
 * صفحه و تنظیمات افزونه هوشمند سئو
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Settings {

    const OPTION_NAME = 'smart_ai_seo_settings';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_settings_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    public static function get_settings() {
        $defaults = array(
            'provider'             => 'openai',
            // OpenAI
            'openai_api_key'       => '',
            'openai_model'         => 'gpt-4o-mini',
            // Google Gemini
            'gemini_api_key'       => '',
            'gemini_model'         => 'gemini-1.5-flash',
            // DeepSeek
            'deepseek_api_key'     => '',
            'deepseek_model'       => 'deepseek-chat',
            // Custom Rest API / Self-hosted
            'custom_endpoint'      => '',
            'custom_api_key'       => '',
            'custom_model'         => '',
            'custom_extra_headers' => '',
            // پرامپت پیش‌فرض سیستم
            'custom_system_prompt' => '',
        );

        $saved = get_option( self::OPTION_NAME, array() );
        return wp_parse_args( $saved, $defaults );
    }

    public static function add_settings_menu() {
        add_menu_page(
            'سئو هوشمند با هوش مصنوعی',
            'سئو هوشمند (AI) ✨',
            'manage_options',
            'smart-ai-seo',
            array( __CLASS__, 'render_settings_page' ),
            'dashicons-chart-line',
            58
        );

        add_submenu_page(
            'smart-ai-seo',
            'تنظیمات و اتصال مدل‌ها',
            'تنظیمات و اتصال ⚙️',
            'manage_options',
            'smart-ai-seo',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function register_settings() {
        register_setting( 'smart_ai_seo_group', self::OPTION_NAME );
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = self::get_settings();
        ?>
        <div class="wrap smart-ai-seo-settings-wrap">
            <div class="smart-header-hero">
                <div>
                    <h1><span>✨</span> تنظیمات و اتصال به هوش مصنوعی (AI Configuration)</h1>
                    <p>پیکربندی کلیدهای API ارائه‌دهندگان معتبر (OpenAI, Gemini, DeepSeek) یا مدل‌های محلی و سرورهای سفارشی (vLLM, Ollama, OpenRouter)</p>
                </div>
                <div class="smart-header-badge">
                    نسخه <?php echo esc_html( SMART_AI_SEO_VERSION ); ?>
                </div>
            </div>

            <?php settings_errors(); ?>

            <form method="post" action="options.php" class="smart-ai-seo-form">
                <?php
                settings_fields( 'smart_ai_seo_group' );
                ?>

                <div class="smart-card">
                    <h2>انتخاب موتور و ارائه‌دهنده هوش مصنوعی</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="ai_provider">ارائه‌دهنده فعال:</label></th>
                            <td>
                                <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[provider]" id="ai_provider" class="regular-text">
                                    <option value="openai" <?php selected( $options['provider'], 'openai' ); ?>>OpenAI (ChatGPT 4o / mini)</option>
                                    <option value="gemini" <?php selected( $options['provider'], 'gemini' ); ?>>Google Gemini (Flash / Pro)</option>
                                    <option value="deepseek" <?php selected( $options['provider'], 'deepseek' ); ?>>DeepSeek (DeepSeek-V3 / R1)</option>
                                    <option value="custom" <?php selected( $options['provider'], 'custom' ); ?>>سفارشی / Rest API اختصاصی و مدل شخصی</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- بخش OpenAI -->
                <div class="smart-card provider-section" id="section-openai">
                    <h2>پیکربندی OpenAI</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="openai_api_key">OpenAI API Key:</label></th>
                            <td>
                                <input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[openai_api_key]" id="openai_api_key" value="<?php echo esc_attr( $options['openai_api_key'] ); ?>" class="large-text" placeholder="sk-..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="openai_model">نام مدل:</label></th>
                            <td>
                                <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[openai_model]" id="openai_model" value="<?php echo esc_attr( $options['openai_model'] ); ?>" class="regular-text" placeholder="gpt-4o-mini" />
                                <p class="description">مثال: gpt-4o-mini، gpt-4o</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- بخش Google Gemini -->
                <div class="smart-card provider-section" id="section-gemini">
                    <h2>پیکربندی Google Gemini</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gemini_api_key">Google AI Studio API Key:</label></th>
                            <td>
                                <input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[gemini_api_key]" id="gemini_api_key" value="<?php echo esc_attr( $options['gemini_api_key'] ); ?>" class="large-text" placeholder="AIzaSy..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gemini_model">نام مدل:</label></th>
                            <td>
                                <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[gemini_model]" id="gemini_model" value="<?php echo esc_attr( $options['gemini_model'] ); ?>" class="regular-text" placeholder="gemini-1.5-flash" />
                                <p class="description">مثال: gemini-1.5-flash، gemini-1.5-pro</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- بخش DeepSeek -->
                <div class="smart-card provider-section" id="section-deepseek">
                    <h2>پیکربندی DeepSeek</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="deepseek_api_key">DeepSeek API Key:</label></th>
                            <td>
                                <input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[deepseek_api_key]" id="deepseek_api_key" value="<?php echo esc_attr( $options['deepseek_api_key'] ); ?>" class="large-text" placeholder="sk-..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="deepseek_model">نام مدل:</label></th>
                            <td>
                                <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[deepseek_model]" id="deepseek_model" value="<?php echo esc_attr( $options['deepseek_model'] ); ?>" class="regular-text" placeholder="deepseek-chat" />
                                <p class="description">مثال: deepseek-chat، deepseek-reasoner</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- بخش Custom API / مدل اختصاصی -->
                <div class="smart-card provider-section" id="section-custom">
                    <h2>اتصال به Rest API و مدل شخصی (Custom EndPoint)</h2>
                    <p class="description">سازگار با تمامی سرویس‌های با ساختار سازگار با OpenAI مانند سرورهای vLLM، Ollama، LM Studio، OpenRouter، Groq یا API شخصی شرکت شما.</p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="custom_endpoint">آدرس کامل Rest API Endpoint:</label></th>
                            <td>
                                <input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_endpoint]" id="custom_endpoint" value="<?php echo esc_attr( $options['custom_endpoint'] ); ?>" class="large-text" placeholder="https://api.yourdomain.com/v1/chat/completions" />
                                <p class="description">مثال برای OpenRouter: <code>https://openrouter.ai/api/v1/chat/completions</code> | لوکال Ollama: <code>http://localhost:11434/v1/chat/completions</code></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="custom_api_key">API Key یا Bearer Token (در صورت نیاز):</label></th>
                            <td>
                                <input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_api_key]" id="custom_api_key" value="<?php echo esc_attr( $options['custom_api_key'] ); ?>" class="large-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="custom_model">شناسه مدل (Model ID):</label></th>
                            <td>
                                <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_model]" id="custom_model" value="<?php echo esc_attr( $options['custom_model'] ); ?>" class="regular-text" placeholder="meta-llama/llama-3-70b-instruct" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="custom_extra_headers">هدرهای سفارشی اضافی (Custom Headers):</label></th>
                            <td>
                                <textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_extra_headers]" id="custom_extra_headers" rows="3" class="large-text" placeholder="HTTP-Referer: https://mysite.com&#10;X-Title: MySite"><?php echo esc_textarea( $options['custom_extra_headers'] ); ?></textarea>
                                <p class="description">هر هدر در یک خط به فرمت <code>Header-Name: Value</code></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- بخش تنظیم پرامپت اختصاصی سیستم -->
                <div class="smart-card">
                    <h2>دستورالعمل و پرامپت کلی هوش مصنوعی (System Prompt)</h2>
                    <p class="description">می‌توانید دستورات تکمیلی یا استراتژی کلی دلخواه خود را تعیین کنید. (در صورت خالی گذاشتن، از پرامپت بهینه‌شده پیش‌فرض استاندارد استفاده می‌شود).</p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="custom_system_prompt">دستورات سفارشی کلی (System Prompt):</label></th>
                            <td>
                                <textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_system_prompt]" id="custom_system_prompt" rows="6" class="large-text" placeholder="مثال: همیشه از لحن صمیمی استفاده کن، به زبان ساده بنویس، از آوردن کلمات کلیشه‌ای خودداری کن و حداقل ۳ زیرعنوان به متن اضافه نما..."><?php echo esc_textarea( $options['custom_system_prompt'] ); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="smart-card">
                    <button type="button" id="smart-test-connection" class="button button-secondary">تست اتصال به هوش مصنوعی</button>
                    <span id="test-connection-result" style="margin-right: 15px; font-weight: bold;"></span>
                </div>

                <?php submit_button( 'ذخیره تنظیمات هوش مصنوعی' ); ?>
            </form>

            <!-- بخش معرفی افزونه‌های مکمل و منتخب وب‌دیده -->
            <?php self::render_promo_showcase(); ?>

        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var select = document.getElementById('ai_provider');
                function toggleSections() {
                    var val = select.value;
                    document.querySelectorAll('.provider-section').forEach(function(el) {
                        el.style.display = 'none';
                    });
                    var active = document.getElementById('section-' + val);
                    if (active) active.style.display = 'block';
                }
                select.addEventListener('change', toggleSections);
                toggleSections();
            });
        </script>
        <?php
    }

    /**
     * رندر بخش معرفی افزونه‌های مکمل و پرکاربرد توسعه‌دهنده
     */
    public static function render_promo_showcase() {
        ?>
        <div class="smart-promo-showcase">
            <h2 class="smart-promo-showcase-title">
                <span>💎</span> افزونه‌های مکمل پیشنهادی جهت رشد فروش و مدیریت فروشگاه شما
            </h2>
            <div class="smart-promo-grid">

                <!-- کارت درگاه کارت به کارت -->
                <div class="smart-promo-card promo-cardtocard">
                    <div>
                        <div class="smart-promo-card-header">
                            <div class="smart-promo-icon">💳</div>
                            <div>
                                <h3>تایید خودکار کارت به کارت ووکامرس</h3>
                                <span class="smart-promo-tag">Webdide Card to Card</span>
                            </div>
                        </div>
                        <p>درگاه پرداخت هوشمند کارت به کارت مستقیم با تایید خودکار و استعلام آنی فیش واریزی در ووکامرس بدون نیاز به بررسی دستی و بدون کارمزد بانکی.</p>
                        <ul class="smart-promo-features">
                            <li>✅ تایید خودکار و اتوماتیک فیش‌های واریزی</li>
                            <li>✅ اتصال به سیستم‌های پیامکی و اعتبارسنجی آنی</li>
                            <li>✅ افزایش نرخ تبدیل مشتریانی که رمز پویا ندارند</li>
                        </ul>
                    </div>
                    <div class="smart-promo-actions">
                        <a href="https://wordpress.org/plugins/webdide-card-to-card-verification/" target="_blank" rel="noopener noreferrer" class="smart-promo-btn btn-cardtocard">
                            <span>دریافت رایگان از مخزن وردپرس</span> ↗
                        </a>
                    </div>
                </div>

                <!-- کارت فروش در دایرکت اینستاگرام -->
                <div class="smart-promo-card promo-instagram">
                    <div>
                        <div class="smart-promo-card-header">
                            <div class="smart-promo-icon">📸</div>
                            <div>
                                <h3>فروش محصولات سایت در دایرکت اینستاگرام</h3>
                                <span class="smart-promo-tag">Pilotlane Social Catalog Sync</span>
                            </div>
                        </div>
                        <p>همگام‌سازی خودکار کاتالوگ محصولات ووکامرس با شبکه‌های اجتماعی جهت فروش مستقیم و پاسخ‌دهی به سفارش‌ها در دایرکت اینستاگرام.</p>
                        <ul class="smart-promo-features">
                            <li>✅ اتصال هوشمند ووکامرس به کاتالوگ اینستاگرام</li>
                            <li>✅ ثبت سفارش و صدور فاکتور آسان در چت و دایرکت</li>
                            <li>✅ بروزرسانی خودکار موجودی و قیمت‌ها بدون اتلاف وقت</li>
                        </ul>
                    </div>
                    <div class="smart-promo-actions">
                        <a href="https://wordpress.org/plugins/pilotlane-social-catalog-sync-woocommerce/" target="_blank" rel="noopener noreferrer" class="smart-promo-btn btn-instagram">
                            <span>دریافت رایگان از مخزن وردپرس</span> ↗
                        </a>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }
}
