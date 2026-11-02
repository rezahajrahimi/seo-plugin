<?php
/**
 * صفحه مدیریت تولید محتوای هوشمند (تکی و خوشه‌ای)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Generator {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_generator_menu' ) );
    }

    public static function add_generator_menu() {
        add_submenu_page(
            'smart-ai-seo',
            'تولید محتوای هوشمند (AI Writer)',
            'تولید محتوا و خوشه‌ها ✍️',
            'edit_posts',
            'smart-ai-seo-generator',
            array( __CLASS__, 'render_generator_page' )
        );
    }

    public static function render_generator_page() {
        ?>
        <div class="wrap smart-ai-seo-settings-wrap">
            <div class="smart-header-hero">
                <div>
                    <h1><span>✍️</span> کارخانه هوشمند محتوا، خوشه‌ها و بهینه‌سازی گروهی</h1>
                    <p>معماری مدرن سئو: تولید مقالات رتبه ۱ گوگل با رعایت Search Intent، استراتژی خوشه‌ای Pillar-Cluster و احیای گروهی مقالات کهنه</p>
                </div>
                <div class="smart-header-badge">
                    Studio Edition ✨
                </div>
            </div>

            <!-- تب‌های اصلی صفحه تولید -->
            <div class="smart-tabs-nav" style="margin-top: 20px;">
                <button type="button" class="smart-tab-btn active" data-tab="single-article">📝 تولید مقاله تکی جامع</button>
                <button type="button" class="smart-tab-btn" data-tab="cluster-plan">🕸️ استراتژی خوشه‌ای (Topic Cluster & Pillar)</button>
                <button type="button" class="smart-tab-btn" data-tab="bulk-optimizer">⚡ بهینه‌سازی و رفرش گروهی مقالات (Bulk Optimizer)</button>
            </div>

            <!-- تب ۱: تولید مقاله تکی -->
            <div class="smart-tab-content active" id="tab-content-single-article">
                <div class="smart-card">
                    <h2>مشخصات مقاله هدف</h2>
                    <form id="smart-single-gen-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="gen_keyword">کلمه کلیدی اصلی (Focus Keyword): *</label></th>
                                <td>
                                    <input type="text" id="gen_keyword" class="large-text" placeholder="مثلاً: آموزش سئو تکنیکال" required />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_lsi">کلمات کلیدی مکمل یا LSI (اختیاری):</label></th>
                                <td>
                                    <input type="text" id="gen_lsi" class="large-text" placeholder="با کاما جدا کنید. مثلاً: سرعت سایت، اسکیما، فایل robots.txt، نقشه سایت" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_tone">لحن نگارش (Tone of Voice):</label></th>
                                <td>
                                    <select id="gen_tone" class="regular-text">
                                        <option value="تخصصی، روان، آموزشی و معتبر">آموزشی و تخصصی (Professional)</option>
                                        <option value="صمیمی، ترغیب‌کننده و دوستانه">دوستانه و صمیمی (Conversational)</option>
                                        <option value="ترغیب‌کننده خرید و تجاری و فروشگاهی">تجاری و ترغیب به خرید (Commercial)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_length">طول و عمق محتوا:</label></th>
                                <td>
                                    <select id="gen_length" class="regular-text">
                                        <option value="standard" selected>استاندارد و جامع (۱۵۰۰ تا ۲۲۰۰ کلمه - پیشنهاد طلایی سئو)</option>
                                        <option value="pillar">راهنمای مرجع ستونی و عمیق (۲۵۰۰ تا ۳۵۰۰ کلمه)</option>
                                        <option value="concise">فشرده و پاسخ سریع (۸۰۰ تا ۱۲۰۰ کلمه)</option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                        <!-- بخش تنظیمات استراتژیک، لینک‌ها و منابع -->
                        <div class="smart-card" style="background:#f8fafc; border-right:4px solid #3b82f6; margin-top:20px;">
                            <h3 style="margin-top:0; color:#1e3a8a;">🎯 هدف‌گذاری بازاریابی، لینک‌های تبدیل و منابع متن</h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="gen_goal">هدف اصلی از تولید مقاله (Article Goal):</label></th>
                                    <td>
                                        <select id="gen_goal" class="regular-text">
                                            <option value="sales">فروش و معرفی محصول / خدمات (Commercial & Conversion)</option>
                                            <option value="leads">تولید لید و ثبت‌نام / تماس (Lead Generation)</option>
                                            <option value="traffic">افزایش بازدید ارگانیک و آگاهی از برند (Brand Awareness & Traffic)</option>
                                            <option value="comparison">مقایسه، نقد و بررسی رقبا (Comparison & Review)</option>
                                        </select>
                                        <p class="description">هوش مصنوعی کل ساختار مقاله را در جهت این هدف متمرکز می‌کند.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="gen_target_audience">مخاطب هدف یا پرسونای خواننده:</label></th>
                                    <td>
                                        <input type="text" id="gen_target_audience" class="large-text" placeholder="مثلاً: ادمین‌های اینستاگرام، صاحبان فروشگاه‌های ووکامرسی، بازاریابان دیجیتال" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="gen_cta_url">لینک صفحه فرود یا محصول هدف (CTA Link):</label></th>
                                    <td>
                                        <input type="url" id="gen_cta_url" class="large-text" placeholder="https://yoursite.com/product-landing-page" />
                                        <p class="description">این لینک به شکل طبیعی و در کادرهای دعوت به اقدام (Call To Action) در بین متن و انتهای مقاله قرار می‌گیرد.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="gen_cta_anchor">متن یا انکرتکست دعوت به اقدام (CTA Anchor):</label></th>
                                    <td>
                                        <input type="text" id="gen_cta_anchor" class="large-text" placeholder="مثلاً: خرید و فعال‌سازی ربات دایرکت ووپایلوت یا برای مشاوره رایگان اینجا کلیک کنید" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="gen_source_reference">متن منبع، آمار یا لینک مقالات رقبا (References):</label></th>
                                    <td>
                                        <textarea id="gen_source_reference" rows="3" class="large-text" placeholder="اگر متن، داکیومنت، آمار، یا لینک مقاله رقیبی دارید وارد کنید تا هوش مصنوعی آن را تحلیل کرده و مقاله‌ای به مراتب کامل‌تر بنویسد..."></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="gen_category">دسته‌بندی نوشته:</label></th>
                                <td>
                                    <?php
                                    $categories = get_categories( array( 'hide_empty' => false ) );
                                    ?>
                                    <select id="gen_category" class="regular-text">
                                        <option value="0">تشخیص خودکار یا دسته پیش‌فرض</option>
                                        <?php foreach ( $categories as $cat ) : ?>
                                            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_image_source">تصویر شاخص نوشته:</label></th>
                                <td>
                                    <select id="gen_image_source" class="regular-text">
                                        <option value="ai_pollinations" selected>تولید خودکار تصویر شاخص با هوش مصنوعی (AI)</option>
                                        <option value="unsplash">دریافت تصویر استوک باکیفیت از Unsplash</option>
                                        <option value="prompt_only">فقط تولید پرامپت انگلیسی تصویر (جهت طراحی دستی)</option>
                                        <option value="none">بدون تصویر شاخص</option>
                                    </select>
                                    <p class="description">تصویر تولید شده به طور خودکار به عنوان تصویر شاخص (Featured Image) با تگ Alt سئو شده ثبت خواهد شد.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_status">وضعیت انتشار نوشته:</label></th>
                                <td>
                                    <select id="gen_status" class="regular-text">
                                        <option value="draft" selected>پیش‌نویس (Draft) - بازبینی قبل از انتشار</option>
                                        <option value="publish">انتشار مستقیم و فوری (Publish)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gen_instructions">دستورات خاص و هدینگ‌های دلخواه (اختیاری):</label></th>
                                <td>
                                    <textarea id="gen_instructions" rows="3" class="large-text" placeholder="مثلاً: حتماً چک‌لیست دانلودی معرفی کن، جدول مقایسه ابزارها داشته باش..."></textarea>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="button" id="smart-start-single-gen" class="button button-primary button-hero">
                                🚀 نگارش و ایجاد پیش‌نویس کامل در وردپرس
                            </button>
                            <span class="spinner" id="gen-single-spinner"></span>
                        </p>
                    </form>
                </div>

                <!-- باکس نتیجه تولید تکی -->
                <div id="gen-single-result-box" class="smart-card smart-hidden">
                    <h3 style="color:#15803d;">✅ مقاله با موفقیت تولید و به عنوان «پیش‌نویس» ذخیره شد!</h3>
                    <p>عنوان: <strong id="res-single-title"></strong></p>
                    <p>پیوند یکتا (Slug): <code id="res-single-slug"></code></p>
                    <p>متای سئو: <span id="res-single-meta" style="color:#475569;"></span></p>
                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <a href="#" id="res-single-edit-link" class="button button-primary button-large" target="_blank">✏️ ویرایش نوشته در وردپرس</a>
                        <a href="#" id="res-single-preview-link" class="button button-secondary button-large" target="_blank">👁️ مشاهده پیش‌نمایش</a>
                    </div>
                </div>
            </div>

            <!-- تب ۲: استراتژی خوشه‌ای (Topic Cluster) -->
            <div class="smart-tab-content" id="tab-content-cluster-plan">
                <div class="smart-card">
                    <h2>طراحی معماری خوشه محتوایی (Pillar & Clusters)</h2>
                    <p class="description">یک موضوع کلان و اصلی به سیستم بدهید؛ هوش مصنوعی ساختار صفحه ستون (Pillar Page) و مقالات اقماری زیرمجموعه را با استراتژی دقیق لینک‌سازی متقابل طراحی می‌کند.</p>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="cluster_core_topic">موضوع کلان اصلی (Core Topic):</label></th>
                            <td>
                                <input type="text" id="cluster_core_topic" class="large-text" placeholder="مثلاً: بازاریابی در اینستاگرام" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cluster_count">تعداد مقالات اقماری (فرعی):</label></th>
                            <td>
                                <select id="cluster_count" class="regular-text">
                                    <option value="4">۴ مقاله اقماری (کوچک)</option>
                                    <option value="6" selected>۶ مقاله اقماری (استاندارد)</option>
                                    <option value="8">۸ مقاله اقماری (جامع و سلطه موضوعی)</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- بخش تنظیمات استراتژیک، لینک‌ها و منابع کل خوشه -->
                    <div class="smart-card" style="background:#f8fafc; border-right:4px solid #10b981; margin-top:20px;">
                        <h3 style="margin-top:0; color:#065f46;">🎯 هدف‌گذاری بازاریابی، لینک‌های تبدیل و منابع کل خوشه</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="cluster_goal">هدف کلی خوشه موضوعی (Cluster Goal):</label></th>
                                <td>
                                    <select id="cluster_goal" class="regular-text">
                                        <option value="sales">فروش و معرفی محصول / خدمات (Commercial & Conversion)</option>
                                        <option value="leads">تولید لید و ثبت‌نام / تماس (Lead Generation)</option>
                                        <option value="traffic" selected>افزایش بازدید ارگانیک و آگاهی از برند (Brand Awareness & Traffic)</option>
                                        <option value="comparison">مقایسه، نقد و بررسی رقبا (Comparison & Review)</option>
                                    </select>
                                    <p class="description">تمامی مقالات ستون و فرعی این خوشه با تمرکز بر این هدف تجاری نگارش خواهند شد.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cluster_target_audience">مخاطب هدف یا پرسونای خوشه:</label></th>
                                <td>
                                    <input type="text" id="cluster_target_audience" class="large-text" placeholder="مثلاً: خریداران عمده پوشاک، مدیران مارکتینگ، صاحبان فروشگاه‌های اینترنتی" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cluster_cta_url">لینک صفحه فرود یا محصول هدف (CTA Link):</label></th>
                                <td>
                                    <input type="url" id="cluster_cta_url" class="large-text" placeholder="https://yoursite.com/main-landing-or-product" />
                                    <p class="description">لینک اصلی کسب‌وکار شما که در تمام مقالات خوشه در کادرهای دعوت به اقدام (CTA) تعبیه می‌شود.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cluster_cta_anchor">متن یا انکرتکست دعوت به اقدام (CTA Anchor):</label></th>
                                <td>
                                    <input type="text" id="cluster_cta_anchor" class="large-text" placeholder="مثلاً: شروع استفاده رایگان از ووپایلوت یا خرید پکیج ویژه" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="cluster_source_reference">متن منابع، داکیومنت‌ها، آمار یا لینک رقبا:</label></th>
                                <td>
                                    <textarea id="cluster_source_reference" rows="3" class="large-text" placeholder="داکیومنت‌ها، اطلاعات محصولات، یا لینک مقالات مرجع که مایلید هوش مصنوعی در نگارش تمام مقالات این خوشه از داده‌های آنها استفاده کند..."></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="cluster_category">دسته‌بندی مقالات خوشه:</label></th>
                            <td>
                                <select id="cluster_category" class="regular-text">
                                    <option value="0">تشخیص یا دسته پیش‌فرض</option>
                                    <?php foreach ( $categories as $cat ) : ?>
                                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cluster_image_source">تصاویر شاخص مقالات خوشه:</label></th>
                            <td>
                                <select id="cluster_image_source" class="regular-text">
                                    <option value="ai_pollinations" selected>تولید خودکار تصاویر با هوش مصنوعی برای هر مقاله</option>
                                    <option value="unsplash">دریافت تصاویر استوک از Unsplash</option>
                                    <option value="none">بدون تصویر</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cluster_schedule_interval">نحوه انتشار و زمان‌بندی (Drip Publishing):</label></th>
                            <td>
                                <select id="cluster_schedule_interval" class="regular-text">
                                    <option value="0">همه مقالات به عنوان «پیش‌نویس» ذخیره شوند</option>
                                    <option value="1">تدریجی: هر ۱ روز یک مقاله منتشر شود</option>
                                    <option value="2">تدریجی: هر ۲ روز یک مقاله منتشر شود</option>
                                    <option value="3" selected>تدریجی: هر ۳ روز یک مقاله منتشر شود (پیشنهاد سئو)</option>
                                    <option value="7">تدریجی: هفتگی (هر ۷ روز یک مقاله)</option>
                                    <option value="publish_all">انتشار فوری و یکباره همه مقالات</option>
                                </select>
                                <p class="description">انتشار تدریجی با الگوریتم زمان‌بندی وردپرس (Scheduled) باعث ارسال سیگنال رشد ارگانیک و طبیعی به خزنده‌های گوگل می‌شود.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="button" id="smart-start-cluster-plan" class="button button-primary button-hero">
                            🕸️ طراحی نقشه خوشه محتوایی
                        </button>
                        <span class="spinner" id="cluster-spinner"></span>
                    </p>
                </div>

                <!-- نمایش درخت خوشه محتوایی -->
                <div id="cluster-result-container" class="smart-card smart-hidden">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
                        <h2>ساختار خوشه موضوعی و پیوندها</h2>
                        <button type="button" id="smart-bulk-generate-cluster" class="button button-primary button-hero">
                            ⚡ تولید و زمان‌بندی خودکار کل خوشه محتوایی
                        </button>
                    </div>
                    <div id="cluster-bulk-progress" class="smart-hidden" style="margin:15px 0; padding:12px; background:#eff6ff; border-radius:6px; border:1px solid #bfdbfe; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span class="spinner is-active" style="float:none; vertical-align:middle;"></span>
                            <strong id="cluster-bulk-status-text">در حال آماده‌سازی تولید خودکار مقالات...</strong>
                        </div>
                        <button type="button" id="smart-cancel-bulk-generate" class="button button-secondary" style="color:#b91c1c; border-color:#fca5a5;">
                            🛑 توقف و لغو فرآیند
                        </button>
                    </div>
                    
                    <!-- مقاله ستون Pillar -->
                    <div class="pillar-box" id="pillar-display-box">
                        <span class="pillar-badge">🏛️ مقاله ستون اصلی (Pillar Page)</span>
                        <h3 id="pillar-title" style="margin:8px 0;"></h3>
                        <p><strong>کلمه کلیدی ستون:</strong> <span id="pillar-keyword"></span> | <strong>نامک:</strong> <code id="pillar-slug"></code></p>
                        <p class="description" id="pillar-desc"></p>
                        <div id="pillar-outlines" style="margin-top:10px;"></div>
                        <div style="margin-top:12px;">
                            <button type="button" class="button button-primary smart-generate-cluster-item-btn" data-type="pillar">
                                ✍️ ایجاد پیش‌نویس مقاله ستون
                            </button>
                            <span class="pillar-status-msg" style="margin-right:10px;"></span>
                        </div>
                    </div>

                    <h3 style="margin-top:25px;">مقالات اقماری فرعی (Cluster Sub-Pages):</h3>
                    <div id="cluster-subpages-list" class="clusters-grid"></div>
                </div>
            </div>

            <!-- تب ۳: بهینه‌سازی و رفرش گروهی مقالات (Bulk Optimizer) -->
            <div class="smart-tab-content" id="tab-content-bulk-optimizer">
                <div class="smart-card">
                    <h2>بهینه‌سازی و احیای گروهی مقالات سایت با هوش مصنوعی</h2>
                    <p class="description">
                        می‌توانید لیستی از مقالات سایت (مثلاً مقالاتی که افت رتبه داشته‌اند یا تحلیل نشده‌اند) را فیلتر و انتخاب کنید تا هوش مصنوعی به صورت خودکار یا گام‌به‌گام عناوین، سئو، محتوا، متای توضیحات و اسکیما را ارتقا داده و به‌روزرسانی نماید.
                    </p>

                    <!-- بخش فیلتر و انتخاب مقالات -->
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:15px 20px; margin:20px 0;">
                        <h3 style="margin-top:0;">۱. انتخاب و فیلتر مقالات کاندید:</h3>
                        <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:center;">
                            <div>
                                <label><strong>نوع فیلتر:</strong></label><br>
                                <select id="bulk_filter_type" style="min-width:200px;">
                                    <option value="decayed">⚠️ مقالات کهنه با خطر افت رتبه (بیش از ۱۲۰ روز)</option>
                                    <option value="no_seo">🔍 مقالاتی که هنوز با سئو هوشمند تحلیل نشده‌اند</option>
                                    <option value="all">📚 تمام نوشته‌های منتشر شده</option>
                                </select>
                            </div>

                            <div>
                                <label><strong>دسته‌بندی موضوعی:</strong></label><br>
                                <select id="bulk_category_id" style="min-width:180px;">
                                    <option value="0">همه دسته‌ها</option>
                                    <?php
                                    $categories = get_categories( array( 'hide_empty' => false ) );
                                    foreach ( $categories as $cat ) {
                                        echo '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . ' (' . esc_html( $cat->count ) . ' نوشته)</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label><strong>تعداد دریافت:</strong></label><br>
                                <input type="number" id="bulk_limit" value="20" min="5" max="100" style="width:80px;" />
                            </div>

                            <div style="align-self:flex-end;">
                                <button type="button" class="button button-secondary" id="smart-bulk-fetch-btn" style="height:32px;">
                                    🔍 بارگذاری لیست مقالات کاندید
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- جدول مقالات انتخاب شده -->
                    <div id="bulk-candidates-container" class="smart-hidden" style="margin-bottom:25px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <h3 style="margin:0;">مقالات پیدا شده جهت انتخاب:</h3>
                            <div>
                                <button type="button" class="button button-small" id="bulk-select-all">انتخاب همه</button>
                                <button type="button" class="button button-small" id="bulk-deselect-all">لغو انتخاب</button>
                            </div>
                        </div>
                        <div style="max-height:300px; overflow-y:auto; border:1px solid #cbd5e1; border-radius:6px; background:#fff;">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><input type="checkbox" id="bulk-check-master" /></th>
                                        <th>عنوان مقاله</th>
                                        <th>کلمه کلیدی فعلی</th>
                                        <th style="width:110px;">امتیاز سئو فعلی</th>
                                        <th style="width:120px;">آخرین ویرایش</th>
                                    </tr>
                                </thead>
                                <tbody id="bulk-candidates-tbody">
                                </tbody>
                            </table>
                        </div>

                        <!-- تنظیمات نحوه اجرای هوش مصنوعی -->
                        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:15px; margin-top:15px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:15px;">
                            <div>
                                <label><strong>نوع بهینه‌سازی هوش مصنوعی:</strong></label>
                                <select id="bulk_opt_mode" style="margin-right:10px;">
                                    <option value="smart_refresh">🔄 احیا و رفرش کامل محتوا (ارتقای متن، آمارها، کادر AEO و اسکیما)</option>
                                    <option value="auto_optimize">⚡ ارتقای ساختار سئو، کلمات کلیدی، عناوین و متای توضیحات</option>
                                    <option value="update_title_meta_only">🎯 بهینه‌سازی عناوین و متای توضیحات کلیک‌خور (بدون دستکاری متن)</option>
                                </select>
                            </div>

                            <button type="button" class="button button-primary button-large" id="smart-add-selected-to-queue">
                                ➕ افزودن مقالات انتخاب‌شده به صف پردازش
                            </button>
                        </div>
                    </div>

                    <!-- صف زنده پردازش مقالات (Live Queue) -->
                    <div id="bulk-queue-section" style="margin-top:30px; border-top:2px solid #e2e8f0; padding-top:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h3 style="margin:0;">
                                📋 صف پردازش مقالات (Active Queue):
                                <span id="queue-count-badge" style="background:#3b82f6; color:#fff; padding:2px 8px; border-radius:12px; font-size:12px;">0</span>
                            </h3>

                            <div style="display:flex; gap:8px;">
                                <button type="button" class="button button-primary" id="smart-bulk-start-processing">
                                    ▶️ شروع پردازش ترتیبی در مرورگر
                                </button>
                                <button type="button" class="button button-secondary" id="smart-bulk-pause-processing" style="display:none;">
                                    ⏸️ توقف موقت
                                </button>
                                <button type="button" class="button" id="smart-bulk-clear-queue">
                                    🗑️ پاکسازی صف
                                </button>
                            </div>
                        </div>

                        <p class="description" style="margin:8px 0 15px;">
                            💡 نکته: اگر این برگه را ببندید، سیستم به لطف <strong>کرون جاب خودکار وردپرس (WP-Cron)</strong> در پس‌زمینه سرور به پردازش تک‌به‌تک مقالات صف ادامه خواهد داد.
                        </p>

                        <!-- نوار وضعیت کلی پیشرفت -->
                        <div id="bulk-progress-container" style="background:#e2e8f0; border-radius:8px; height:18px; overflow:hidden; margin-bottom:15px; display:none;">
                            <div id="bulk-progress-bar" style="background:linear-gradient(to right, #3b82f6, #10b981); height:100%; width:0%; transition:width 0.3s; text-align:center; color:#fff; font-size:11px; line-height:18px; font-weight:bold;">0%</div>
                        </div>

                        <!-- جدول آیتم‌های صف -->
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width:70px;">شناسه</th>
                                    <th>عنوان نوشته</th>
                                    <th style="width:130px;">نوع اقدام</th>
                                    <th style="width:140px;">وضعیت پردازش</th>
                                    <th style="width:100px;">امتیاز جدید</th>
                                    <th style="width:120px;">عملیات</th>
                                </tr>
                            </thead>
                            <tbody id="bulk-queue-tbody">
                                <tr><td colspan="6" style="text-align:center; color:#94a3b8; padding:20px;">هنوز مقاله‌ای در صف پردازش قرار ندارد.</td></tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
        <?php
    }
}
