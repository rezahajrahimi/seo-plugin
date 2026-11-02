<?php
/**
 * مدیریت پردازش گروهی (Bulk Optimization) مقالات سایت
 * با پشتیبانی از صف زنده مرورگر و کرون جاب خودکار (WP-Cron)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Bulk_Optimizer {

    const OPTION_QUEUE = 'smart_ai_seo_bulk_queue';
    const CRON_HOOK    = 'smart_ai_seo_process_cron_queue';

    public static function init() {
        // ثبت کرون جاب زمان‌بندی شده برای پردازش پس‌زمینه
        add_action( self::CRON_HOOK, array( __CLASS__, 'process_cron_item' ) );

        // اضافه کردن اکشن‌های گروهی به لیست نوشته‌ها و برگه‌ها
        add_filter( 'bulk_actions-edit-post', array( __CLASS__, 'register_bulk_actions' ) );
        add_filter( 'bulk_actions-edit-page', array( __CLASS__, 'register_bulk_actions' ) );
        add_filter( 'handle_bulk_actions-edit-post', array( __CLASS__, 'handle_bulk_action_redirect' ), 10, 3 );
        add_filter( 'handle_bulk_actions-edit-page', array( __CLASS__, 'handle_bulk_action_redirect' ), 10, 3 );

        // AJAX Handlers برای پردازش گروهی
        add_action( 'wp_ajax_smart_ai_seo_bulk_fetch_candidates', array( __CLASS__, 'ajax_fetch_candidates' ) );
        add_action( 'wp_ajax_smart_ai_seo_bulk_add_to_queue', array( __CLASS__, 'ajax_add_to_queue' ) );
        add_action( 'wp_ajax_smart_ai_seo_bulk_process_single', array( __CLASS__, 'ajax_process_single' ) );
        add_action( 'wp_ajax_smart_ai_seo_bulk_get_queue_status', array( __CLASS__, 'ajax_get_queue_status' ) );
        add_action( 'wp_ajax_smart_ai_seo_bulk_clear_queue', array( __CLASS__, 'ajax_clear_queue' ) );

        // اطمینان از زمان‌بندی کرون جاب در صورت فعال بودن آیتم‌های پس‌زمینه
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time() + 60, 'five_minutes', self::CRON_HOOK );
        }
    }

    /**
     * ثبت عملیات گروهی در دراپ‌داون جدول نوشته‌ها
     */
    public static function register_bulk_actions( $bulk_actions ) {
        $bulk_actions['smart_ai_seo_bulk_optimize'] = '🚀 بهینه‌سازی و رفرش گروهی سئو با هوش مصنوعی';
        return $bulk_actions;
    }

    /**
     * هدایت پس از انتخاب عملیات گروهی در جدول نوشته‌ها
     */
    public static function handle_bulk_action_redirect( $redirect_to, $action, $post_ids ) {
        if ( 'smart_ai_seo_bulk_optimize' !== $action ) {
            return $redirect_to;
        }

        if ( ! empty( $post_ids ) ) {
            $queue = get_option( self::OPTION_QUEUE, array() );
            $added_count = 0;

            foreach ( $post_ids as $pid ) {
                $pid = absint( $pid );
                if ( ! isset( $queue[ $pid ] ) || $queue[ $pid ]['status'] === 'completed' ) {
                    $queue[ $pid ] = array(
                        'post_id'      => $pid,
                        'title'        => get_the_title( $pid ),
                        'status'       => 'pending', // pending, processing, completed, error
                        'mode'         => 'smart_refresh', // auto_optimize, smart_refresh, update_title_meta
                        'scheduled_at' => current_time( 'mysql' ),
                        'error_msg'    => '',
                    );
                    $added_count++;
                }
            }

            update_option( self::OPTION_QUEUE, $queue );

            $redirect_to = add_query_arg(
                array(
                    'page'         => 'smart-ai-seo-generator',
                    'tab'          => 'bulk-optimizer',
                    'bulk_added'   => $added_count,
                ),
                admin_url( 'admin.php' )
            );
        }

        return $redirect_to;
    }

    /**
     * دریافت صف فعلی ذخیره شده در دیتابیس
     */
    public static function get_queue() {
        return get_option( self::OPTION_QUEUE, array() );
    }

    /**
     * دریافت لیست نوشته‌ها برای فیلتر و انتخاب هوشمند
     */
    public static function ajax_fetch_candidates() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $filter_type = isset( $_POST['filter_type'] ) ? sanitize_text_field( $_POST['filter_type'] ) : 'all';
        $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
        $limit       = isset( $_POST['limit'] ) ? min( 100, max( 5, absint( $_POST['limit'] ) ) ) : 30;

        $args = array(
            'post_type'      => 'post',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => $limit,
            'orderby'        => 'modified',
            'order'          => 'ASC',
        );

        if ( $category_id > 0 ) {
            $args['cat'] = $category_id;
        }

        if ( 'decayed' === $filter_type ) {
            // مقالاتی که بیش از ۱۲۰ روز از آخرین ویرایش آن‌ها گذشته است
            $args['date_query'] = array(
                array(
                    'column' => 'post_modified',
                    'before' => '120 days ago',
                ),
            );
        } elseif ( 'no_seo' === $filter_type ) {
            // مقالاتی که تا به حال با هوش مصنوعی تحلیل یا بهینه نشده‌اند
            $args['meta_query'] = array(
                array(
                    'key'     => '_smart_ai_seo_analysis',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        $query = new WP_Query( $args );
        $candidates = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $pid = get_the_ID();
                $post_modified = get_post_modified_time( 'U', false, $pid );
                $days_passed   = (int) ( ( current_time( 'timestamp' ) - $post_modified ) / DAY_IN_SECONDS );
                $analysis      = get_post_meta( $pid, '_smart_ai_seo_analysis', true );
                $score         = ! empty( $analysis['seo_score'] ) ? $analysis['seo_score'] : '---';
                $keyword       = get_post_meta( $pid, '_smart_ai_seo_keyword', true );

                $candidates[] = array(
                    'id'          => $pid,
                    'title'       => get_the_title( $pid ),
                    'keyword'     => $keyword ? $keyword : 'ثبت نشده',
                    'score'       => $score,
                    'days_passed' => $days_passed,
                    'modified'    => get_the_modified_date( 'Y/m/d' ),
                    'edit_url'    => get_edit_post_link( $pid ),
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success( array( 'candidates' => $candidates ) );
    }

    /**
     * افزودن دسته‌ای از مقالات به صف پردازش
     */
    public static function ajax_add_to_queue() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $post_ids = isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ? array_map( 'absint', $_POST['post_ids'] ) : array();
        $mode     = isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'smart_refresh';

        if ( empty( $post_ids ) ) {
            wp_send_json_error( array( 'message' => 'هیچ مقاله‌ای انتخاب نشده است.' ) );
        }

        $queue = get_option( self::OPTION_QUEUE, array() );
        $count = 0;

        foreach ( $post_ids as $pid ) {
            if ( ! $pid ) continue;
            $queue[ $pid ] = array(
                'post_id'      => $pid,
                'title'        => get_the_title( $pid ),
                'status'       => 'pending',
                'mode'         => $mode,
                'scheduled_at' => current_time( 'mysql' ),
                'error_msg'    => '',
            );
            $count++;
        }

        update_option( self::OPTION_QUEUE, $queue );
        wp_send_json_success( array(
            'message' => "تعداد {$count} مقاله با موفقیت به صف بهینه‌سازی گروهی اضافه شدند.",
            'queue'   => array_values( $queue ),
        ) );
    }

    /**
     * پاک‌سازی کامل صف پردازش
     */
    public static function ajax_clear_queue() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        update_option( self::OPTION_QUEUE, array() );
        wp_send_json_success( array( 'message' => 'صف پردازش گروهی به طور کامل پاک شد.' ) );
    }

    /**
     * دریافت وضعیت صف پردازش
     */
    public static function ajax_get_queue_status() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        $queue = get_option( self::OPTION_QUEUE, array() );
        wp_send_json_success( array( 'queue' => array_values( $queue ) ) );
    }

    /**
     * پردازش یک مقاله منفرد از صف (اجرای گام به گام زنده در مرورگر)
     */
    public static function ajax_process_single() {
        check_ajax_referer( 'smart_ai_seo_nonce', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'عدم دسترسی کافی.' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        $mode    = isset( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : 'smart_refresh';

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'شناسه نوشته معتبر نیست.' ) );
        }

        $result = self::optimize_post_action( $post_id, $mode );

        $queue = get_option( self::OPTION_QUEUE, array() );
        if ( isset( $queue[ $post_id ] ) ) {
            if ( is_wp_error( $result ) ) {
                $queue[ $post_id ]['status']    = 'error';
                $queue[ $post_id ]['error_msg'] = $result->get_error_message();
            } else {
                $queue[ $post_id ]['status']       = 'completed';
                $queue[ $post_id ]['completed_at'] = current_time( 'mysql' );
                $queue[ $post_id ]['new_score']    = $result['seo_score'] ?? '---';
            }
            update_option( self::OPTION_QUEUE, $queue );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message'   => 'مقاله با موفقیت بهینه‌سازی شد.',
            'post_id'   => $post_id,
            'seo_score' => $result['seo_score'] ?? '---',
            'title'     => $result['optimized_title'] ?? get_the_title( $post_id ),
        ) );
    }

    /**
     * تابع محوری اجرای هوشمند بهینه‌سازی روی یک نوشته
     */
    public static function optimize_post_action( $post_id, $mode = 'smart_refresh' ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'not_found', 'نوشته یافت نشد.' );
        }

        // استخراج کلمه کلیدی یا حدس آن از عنوان نوشته در صورت عدم ثبت
        $keyword = get_post_meta( $post_id, '_smart_ai_seo_keyword', true );
        if ( empty( $keyword ) ) {
            $keyword = wp_strip_all_tags( $post->post_title );
        }

        $rank = get_post_meta( $post_id, '_smart_ai_seo_rank', true );

        $custom_instruction = 'اقدام گروهی خودکار: لطفاً ساختار سئو را ارتقا بده، عنوان کلیک‌خور و جذاب تولید کن، بخش‌های کهنه را به‌روز کن، باکس نکات کلیدی اضافه کن و سوالات متداول کاربردی استخراج نما.';
        if ( 'smart_refresh' === $mode ) {
            $custom_instruction .= ' تمرکز ویژه بر الگوریتم تازگی (Freshness) گوگل: بازنویسی و ارتقای آمارهای کهنه به سال جاری، افزودن ۲ سرفصل جدید پرتقاضا، و کادر پاسخ مستقیم بهینه‌شده برای Google AI Overviews.';
        }

        // پشتیبان‌گیری خودکار از محتوا قبل از هرگونه تغییر
        $backup_key = '_smart_ai_seo_backup_content';
        update_post_meta( $post_id, $backup_key, array(
            'content' => $post->post_content,
            'title'   => $post->post_title,
            'time'    => current_time( 'mysql' ),
        ) );

        // ارسال درخواست به هوش مصنوعی
        $ai_response = Smart_AI_SEO_AI_Service::analyze_and_optimize(
            $keyword,
            $rank,
            $post->post_title,
            $post->post_content,
            $custom_instruction
        );

        if ( is_wp_error( $ai_response ) ) {
            return $ai_response;
        }

        // ذخیره نتایج تحلیل
        update_post_meta( $post_id, '_smart_ai_seo_keyword', $keyword );
        update_post_meta( $post_id, '_smart_ai_seo_analysis', $ai_response );

        // اعمال خودکار عنوان و محتوای بازنویسی شده به نوشته وردپرس
        $update_data = array(
            'ID' => $post_id,
        );

        if ( ! empty( $ai_response['optimized_title'] ) ) {
            $update_data['post_title'] = sanitize_text_field( $ai_response['optimized_title'] );
        }

        if ( ! empty( $ai_response['optimized_content'] ) && 'update_title_meta_only' !== $mode ) {
            $update_data['post_content'] = $ai_response['optimized_content'];
        }

        wp_update_post( $update_data );

        // همگام‌سازی متای توضیحات و کلمه کلیدی با Rank Math و Yoast SEO
        $meta_desc = ! empty( $ai_response['meta_description'] ) ? sanitize_textarea_field( $ai_response['meta_description'] ) : '';
        if ( ! empty( $meta_desc ) ) {
            // Rank Math
            update_post_meta( $post_id, 'rank_math_description', $meta_desc );
            update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
            // Yoast SEO
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_desc );
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyword );
        }

        // فعال‌سازی خودکار اسکیما FAQ در صورت تولید سوالات متداول
        if ( ! empty( $ai_response['schema_faq'] ) && is_array( $ai_response['schema_faq'] ) ) {
            update_post_meta( $post_id, '_smart_ai_seo_enable_faq_schema', 'yes' );
        }

        return $ai_response;
    }

    /**
     * پردازش خودکار توسط WP-Cron برای اجرای تدریجی در پس‌زمینه سرور
     */
    public static function process_cron_item() {
        $queue = get_option( self::OPTION_QUEUE, array() );
        if ( empty( $queue ) ) {
            return;
        }

        foreach ( $queue as $pid => $item ) {
            if ( 'pending' === $item['status'] ) {
                $queue[ $pid ]['status'] = 'processing';
                update_option( self::OPTION_QUEUE, $queue );

                $result = self::optimize_post_action( $pid, $item['mode'] ?? 'smart_refresh' );

                if ( is_wp_error( $result ) ) {
                    $queue[ $pid ]['status']    = 'error';
                    $queue[ $pid ]['error_msg'] = $result->get_error_message();
                } else {
                    $queue[ $pid ]['status']       = 'completed';
                    $queue[ $pid ]['completed_at'] = current_time( 'mysql' );
                    $queue[ $pid ]['new_score']    = $result['seo_score'] ?? '---';
                }

                update_option( self::OPTION_QUEUE, $queue );
                break; // در هر بار اجرای کرون فقط ۱ مقاله پردازش شود تا به سرور فشار نیاید و تایم‌اوت نشود
            }
        }
    }
}
