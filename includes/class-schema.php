<?php
/**
 * مدیریت اسکیما JSON-LD (FAQ & Article) و تزریق در تگ <head>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_Schema {

    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'output_schema_in_head' ) );
    }

    /**
     * چاپ اسکیما در هدر صفحه اختصاصی نوشته در صورت فعال بودن
     */
    public static function output_schema_in_head() {
        if ( ! is_singular( array( 'post', 'page' ) ) ) {
            return;
        }

        global $post;
        if ( ! $post ) {
            return;
        }

        $enable_schema = get_post_meta( $post->ID, '_smart_ai_seo_enable_faq_schema', true );
        if ( $enable_schema !== 'yes' ) {
            return;
        }

        $analysis = get_post_meta( $post->ID, '_smart_ai_seo_analysis', true );
        if ( empty( $analysis['schema_faq'] ) || ! is_array( $analysis['schema_faq'] ) ) {
            return;
        }

        $faq_schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array(),
        );

        foreach ( $analysis['schema_faq'] as $item ) {
            if ( ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) {
                $faq_schema['mainEntity'][] = array(
                    '@type'          => 'Question',
                    'name'           => wp_strip_all_tags( $item['question'] ),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => wp_kses_post( $item['answer'] ),
                    ),
                );
            }
        }

        if ( ! empty( $faq_schema['mainEntity'] ) ) {
            echo "\n<!-- Smart AI SEO FAQ Schema -->\n";
            echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
        }
    }
}
