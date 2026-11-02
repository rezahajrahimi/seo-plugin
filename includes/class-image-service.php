<?php
/**
 * مدیریت تولید، دریافت و آپلود خودکار تصاویر در کتابخانه رسانه وردپرس و تنظیم به عنوان تصویر شاخص
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Image_Service {

    /**
     * ایجاد و تنظیم تصویر شاخص برای یک نوشته با متدهای مختلف
     *
     * @param int    $post_id شناسه نوشته
     * @param string $keyword کلمه کلیدی یا موضوع
     * @param string $source منبع تصویر ('ai_pollinations', 'unsplash', 'prompt_only')
     * @param string $image_prompt پرامپت اختصاصی تولید تصویر
     * @return int|WP_Error شناسه پیوست (Attachment ID) در صورت موفقیت
     */
    public static function attach_featured_image( $post_id, $keyword, $source = 'ai_pollinations', $image_prompt = '' ) {
        if ( ! $post_id ) {
            return new WP_Error( 'invalid_post', 'شناسه نوشته نامعتبر است.' );
        }

        // اگر فقط پرامپت خواسته شده باشد
        if ( 'prompt_only' === $source ) {
            if ( empty( $image_prompt ) ) {
                $image_prompt = 'Professional editorial modern 3D illustration for: ' . $keyword . ', clean studio lighting, high resolution, minimalist tech aesthetic, 4k';
            }
            update_post_meta( $post_id, '_smart_ai_seo_image_prompt', $image_prompt );
            return true;
        }

        $image_url = '';

        if ( 'ai_pollinations' === $source ) {
            // ساخت پرامپت انگلیسی تمیز
            $clean_prompt = ! empty( $image_prompt ) ? $image_prompt : ( 'modern editorial 3D concept illustration of ' . $keyword . ', minimalist technology aesthetic, soft studio lighting, ultra-detailed, 8k wallpaper' );
            $encoded_prompt = rawurlencode( $clean_prompt );
            $image_url = "https://image.pollinations.ai/prompt/{$encoded_prompt}?width=1200&height=675&nologo=true";
        } elseif ( 'unsplash' === $source ) {
            // دریافت تصویر باکیفیت مرتبط از منبع رایگان Unsplash بر اساس کلمه کلیدی
            $clean_kw = rawurlencode( $keyword );
            $image_url = "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80"; // تصویر پایه یا جستجو
            // استفاده از سرویس Unsplash Source
            $image_url = "https://source.unsplash.com/1200x675/?" . $clean_kw;
        }

        if ( empty( $image_url ) ) {
            return new WP_Error( 'no_image_url', 'آدرس تصویر ایجاد نشد.' );
        }

        // دانلود تصویر و الصاق به رسانه‌های وردپرس
        return self::download_and_attach( $image_url, $post_id, $keyword );
    }

    /**
     * دانلود فایل تصویر از URL و ثبت در کتابخانه رسانه‌های وردپرس (wp_insert_attachment)
     */
    private static function download_and_attach( $url, $post_id, $alt_text ) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // دانلود فایل به پوشه موقت
        $tmp = download_url( $url, 45 );
        if ( is_wp_error( $tmp ) ) {
            return $tmp;
        }

        $file_array = array(
            'name'     => sanitize_title( $alt_text ) . '-' . wp_generate_password( 4, false ) . '.jpg',
            'tmp_name' => $tmp,
        );

        // اعتبارسنجی نوع فایل
        $file_type = wp_check_filetype( $file_array['name'], null );
        if ( empty( $file_type['type'] ) ) {
            $file_array['name'] .= '.jpg';
        }

        // افزودن تصویر به رسانه‌ها
        $attach_id = media_handle_sideload( $file_array, $post_id, $alt_text );

        // حذف فایل موقت در صورت بروز خطا
        if ( is_wp_error( $attach_id ) ) {
            @unlink( $file_array['tmp_name'] );
            return $attach_id;
        }

        // تنظیم Alt Text سئو شده
        update_post_meta( $attach_id, '_wp_attachment_image_alt', sanitize_text_field( $alt_text ) );

        // تنظیم به عنوان تصویر شاخص نوشته (Featured Image)
        set_post_thumbnail( $post_id, $attach_id );

        return $attach_id;
    }
}
