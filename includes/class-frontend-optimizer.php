<?php
/**
 * بهینه‌ساز فرانت‌اند: فهرست مطالب خودکار (TOC)، زمان مطالعه، نوار پیشرفت و بهینه‌سازی تصاویر
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Frontend_Optimizer {

    public static function init() {
        add_filter( 'the_content', array( __CLASS__, 'enhance_post_content' ), 20 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
    }

    public static function enqueue_frontend_assets() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        // استایل مینیمال نوار پیشرفت و فهرست مطالب
        wp_add_inline_style( 'wp-block-library', '
            #smart-reading-progress-bar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(to right, #3b82f6, #10b981);
                width: 0%;
                z-index: 999999;
                transition: width 0.1s ease-out;
            }
            .smart-ai-toc-box {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 16px 20px;
                margin: 25px 0;
                direction: rtl;
                text-align: right;
            }
            .smart-ai-toc-box summary {
                font-weight: bold;
                font-size: 15px;
                cursor: pointer;
                color: #1e293b;
            }
            .smart-ai-toc-box ul {
                margin: 12px 15px 0 0;
                padding: 0;
                list-style: decimal;
            }
            .smart-ai-toc-box li {
                margin-bottom: 6px;
            }
            .smart-ai-toc-box a {
                color: #2563eb;
                text-decoration: none;
            }
            .smart-ai-toc-box a:hover {
                text-decoration: underline;
            }
            .smart-reading-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #eff6ff;
                color: #1d4ed8;
                padding: 4px 12px;
                border-radius: 16px;
                font-size: 12.5px;
                margin-bottom: 20px;
                font-weight: 500;
            }
            .smart-aeo-direct-answer {
                background: #fdf4ff;
                border-right: 4px solid #a855f7;
                padding: 14px 18px;
                border-radius: 6px;
                margin: 20px 0;
                font-size: 14.5px;
                line-height: 1.8;
                color: #581c87;
            }
        ' );

        // جاوااسکریپت نوار پیشرفت مطالعه
        wp_add_inline_script( 'jquery', "
            document.addEventListener('DOMContentLoaded', function() {
                var bar = document.createElement('div');
                bar.id = 'smart-reading-progress-bar';
                document.body.appendChild(bar);

                window.addEventListener('scroll', function() {
                    var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    var scrolled = (winScroll / height) * 100;
                    bar.style.width = scrolled + '%';
                });
            });
        " );
    }

    /**
     * تزریق زمان مطالعه، فهرست مطالب (TOC) و بهینه‌سازی تصاویر داخل محتوا
     */
    public static function enhance_post_content( $content ) {
        if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        // ۱. محاسبه تخمین زمان مطالعه (بر اساس سرعت میانگین ۲۰۰ کلمه در دقیقه)
        $clean_words = str_word_count( wp_strip_all_tags( $content ) );
        if ( $clean_words < 10 ) {
            // برای متون فارسی که str_word_count دقیق نیست
            $clean_words = count( preg_split( '/\s+/u', wp_strip_all_tags( $content ) ) );
        }
        $read_time = max( 1, ceil( $clean_words / 200 ) );

        $reading_badge = '<div class="smart-reading-badge">⏱️ زمان تقریبی مطالعه: ' . $read_time . ' دقیقه (' . $clean_words . ' کلمه)</div>';

        // ۲. استخراج H2ها و ساخت جدول فهرست مطالب (TOC) با لینک لنگری (Anchor Links)
        $toc_html = '';
        if ( preg_match_all( '/<h2([^>]*)>(.*?)<\/h2>/iu', $content, $matches, PREG_SET_ORDER ) ) {
            if ( count( $matches ) >= 2 ) {
                $toc_items = '';
                $index = 1;
                foreach ( $matches as $match ) {
                    $original_h2 = $match[0];
                    $h2_text = wp_strip_all_tags( $match[2] );
                    $anchor_id = 'heading-' . $index . '-' . sanitize_title( $h2_text );

                    $replacement_h2 = '<h2' . $match[1] . ' id="' . esc_attr( $anchor_id ) . '">' . $match[2] . '</h2>';
                    $content = substr_replace( $content, $replacement_h2, strpos( $content, $original_h2 ), strlen( $original_h2 ) );

                    $toc_items .= '<li><a href="#' . esc_attr( $anchor_id ) . '">' . esc_html( $h2_text ) . '</a></li>';
                    $index++;
                }

                $toc_html = '<details class="smart-ai-toc-box" open>'
                          . '<summary>📋 فهرست مطالب این مقاله (دسترسی سریع)</summary>'
                          . '<ul>' . $toc_items . '</ul>'
                          . '</details>';
            }
        }

        // ۳. بهینه‌سازی تگ‌های تصویر (افزودن خودکار loading="lazy" و decoding="async")
        $content = preg_replace_callback( '/<img([^>]*)>/iu', function( $m ) {
            $tag = $m[1];
            if ( strpos( $tag, 'loading=' ) === false ) {
                $tag .= ' loading="lazy"';
            }
            if ( strpos( $tag, 'decoding=' ) === false ) {
                $tag .= ' decoding="async"';
            }
            return '<img' . $tag . '>';
        }, $content );

        return $reading_badge . $toc_html . $content;
    }
}
