<?php
/**
 * جعبه متای اختصاصی در ویرایشگر نوشته‌های وردپرس
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Metabox {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_metabox' ) );
        add_action( 'save_post', array( __CLASS__, 'save_post_meta' ) );
    }

    public static function add_metabox() {
        $screens = array( 'post', 'page' );
        foreach ( $screens as $screen ) {
            add_meta_box(
                'smart_ai_seo_box',
                '⚡ بهینه‌ساز هوشمند سئو با هوش مصنوعی (Rank Booster)',
                array( __CLASS__, 'render_metabox' ),
                $screen,
                'normal',
                'high'
            );
        }
    }

    public static function render_metabox( $post ) {
        wp_nonce_field( 'smart_ai_seo_meta_action', 'smart_ai_seo_meta_nonce' );

        $target_keyword = get_post_meta( $post->ID, '_smart_ai_seo_keyword', true );
        $target_rank    = get_post_meta( $post->ID, '_smart_ai_seo_rank', true );
        $custom_prompt  = get_post_meta( $post->ID, '_smart_ai_seo_custom_prompt', true );
        $last_analysis  = get_post_meta( $post->ID, '_smart_ai_seo_analysis', true );

        // بررسی افت محتوا (بیش از ۱۲۰ روز از آخرین ویرایش)
        $post_modified = get_post_modified_time( 'U', false, $post->ID );
        $days_passed   = ( current_time( 'timestamp' ) - $post_modified ) / DAY_IN_SECONDS;
        $is_decayed    = ( $days_passed > 120 );
        ?>
        <div class="smart-ai-seo-panel" id="smart_ai_seo_metabox">
            <?php if ( $is_decayed ) : ?>
                <div class="smart-decay-alert" style="background:#fff1f2; border:1px solid #fecdd3; border-right:4px solid #e11d48; border-radius:6px; padding:12px 16px; margin-bottom:15px; display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <div>
                        <strong style="color:#9f1239; font-size:13px;">⚠️ هشدار افت سئو (Content Decay Detected):</strong>
                        <p style="margin:4px 0 0; color:#4c0519; font-size:12px;">بیش از <?php echo (int) $days_passed; ?> روز از آخرین به‌روزرسانی این مقاله گذشته است. الگوریتم تازگی (Freshness) گوگل مقالات قدیمی‌تر را در نتایج کاهش می‌دهد.</p>
                    </div>
                    <button type="button" class="button button-secondary" id="smart-quick-decay-refresh-btn" style="white-space:nowrap; background:#ffffff; border-color:#fda4af; color:#be123c; font-weight:bold;">
                        🔄 پرامپت آماده برای رفرش محتوا
                    </button>
                </div>
            <?php endif; ?>
            <div class="smart-panel-inputs">
                <div class="smart-input-group">
                    <label for="smart_target_keyword"><strong>کلمه کلیدی هدف (Focus Keyword):</strong></label>
                    <input type="text" id="smart_target_keyword" name="smart_target_keyword" value="<?php echo esc_attr( $target_keyword ); ?>" class="large-text" placeholder="مثلاً: خرید گوشی سامسونگ" />
                </div>

                <div class="smart-input-group">
                    <label for="smart_target_rank"><strong>جایگاه فعلی در گوگل (Position / Rank):</strong></label>
                    <input type="text" id="smart_target_rank" name="smart_target_rank" value="<?php echo esc_attr( $target_rank ); ?>" class="regular-text" placeholder="مثلاً: رتبه 7 یا صفحه 2" />
                    <span class="description">جایگاه فعلی به هوش مصنوعی کمک می‌کند استراتژی سبقت از رقبا را دقیق‌تر تعیین کند.</span>
                </div>

                <div class="smart-input-group">
                    <label for="smart_custom_prompt"><strong>پرامپت و دستورات سفارشی برای این نوشته (اختیاری):</strong></label>
                    <textarea id="smart_custom_prompt" name="smart_custom_prompt" rows="3" class="large-text" placeholder="مثلاً: روی مزایا و معایب بیشتر تمرکز کن، جدول مقایسه‌ای ایجاد کن، بخش سوالات متداول (FAQ) به انتهای متن اضافه نما و لحن ترغیب‌کننده داشته باش..."><?php echo esc_textarea( $custom_prompt ); ?></textarea>
                    <span class="description">هرگونه خواسته یا دستور اختصاصی که می‌خواهید هوش مصنوعی هنگام تحلیل و بازنویسی متن مدنظر قرار دهد.</span>
                </div>

                <div class="smart-action-row">
                    <button type="button" id="smart-ai-start-analysis" class="button button-primary button-hero">
                        <span class="dashicons dashicons-search" style="vertical-align: middle;"></span>
                        تحلیل مشکلات و ایجاد نسخه بهینه‌شده
                    </button>
                    <span class="spinner" id="smart-ai-spinner"></span>
                </div>
            </div>

            <!-- محفظه نمایش نتایج و پیشنهادات -->
            <div id="smart-ai-results-container" class="<?php echo empty( $last_analysis ) ? 'smart-hidden' : ''; ?>">
                <hr class="smart-divider" />
                
                <div class="smart-results-header">
                    <h3>نتایج بررسی و پیشنهادهای بهبود هوش مصنوعی</h3>
                    <div class="smart-score-badge" id="smart-score-badge">
                        امتیاز تخمینی سئو: <span id="smart-score-value"><?php echo ! empty( $last_analysis['seo_score'] ) ? esc_html( $last_analysis['seo_score'] ) : '--'; ?></span>/100
                    </div>
                </div>

                <div class="smart-summary-box" id="smart-summary-box">
                    <strong>بررسی کلی:</strong>
                    <p id="smart-summary-text"><?php echo ! empty( $last_analysis['summary'] ) ? esc_html( $last_analysis['summary'] ) : ''; ?></p>
                </div>

                <!-- تب‌های تفکیکی -->
                <div class="smart-tabs-nav">
                    <button type="button" class="smart-tab-btn active" data-tab="issues">⚠️ مشکلات (<span id="issues-count"><?php echo ! empty( $last_analysis['issues'] ) ? count( $last_analysis['issues'] ) : 0; ?></span>)</button>
                    <button type="button" class="smart-tab-btn" data-tab="recommendations">💡 کارهای لازم (<span id="recomms-count"><?php echo ! empty( $last_analysis['recommendations'] ) ? count( $last_analysis['recommendations'] ) : 0; ?></span>)</button>
                    <button type="button" class="smart-tab-btn" data-tab="lsi">🔑 کلمات کلیدی مکمل (LSI)</button>
                    <button type="button" class="smart-tab-btn" data-tab="linking">🔗 لینک‌سازی داخلی</button>
                    <button type="button" class="smart-tab-btn" data-tab="schema">🏷️ اسکیما (FAQ Schema)</button>
                    <button type="button" class="smart-tab-btn" data-tab="preview">📝 پیش‌نمایش، همگام‌سازی و اعمال</button>
                </div>

                <!-- محتوای تب مشکلات -->
                <div class="smart-tab-content active" id="tab-content-issues">
                    <ul class="smart-issues-list" id="smart-issues-list">
                        <?php if ( ! empty( $last_analysis['issues'] ) && is_array( $last_analysis['issues'] ) ) : ?>
                            <?php foreach ( $last_analysis['issues'] as $issue ) : ?>
                                <li class="issue-item issue-<?php echo esc_attr( $issue['type'] ?? 'warning' ); ?>">
                                    <strong><?php echo esc_html( $issue['title'] ?? '' ); ?>:</strong>
                                    <span><?php echo esc_html( $issue['description'] ?? '' ); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- محتوای تب توصیه‌ها -->
                <div class="smart-tab-content" id="tab-content-recommendations">
                    <ul class="smart-recomms-list" id="smart-recomms-list">
                        <?php if ( ! empty( $last_analysis['recommendations'] ) && is_array( $last_analysis['recommendations'] ) ) : ?>
                            <?php foreach ( $last_analysis['recommendations'] as $rec ) : ?>
                                <li class="recomm-item priority-<?php echo esc_attr( $rec['priority'] ?? 'medium' ); ?>">
                                    <span class="badge"><?php echo esc_html( strtoupper( $rec['priority'] ?? 'MEDIUM' ) ); ?></span>
                                    <strong><?php echo esc_html( $rec['action'] ?? '' ); ?></strong>
                                    <p class="impact"><?php echo esc_html( $rec['impact'] ?? '' ); ?></p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- محتوای تب کلمات LSI -->
                <div class="smart-tab-content" id="tab-content-lsi">
                    <div class="smart-lsi-header-info">
                        <p class="description">کلمات کلیدی مکمل و معنایی که پوشش آنها به گوگل نشان می‌دهد محتوای شما کامل‌ترین راهنما در سطح وب است. در نسخه بهینه‌شده (تب پیش‌نمایش) این کلمات اعمال شده‌اند؛ همچنین می‌توانید با کلیک روی دکمه <strong>«+ درج در متن»</strong>، آن را در انتهای پاراگراف مناسب ویرایشگر درج کنید.</p>
                    </div>
                    <div class="smart-lsi-container" id="smart-lsi-container">
                        <?php if ( ! empty( $last_analysis['lsi_keywords'] ) && is_array( $last_analysis['lsi_keywords'] ) ) : ?>
                            <?php foreach ( $last_analysis['lsi_keywords'] as $lsi ) : ?>
                                <div class="smart-lsi-chip <?php echo ! empty( $lsi['found_in_content'] ) ? 'is-found' : 'is-missing'; ?>" data-keyword="<?php echo esc_attr( $lsi['keyword'] ?? '' ); ?>">
                                    <div class="lsi-top-row">
                                        <span class="lsi-status"><?php echo ! empty( $lsi['found_in_content'] ) ? '✓ در متن موجود است' : '+ نیاز به درج'; ?></span>
                                        <button type="button" class="smart-insert-lsi-btn button button-small" title="درج این عبارت در انتهای متن نوشته">
                                            + درج در متن
                                        </button>
                                    </div>
                                    <strong><?php echo esc_html( $lsi['keyword'] ?? '' ); ?></strong>
                                    <?php if ( ! empty( $lsi['suggestion'] ) ) : ?>
                                        <small><?php echo esc_html( $lsi['suggestion'] ); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description">پس از تحلیل، کلمات کلیدی LSI اینجا ظاهر می‌شوند.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- محتوای تب لینک‌سازی داخلی -->
                <div class="smart-tab-content" id="tab-content-linking">
                    <p class="description">موقعیت‌های استراتژیک در متن جهت ایجاد پیوند داخلی به سایر مقالات سایت شما. می‌توانید با زدن دکمه <strong>«🔗 تبدیل به لینک در متن»</strong>، انکر تکست را در متن یافته و به مقاله مربوطه لینک دهید یا با زدن <strong>«کپی انکرتکست»</strong> دستی لینک کنید:</p>
                    <ul class="smart-linking-list" id="smart-linking-list">
                        <?php if ( ! empty( $last_analysis['internal_linking'] ) && is_array( $last_analysis['internal_linking'] ) ) : ?>
                            <?php foreach ( $last_analysis['internal_linking'] as $link_item ) : ?>
                                <li class="linking-item" data-anchor="<?php echo esc_attr( $link_item['anchor_text'] ?? '' ); ?>" data-topic="<?php echo esc_attr( $link_item['target_topic'] ?? '' ); ?>">
                                    <div class="linking-info">
                                        <span class="dashicons dashicons-admin-links"></span>
                                        انکر تکست پیشنهادی: <code><?php echo esc_html( $link_item['anchor_text'] ?? '' ); ?></code>
                                        <span class="target-topic">👈 پیوند به مقاله مرتبط با موضوع: <strong><?php echo esc_html( $link_item['target_topic'] ?? '' ); ?></strong></span>
                                    </div>
                                    <div class="linking-actions">
                                        <button type="button" class="smart-auto-link-btn button button-small button-primary" title="یافتن انکر تکست در متن و تبدیل آن به پیوند">
                                            🔗 لینک‌دار کردن در متن
                                        </button>
                                        <button type="button" class="smart-copy-anchor-btn button button-small" title="کپی انکر تکست">
                                            کپی عبارت
                                        </button>
                                    </div>
                                    <div class="linking-quick-search-result"></div>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description">پس از تحلیل، پیشنهادهای انکرتکست و صفحات مرتبط اینجا قرار می‌گیرد.</p>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- محتوای تب اسکیما -->
                <div class="smart-tab-content" id="tab-content-schema">
                    <?php
                    $enable_schema = get_post_meta( $post->ID, '_smart_ai_seo_enable_faq_schema', true );
                    ?>
                    <div class="smart-schema-activation">
                        <label>
                            <input type="checkbox" id="smart_enable_faq_schema" name="smart_enable_faq_schema" value="yes" <?php checked( $enable_schema, 'yes' ); ?> />
                            <strong>تزریق خودکار اسکیما FAQ (سوالات متداول) در هدر این برگه (Rich Snippets گوگل)</strong>
                        </label>
                    </div>

                    <div id="smart-schema-faq-list" class="smart-faq-list">
                        <?php if ( ! empty( $last_analysis['schema_faq'] ) && is_array( $last_analysis['schema_faq'] ) ) : ?>
                            <?php foreach ( $last_analysis['schema_faq'] as $faq ) : ?>
                                <div class="faq-card">
                                    <div class="faq-q">❓ <?php echo esc_html( $faq['question'] ?? '' ); ?></div>
                                    <div class="faq-a">💡 <?php echo esc_html( $faq['answer'] ?? '' ); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description">پس از تحلیل، سوالات متداول سئو شده جهت ساخت اسکیما اینجا نمایش داده می‌شوند.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- محتوای تب پیش‌نمایش، همگام سازی و اعمال -->
                <div class="smart-tab-content" id="tab-content-preview">
                    <div class="smart-meta-suggestions">
                        <div class="suggestion-item">
                            <label><strong>عنوان بهینه‌شده پیشنهادی:</strong></label>
                            <div class="input-with-action">
                                <input type="text" id="smart-suggested-title" readonly value="<?php echo ! empty( $last_analysis['optimized_title'] ) ? esc_attr( $last_analysis['optimized_title'] ) : ''; ?>" class="large-text" />
                                <button type="button" class="button" id="smart-apply-title-btn">جایگزینی در عنوان نوشته</button>
                            </div>

                            <!-- گزینه‌های جایگزین عنوان با نرخ کلیک بالا (CTR Booster Variants) -->
                            <div id="smart-title-variants-wrap" class="smart-title-variants-box" style="margin-top: 10px; background: #ffffff; padding: 10px 14px; border: 1px dashed #cbd5e1; border-radius: 6px;">
                                <div style="font-size: 12.5px; font-weight: bold; color: #334155; margin-bottom: 6px;">
                                    🎯 پیشنهادهای جایگزین عنوان برای انفجار نرخ کلیک (CTR Booster):
                                </div>
                                <div id="smart-title-variants-list" class="title-variants-list">
                                    <?php if ( ! empty( $last_analysis['title_variants'] ) && is_array( $last_analysis['title_variants'] ) ) : ?>
                                        <?php foreach ( $last_analysis['title_variants'] as $t_var ) : ?>
                                            <div class="title-variant-row" style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; padding:4px 0; border-bottom:1px solid #f1f5f9;">
                                                <div style="font-size: 12.5px;">
                                                    <span class="variant-badge" style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:bold;"><?php echo esc_html( $t_var['type'] ?? 'پیشنهادی' ); ?></span>
                                                    <span class="variant-text"><?php echo esc_html( $t_var['title'] ?? '' ); ?></span>
                                                </div>
                                                <button type="button" class="button button-small smart-pick-variant-btn" data-title="<?php echo esc_attr( $t_var['title'] ?? '' ); ?>">انتخاب این عنوان</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="description" style="margin:0;">پس از اجرای تحلیل یا تولید محتوا، گزینه‌های کلیک‌خور فکت‌محور، کنجکاوی و جامع اینجا نمایش داده می‌شوند.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="suggestion-item">
                            <label><strong>متای توضیحات سئو:</strong></label>
                            <textarea id="smart-suggested-meta" readonly rows="2" class="large-text"><?php echo ! empty( $last_analysis['meta_description'] ) ? esc_textarea( $last_analysis['meta_description'] ) : ''; ?></textarea>
                            
                            <!-- دکمه‌های همگام‌سازی مستقیم با افزونه‌های سئو -->
                            <div class="smart-sync-actions">
                                <span>همگام‌سازی مستقیم یک‌کلیکه:</span>
                                <button type="button" class="button button-secondary" id="smart-sync-rankmath-btn">
                                    🟢 انتقال به Rank Math
                                </button>
                                <button type="button" class="button button-secondary" id="smart-sync-yoast-btn">
                                    🟣 انتقال به Yoast SEO
                                </button>
                                <span id="smart-sync-status" style="margin-right: 10px; font-weight: 500;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="smart-content-preview-box">
                        <div class="preview-actions-bar">
                            <h4>پیش‌نمایش محتوای بازنویسی و اصلاح شده:</h4>
                            <div class="action-buttons-group">
                                <button type="button" class="button button-primary button-large" id="smart-apply-content-btn">
                                    🚀 جایگزینی این محتوای بهینه‌شده در متن اصلی نوشته
                                </button>
                                <button type="button" class="button button-secondary" id="smart-restore-content-btn" title="بازگردانی متنی که قبل از آخرین اعمال وجود داشت">
                                    ↩️ بازگردانی متن قبلی (Undo)
                                </button>
                            </div>
                        </div>
                        <div id="smart-optimized-preview" class="smart-preview-area">
                            <?php echo ! empty( $last_analysis['optimized_content'] ) ? wp_kses_post( $last_analysis['optimized_content'] ) : ''; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    public static function save_post_meta( $post_id ) {
        if ( ! isset( $_POST['smart_ai_seo_meta_nonce'] ) || ! wp_verify_nonce( $_POST['smart_ai_seo_meta_nonce'], 'smart_ai_seo_meta_action' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['smart_target_keyword'] ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_keyword', sanitize_text_field( $_POST['smart_target_keyword'] ) );
        }

        if ( isset( $_POST['smart_target_rank'] ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_rank', sanitize_text_field( $_POST['smart_target_rank'] ) );
        }

        if ( isset( $_POST['smart_custom_prompt'] ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_custom_prompt', sanitize_textarea_field( $_POST['smart_custom_prompt'] ) );
        }

        if ( isset( $_POST['smart_enable_faq_schema'] ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_enable_faq_schema', 'yes' );
        } else {
            delete_post_meta( $post_id, '_smart_ai_seo_enable_faq_schema' );
        }
    }
}
