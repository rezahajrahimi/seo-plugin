<?php
/**
 * مدیریت درخواست‌های Ajax برای تحلیل سئو و تست اتصال
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Ajax_Handler {

    public static function init() {
        add_action( 'wp_ajax_smart_ai_seo_analyze', array( __CLASS__, 'handle_analysis' ) );
        add_action( 'wp_ajax_smart_ai_seo_test_connection', array( __CLASS__, 'handle_test_connection' ) );
        add_action( 'wp_ajax_smart_ai_seo_sync_plugin', array( __CLASS__, 'handle_sync_plugin' ) );
        add_action( 'wp_ajax_smart_ai_seo_backup_content', array( __CLASS__, 'handle_backup_content' ) );
        add_action( 'wp_ajax_smart_ai_seo_restore_content', array( __CLASS__, 'handle_restore_content' ) );
        add_action( 'wp_ajax_smart_ai_seo_search_posts', array( __CLASS__, 'handle_search_posts' ) );
        add_action( 'wp_ajax_smart_ai_seo_gen_single', array( __CLASS__, 'handle_gen_single' ) );
        add_action( 'wp_ajax_smart_ai_seo_gen_cluster_plan', array( __CLASS__, 'handle_gen_cluster_plan' ) );
    }

    /**
     * تولید مقاله تکی و ساخت نوشته پیش‌نویس (Draft) در وردپرس با تمام متادیتاها
     */
    public static function handle_gen_single() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $keyword      = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
        $lsi          = isset( $_POST['lsi'] ) ? sanitize_text_field( wp_unslash( $_POST['lsi'] ) ) : '';
        $tone         = isset( $_POST['tone'] ) ? sanitize_text_field( wp_unslash( $_POST['tone'] ) ) : 'professional';
        $instructions = isset( $_POST['instructions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['instructions'] ) ) : '';
        $status       = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'draft';
        $category_id  = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
        $image_source = isset( $_POST['image_source'] ) ? sanitize_text_field( $_POST['image_source'] ) : 'ai_pollinations';
        $schedule_day = isset( $_POST['schedule_days'] ) ? intval( $_POST['schedule_days'] ) : 0;

        $strategy = array(
            'goal'             => isset( $_POST['goal'] ) ? sanitize_text_field( $_POST['goal'] ) : 'traffic',
            'audience'         => isset( $_POST['audience'] ) ? sanitize_text_field( wp_unslash( $_POST['audience'] ) ) : '',
            'cta_url'          => isset( $_POST['cta_url'] ) ? esc_url_raw( wp_unslash( $_POST['cta_url'] ) ) : '',
            'cta_anchor'       => isset( $_POST['cta_anchor'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_anchor'] ) ) : '',
            'source_reference' => isset( $_POST['source_reference'] ) ? sanitize_textarea_field( wp_unslash( $_POST['source_reference'] ) ) : '',
            'length'           => isset( $_POST['length'] ) ? sanitize_text_field( $_POST['length'] ) : 'standard',
        );

        if ( empty( $keyword ) ) {
            wp_send_json_error( array( 'message' => 'لطفاً کلمه کلیدی اصلی را وارد کنید.' ) );
        }

        $result = Smart_AI_SEO_AI_Service::generate_single_article( $keyword, $lsi, $tone, $instructions, $strategy );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $title   = ! empty( $result['title'] ) ? sanitize_text_field( $result['title'] ) : $keyword;
        $slug    = ! empty( $result['slug'] ) ? sanitize_title( $result['slug'] ) : sanitize_title( $keyword );
        $content = ! empty( $result['content'] ) ? wp_kses_post( $result['content'] ) : '';

        // تضمین قطعی درج لینک هدف: اگر مدل لینک را نذاشته بود، آن را در انتهای مقاله قرار بده
        if ( ! empty( $strategy['cta_url'] ) && strpos( $content, $strategy['cta_url'] ) === false ) {
            $cta_anchor = ! empty( $strategy['cta_anchor'] ) ? $strategy['cta_anchor'] : 'مشاهده و کسب اطلاعات بیشتر';
            $cta_box = '<div style="background:#eff6ff; border-right:4px solid #2563eb; padding:16px; margin:25px 0; border-radius:6px; font-size:14px; line-height:1.8;">'
                     . '<p style="margin:0 0 10px; font-weight:bold; color:#1e3a8a;">پیشنهاد ویژه و دسترسی سریع:</p>'
                     . '<a href="' . esc_url( $strategy['cta_url'] ) . '" style="display:inline-block; background:#2563eb; color:#ffffff; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:bold;">' . esc_html( $cta_anchor ) . '</a>'
                     . '</div>';
            $content .= $cta_box;
        }

        $post_status = ( 'publish' === $status ) ? 'publish' : 'draft';

        $post_data = array(
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => $post_status,
            'post_type'    => 'post',
        );

        // تخصیص دسته‌بندی در صورت انتخاب
        if ( $category_id > 0 ) {
            $post_data['post_category'] = array( $category_id );
        }

        // زمان‌بندی تدریجی در آینده در صورت تعیین روز
        if ( $schedule_day > 0 ) {
            $future_time = strtotime( "+{$schedule_day} days", current_time( 'timestamp' ) );
            $post_data['post_status']   = 'future';
            $post_data['post_date']     = date( 'Y-m-d H:i:s', $future_time );
            $post_data['post_date_gmt'] = get_gmt_from_date( date( 'Y-m-d H:i:s', $future_time ) );
        }

        $new_post_id = wp_insert_post( $post_data );
        if ( is_wp_error( $new_post_id ) ) {
            wp_send_json_error( array( 'message' => 'خطا در ایجاد نوشته در دیتابیس: ' . $new_post_id->get_error_message() ) );
        }

        // متاداده‌های سئو و اسکیما
        update_post_meta( $new_post_id, '_smart_ai_seo_keyword', $keyword );
        if ( ! empty( $result['meta_description'] ) ) {
            // ذخیره در Rank Math و Yoast
            update_post_meta( $new_post_id, 'rank_math_description', sanitize_textarea_field( $result['meta_description'] ) );
            update_post_meta( $new_post_id, 'rank_math_focus_keyword', $keyword );
            update_post_meta( $new_post_id, '_yoast_wpseo_metadesc', sanitize_textarea_field( $result['meta_description'] ) );
            update_post_meta( $new_post_id, '_yoast_wpseo_focuskw', $keyword );
        }

        // تولید و الصاق تصویر شاخص
        $image_attached = false;
        $image_prompt   = ! empty( $result['image_prompt'] ) ? $result['image_prompt'] : '';
        if ( 'none' !== $image_source ) {
            $img_res = Smart_AI_SEO_Image_Service::attach_featured_image( $new_post_id, $keyword, $image_source, $image_prompt );
            if ( ! is_wp_error( $img_res ) && $img_res ) {
                $image_attached = true;
            }
        }

        // اسکیما FAQ
        if ( ! empty( $result['schema_faq'] ) ) {
            update_post_meta( $new_post_id, '_smart_ai_seo_enable_faq_schema', 'yes' );
            update_post_meta( $new_post_id, '_smart_ai_seo_analysis', array(
                'schema_faq' => $result['schema_faq'],
                'seo_score'  => 85,
            ) );
        }

        wp_send_json_success( array(
            'post_id'      => $new_post_id,
            'title'        => $title,
            'slug'         => $slug,
            'meta_desc'    => $result['meta_description'] ?? '',
            'edit_url'     => get_edit_post_link( $new_post_id, 'raw' ),
            'preview_url'  => get_preview_post_link( $new_post_id ),
            'message'      => 'مقاله با موفقیت ایجاد شد!',
        ) );
    }

    /**
     * طراحی استراتژی خوشه‌ای
     */
    public static function handle_gen_cluster_plan() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $core_topic = isset( $_POST['core_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['core_topic'] ) ) : '';
        $count      = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 5;

        $strategy = array(
            'goal'             => isset( $_POST['goal'] ) ? sanitize_text_field( $_POST['goal'] ) : 'traffic',
            'audience'         => isset( $_POST['audience'] ) ? sanitize_text_field( wp_unslash( $_POST['audience'] ) ) : '',
            'cta_url'          => isset( $_POST['cta_url'] ) ? esc_url_raw( wp_unslash( $_POST['cta_url'] ) ) : '',
            'cta_anchor'       => isset( $_POST['cta_anchor'] ) ? sanitize_text_field( wp_unslash( $_POST['cta_anchor'] ) ) : '',
            'source_reference' => isset( $_POST['source_reference'] ) ? sanitize_textarea_field( wp_unslash( $_POST['source_reference'] ) ) : '',
        );

        if ( empty( $core_topic ) ) {
            wp_send_json_error( array( 'message' => 'موضوع کلان اصلی را وارد نمایید.' ) );
        }

        $result = Smart_AI_SEO_AI_Service::generate_cluster_plan( $core_topic, $count, $strategy );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'plan' => $result,
        ) );
    }

    /**
     * جستجوی مقالات منتشر شده سایت جهت یافتن لینک دقیق موضوع پیشنهادی
     */
    public static function handle_search_posts() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error();
        }

        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
        if ( empty( $query ) ) {
            wp_send_json_error( array( 'message' => 'عبارت جستجو خالی است.' ) );
        }

        $posts = get_posts( array(
            's'              => $query,
            'post_type'      => array( 'post', 'page' ),
            'post_status'    => 'publish',
            'posts_per_page' => 5,
        ) );

        $results = array();
        foreach ( $posts as $p ) {
            $results[] = array(
                'id'    => $p->ID,
                'title' => get_the_title( $p ),
                'url'   => get_permalink( $p ),
            );
        }

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * ذخیره نسخه پشتیبان قبل از اعمال متن جدید
     */
    public static function handle_backup_content() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';
        $title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

        if ( $post_id && ! empty( $content ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_backup_content', $content );
            update_post_meta( $post_id, '_smart_ai_seo_backup_title', $title );
            update_post_meta( $post_id, '_smart_ai_seo_backup_time', current_time( 'mysql' ) );
            wp_send_json_success( array( 'message' => 'نسخه قبلی ذخیره شد.' ) );
        }

        wp_send_json_error();
    }

    /**
     * بازیابی نسخه پشتیبان قبلی
     */
    public static function handle_restore_content() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error();
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'نوشته معتبر نیست.' ) );
        }

        $backup_content = get_post_meta( $post_id, '_smart_ai_seo_backup_content', true );
        $backup_title   = get_post_meta( $post_id, '_smart_ai_seo_backup_title', true );
        $backup_time    = get_post_meta( $post_id, '_smart_ai_seo_backup_time', true );

        if ( empty( $backup_content ) ) {
            wp_send_json_error( array( 'message' => 'هیچ نسخه پشتیبانی یافت نشد.' ) );
        }

        wp_send_json_success( array(
            'content' => $backup_content,
            'title'   => $backup_title,
            'time'    => $backup_time,
            'message' => 'نسخه قبلی با موفقیت آماده بازگردانی شد.',
        ) );
    }

    /**
     * همگام‌سازی مستقیم با افزونه‌های رنک‌مث (Rank Math) و یوست سئو (Yoast SEO)
     */
    public static function handle_sync_plugin() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $post_id     = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $target_tool = isset( $_POST['target_tool'] ) ? sanitize_text_field( $_POST['target_tool'] ) : 'rank_math';
        $title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $desc        = isset( $_POST['desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['desc'] ) ) : '';
        $keyword     = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'شناسه نوشته نامعتبر است.' ) );
        }

        if ( 'rank_math' === $target_tool ) {
            // متای Rank Math
            if ( ! empty( $title ) ) {
                update_post_meta( $post_id, 'rank_math_title', $title );
            }
            if ( ! empty( $desc ) ) {
                update_post_meta( $post_id, 'rank_math_description', $desc );
            }
            if ( ! empty( $keyword ) ) {
                update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
            }
            wp_send_json_success( array( 'message' => 'اطلاعات با موفقیت در فیلدهای Rank Math ذخیره شد!' ) );
        } elseif ( 'yoast' === $target_tool ) {
            // متای Yoast SEO
            if ( ! empty( $title ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_title', $title );
            }
            if ( ! empty( $desc ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_metadesc', $desc );
            }
            if ( ! empty( $keyword ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyword );
            }
            wp_send_json_success( array( 'message' => 'اطلاعات با موفقیت در فیلدهای Yoast SEO ذخیره شد!' ) );
        }

        wp_send_json_error( array( 'message' => 'افزونه مقصد نامعتبر است.' ) );
    }

    /**
     * پردازش درخواست تحلیل سئو
     */
    public static function handle_analysis() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'شما اجازه انجام این کار را ندارید.' ) );
        }

        $post_id           = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $keyword           = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
        $current_rank      = isset( $_POST['rank'] ) ? sanitize_text_field( wp_unslash( $_POST['rank'] ) ) : '';
        $custom_prompt     = isset( $_POST['custom_prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_prompt'] ) ) : '';
        $title             = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $content           = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

        if ( empty( $keyword ) ) {
            wp_send_json_error( array( 'message' => 'لطفاً کلمه کلیدی هدف را مشخص کنید.' ) );
        }

        if ( empty( $content ) ) {
            wp_send_json_error( array( 'message' => 'متن نوشته خالی است. لطفاً ابتدا مقداری محتوا بنویسید.' ) );
        }

        // ذخیره کلمه، رتبه و پرامپت در متای نوشته
        if ( $post_id ) {
            update_post_meta( $post_id, '_smart_ai_seo_keyword', $keyword );
            update_post_meta( $post_id, '_smart_ai_seo_rank', $current_rank );
            update_post_meta( $post_id, '_smart_ai_seo_custom_prompt', $custom_prompt );
        }

        $result = Smart_AI_SEO_AI_Service::analyze_and_optimize( $keyword, $current_rank, $title, $content, $custom_prompt );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // ذخیره نتیجه آخرین آنالیز در متای پست
        if ( $post_id ) {
            update_post_meta( $post_id, '_smart_ai_seo_analysis', $result );
        }

        wp_send_json_success( array(
            'data'    => $result,
            'message' => 'تحلیل با موفقیت انجام شد.',
        ) );
    }

    /**
     * تست اتصال به هوش مصنوعی در صفحه تنظیمات
     */
    public static function handle_test_connection() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $result = Smart_AI_SEO_AI_Service::analyze_and_optimize(
            'تست سئو',
            '10',
            'تست سلامت ارتباط هوش مصنوعی',
            '<p>این یک متن کوتاه برای سنجش ارتباط افزونه وردپرس با سرویس هوش مصنوعی انتخابی شماست.</p>'
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => 'خطا در برقراری ارتباط: ' . $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => 'ارتباط با هوش مصنوعی کاملاً پایدار و موفقیت‌آمیز است!' ) );
    }
}
