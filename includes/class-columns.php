<?php
/**
 * افزودن ستون وضعیت سئو هوشمند به جدول نوشته‌ها و برگه‌ها در مدیریت وردپرس
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Columns {

    public static function init() {
        // برای نوشته‌ها
        add_filter( 'manage_post_posts_columns', array( __CLASS__, 'add_columns' ) );
        add_action( 'manage_post_posts_custom_column', array( __CLASS__, 'render_column' ), 10, 2 );

        // برای برگه‌ها (در وردپرس نام هوک اکشن manage_pages_custom_column است)
        add_filter( 'manage_page_posts_columns', array( __CLASS__, 'add_columns' ) );
        add_action( 'manage_pages_custom_column', array( __CLASS__, 'render_column' ), 10, 2 );
    }

    public static function add_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'title' === $key ) {
                $new_columns['smart_ai_seo'] = 'سئو هوشمند (AI)';
            }
        }
        return $new_columns;
    }

    public static function render_column( $column, $post_id ) {
        if ( 'smart_ai_seo' !== $column ) {
            return;
        }

        $keyword   = get_post_meta( $post_id, '_smart_ai_seo_keyword', true );
        $rank      = get_post_meta( $post_id, '_smart_ai_seo_rank', true );
        $analysis  = get_post_meta( $post_id, '_smart_ai_seo_analysis', true );

        if ( ! empty( $analysis ) && isset( $analysis['seo_score'] ) ) {
            $score = (int) $analysis['seo_score'];
            $color = '#ef4444'; // قرمز
            if ( $score >= 75 ) {
                $color = '#10b981'; // سبز
            } elseif ( $score >= 50 ) {
                $color = '#f59e0b'; // زرد / نارنجی
            }

            echo '<div style="display:flex; align-items:center; gap:6px;">';
            echo '<span style="background:' . esc_attr( $color ) . '; color:#fff; border-radius:12px; padding:2px 8px; font-weight:bold; font-size:11px;">' . esc_html( $score ) . '/100</span>';
            if ( $keyword ) {
                echo '<span style="font-size:11px; color:#64748b;" title="کلمه هدف: ' . esc_attr( $keyword ) . '">🔑 ' . esc_html( mb_substr( $keyword, 0, 15 ) ) . '</span>';
            }
            echo '</div>';

            if ( $rank ) {
                echo '<div style="font-size:10px; color:#94a3b8; margin-top:2px;">رتبه هدف: ' . esc_html( $rank ) . '</div>';
            }

            // بررسی افت و کهنگی محتوا (Content Decay Detector)
            $post_modified = get_post_modified_time( 'U', false, $post_id );
            $days_passed   = ( current_time( 'timestamp' ) - $post_modified ) / DAY_IN_SECONDS;
            if ( $days_passed > 120 ) {
                $edit_url = get_edit_post_link( $post_id );
                echo '<div style="margin-top:4px;">';
                echo '<a href="' . esc_url( $edit_url ) . '#smart_ai_seo_metabox" style="text-decoration:none; display:inline-block;" title="بیش از ۴ ماه است که این مقاله به‌روز نشده است. کلیک کنید تا در ویرایشگر با هوش مصنوعی رفرش و آپدیت شود.">';
                echo '<span style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; border-radius:4px; padding:2px 6px; font-size:10px; font-weight:600; display:inline-flex; align-items:center; gap:3px;">🔄 رفرش سئو (افت رتبه)</span>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            if ( $keyword ) {
                echo '<span style="color:#64748b; font-size:11px;">🔑 ' . esc_html( mb_substr( $keyword, 0, 15 ) ) . '</span><br>';
                echo '<span style="color:#94a3b8; font-size:10px;">در انتظار تحلیل</span>';
            } else {
                echo '<span style="color:#cbd5e1; font-size:11px;">بدون کلمه هدف</span>';
            }
        }
    }
}
