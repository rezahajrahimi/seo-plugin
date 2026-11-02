<?php
/**
 * مدیریت اتصال به مدل‌های مختلف هوش مصنوعی (OpenAI, Gemini, DeepSeek, Custom API)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Smart_AI_SEO_AI_Service {

    /**
     * تولید مقاله تکی سئو شده بر اساس کلمه کلیدی، لحن، هدف بازاریابی، لینک‌ها و مراجع
     */
    public static function generate_single_article( $keyword, $lsi_words = '', $tone = 'professional', $custom_instructions = '', $strategy = array() ) {
        $options  = Smart_AI_SEO_Settings::get_settings();
        $provider = ! empty( $options['provider'] ) ? $options['provider'] : 'openai';
        $global_custom_prompt = ! empty( $options['custom_system_prompt'] ) ? trim( $options['custom_system_prompt'] ) : '';

        $goal     = ! empty( $strategy['goal'] ) ? $strategy['goal'] : 'traffic';
        $audience = ! empty( $strategy['audience'] ) ? $strategy['audience'] : '';
        $cta_url  = ! empty( $strategy['cta_url'] ) ? $strategy['cta_url'] : '';
        $cta_text = ! empty( $strategy['cta_anchor'] ) ? $strategy['cta_anchor'] : 'مشاهده اطلاعات بیشتر';
        $sources  = ! empty( $strategy['source_reference'] ) ? $strategy['source_reference'] : '';
        $length   = ! empty( $strategy['length'] ) ? $strategy['length'] : 'standard';

        $system_prompt = "You are a World-Class Senior SEO Content Strategist and Direct-Response Copywriter who ranks #1 on Google while maximizing conversions.
Write an exceptionally comprehensive, high-ranking, and persuasive article in Persian (فارسی) optimized for the provided keyword.

STRICT SEO, LINKING & CONVERSION RULES:
1. Natural Keyword Density: Place focus keyword in H1/Title, within first 100 words, in at least two H2 headings, and in conclusion.
2. Structure: Start with a 'Key Takeaways / نکات کلیدی در یک نگاه' bulleted callout box at the very top. Use rich H2 and H3 tags, informative paragraphs, and at least one HTML comparison table.
3. ABSOLUTE LINK INTEGRITY (CRITICAL - DO NOT INVENT URLS):
   - NEVER hallucinate, guess, or invent non-existent URLs (like fake /blog/xyz or fake internal links).
   - ONLY include links explicitly provided to you in the prompt below. If no URL is provided, do NOT create arbitrary hyperlinks.";

        if ( ! empty( $cta_url ) ) {
            $system_prompt .= "\n   - MANDATORY CTA LINK: You MUST insert exactly this link <a href=\"{$cta_url}\" style=\"color:#2563eb; font-weight:bold; text-decoration:underline;\">{$cta_text}</a> inside an attractive callout box in the middle of the article AND again in the concluding section. Do not alter this URL.";
        }

        if ( ! empty( $sources ) ) {
            $system_prompt .= "\n4. MANDATORY SOURCE USAGE: You MUST deeply study and integrate the facts, data, statistics, insights, and concepts provided in the 'REFERENCE MATERIAL' section below into the article body, making this article far more comprehensive, structured, and informative than the original source.";
        }

        $system_prompt .= "\n5. Human Tone: Avoid cliché AI intros like 'در دنیای پرشتاب امروزی' or 'بدون شک'. Start directly with the user's burning problem or hook.
6. FAQ & Schema: Include 3-4 key FAQ questions with insightful answers at the end.
7. Tone of Voice: {$tone}.
8. Target Audience: " . ( $audience ? $audience : "عموم مخاطبان علاقه‌مند و خریداران بالقوه" ) . ".";

        if ( ! empty( $global_custom_prompt ) ) {
            $system_prompt .= "\n\nGLOBAL INSTRUCTIONS:\n" . $global_custom_prompt;
        }

        $system_prompt .= "\n\nCRITICAL: Return strictly a valid JSON object without markdown fences, with NO extra text:
    \"title\": \"عنوان سئو شده و کلیک‌خور شامل کلمه کلیدی\",
    \"slug\": \"clean-english-url-slug-2-to-5-words\",
    \"meta_description\": \"متای توضیحات جذاب بین ۱۳۰ تا ۱۵۵ کاراکتر با کلمه کلیدی\",
    \"image_prompt\": \"Detailed English prompt for generating a stunning, high-converting featured image (photorealistic or 3D render) relevant to this topic\",
    \"content\": \"متن کامل مقاله به فرمت HTML غنی شامل باکس نکات کلیدی، یک کادر پاسخ مستقیم بهینه‌شده برای هوش مصنوعی با کلاس <div class=\\\"smart-aeo-direct-answer\\\">پاسخ مستقیم و صریح به اصلی‌ترین پرسش کاربر در ۴۰ تا ۶۰ کلمه</div>، هدینگ‌های H2 و H3، پاراگراف‌ها، جدول مقایسه، لینک کال تو اکشن، و بخش جمع‌بندی\",
    \"title_variants\": [
        {\"type\": \"عددی و فکت‌محور\", \"title\": \"۵ راز مهم در...\"},
        {\"type\": \"کنجکاوی و ترغیب‌کننده\", \"title\": \"آیا می‌دانید چگونه...\"},
        {\"type\": \"جامع و تضمینی\", \"title\": \"راهنمای صفر تا صد...\"}
    ],
    \"schema_faq\": [
        {\"question\": \"سوال متداول؟\", \"answer\": \"پاسخ سئو شده و کاربردی\"}
    ],
    \"lsi_used\": [\"کلمات کلیدی مکمل استفاده شده\"]
}";

        $user_prompt = "Focus Keyword: {$keyword}\n";
        if ( ! empty( $lsi_words ) ) {
            $user_prompt .= "LSI & Secondary Keywords to include: {$lsi_words}\n";
        }
        if ( ! empty( $cta_url ) ) {
            $user_prompt .= "MANDATORY TARGET CTA URL TO INSERT: {$cta_url}\nCTA Anchor Text: {$cta_text}\n(You must include <a href=\"{$cta_url}\">{$cta_text}</a> in the content. DO NOT USE ANY OTHER INTERNAL LINKS)\n";
        }
        if ( ! empty( $sources ) ) {
            $user_prompt .= "\nMANDATORY REFERENCE MATERIAL & FACTS (You MUST use and synthesize this data in the article content):\n{$sources}\n";
        }
        if ( ! empty( $custom_instructions ) ) {
            $user_prompt .= "Specific Guidelines: {$custom_instructions}\n";
        }
        $user_prompt .= "Article Length Mode: {$length}\nTone: {$tone}\nLanguage: Persian (فارسی).";

        return self::dispatch_ai_request( $provider, $options, $system_prompt, $user_prompt );
    }

    /**
     * طراحی و تولید نقشه خوشه موضوعی (Topic Cluster & Pillar Plan) بر اساس هدف تجاری و منابع
     */
    public static function generate_cluster_plan( $core_topic, $count = 5, $strategy = array() ) {
        $options  = Smart_AI_SEO_Settings::get_settings();
        $provider = ! empty( $options['provider'] ) ? $options['provider'] : 'openai';

        $goal     = ! empty( $strategy['goal'] ) ? $strategy['goal'] : 'traffic';
        $audience = ! empty( $strategy['audience'] ) ? $strategy['audience'] : '';
        $cta_url  = ! empty( $strategy['cta_url'] ) ? $strategy['cta_url'] : '';
        $sources  = ! empty( $strategy['source_reference'] ) ? $strategy['source_reference'] : '';

        $system_prompt = "You are an Elite SEO Strategist specializing in Topical Authority and Topic Clusters (Pillar-Cluster Model).
Given a core topic, construct a complete Content Silo to dominate Google search results with clear commercial/traffic intent.

STRATEGY CONTEXT:
- Primary Cluster Goal: {$goal}
- Target Audience: " . ( $audience ? $audience : "عموم مخاطبان علاقه‌مند و خریداران بالقوه" ) . "
" . ( ! empty( $cta_url ) ? "- Target Conversion URL: {$cta_url}" : "" ) . "

CRITICAL: Return strictly a valid JSON object without markdown fences:
{
    \"pillar\": {
        \"title\": \"عنوان مقاله ستون (Pillar Page) جامع و ۳۶۰ درجه\",
        \"keyword\": \"کلمه کلیدی مقاله اصلی ستون\",
        \"slug\": \"pillar-english-slug\",
        \"meta_description\": \"متای توضیحات مقاله ستون\",
        \"outline\": [\"H2 سرفصل ۱\", \"H2 سرفصل ۲\", \"H2 سرفصل ۳\", \"H2 سرفصل ۴\"]
    },
    \"clusters\": [
        {
            \"title\": \"عنوان مقاله اقماری فرعی\",
            \"keyword\": \"کلمه کلیدی خاص این مقاله فرعی (Long-tail)\",
            \"slug\": \"cluster-english-slug\",
            \"meta_description\": \"متای توضیحات مقاله فرعی\",
            \"anchor_to_pillar\": \"انکر تکست پیشنهادی در این مقاله جهت پیوند به مقاله ستون\",
            \"search_intent\": \"Informational|Commercial|Transactional\"
        }
    ]
}
Generate exactly {$count} cluster sub-articles. All texts in Persian (فارسی) except slugs.";

        $user_prompt = "Core Topic: {$core_topic}\nNumber of Sub-topics: {$count}";
        if ( ! empty( $sources ) ) {
            $user_prompt .= "\nReference Context & Documentation:\n" . $sources;
        }

        return self::dispatch_ai_request( $provider, $options, $system_prompt, $user_prompt );
    }

    /**
     * متد مشترک ارسال درخواست به ارائه‌دهنده فعال
     */
    private static function dispatch_ai_request( $provider, $options, $system_prompt, $user_prompt ) {
        switch ( $provider ) {
            case 'openai':
                return self::call_openai_compatible(
                    'https://api.openai.com/v1/chat/completions',
                    $options['openai_api_key'],
                    ! empty( $options['openai_model'] ) ? $options['openai_model'] : 'gpt-4o-mini',
                    $system_prompt,
                    $user_prompt
                );

            case 'gemini':
                return self::call_gemini(
                    $options['gemini_api_key'],
                    ! empty( $options['gemini_model'] ) ? $options['gemini_model'] : 'gemini-1.5-flash',
                    $system_prompt,
                    $user_prompt
                );

            case 'deepseek':
                return self::call_openai_compatible(
                    'https://api.deepseek.com/chat/completions',
                    $options['deepseek_api_key'],
                    ! empty( $options['deepseek_model'] ) ? $options['deepseek_model'] : 'deepseek-chat',
                    $system_prompt,
                    $user_prompt
                );

            case 'custom':
                $endpoint = ! empty( $options['custom_endpoint'] ) ? trim( $options['custom_endpoint'] ) : '';
                $api_key  = ! empty( $options['custom_api_key'] ) ? trim( $options['custom_api_key'] ) : '';
                $model    = ! empty( $options['custom_model'] ) ? trim( $options['custom_model'] ) : '';
                if ( empty( $endpoint ) ) {
                    return new WP_Error( 'missing_endpoint', 'آدرس Rest API سفارشی (Endpoint) در تنظیمات مشخص نشده است.' );
                }
                return self::call_openai_compatible(
                    $endpoint,
                    $api_key,
                    $model,
                    $system_prompt,
                    $user_prompt,
                    $options['custom_extra_headers'] ?? ''
                );

            default:
                return new WP_Error( 'invalid_provider', 'ارائه‌دهنده نامعتبر است.' );
        }
    }

    /**
     * ارسال محتوا و تحلیل سئو با مدل تنظیم شده
     */
    public static function analyze_and_optimize( $keyword, $current_rank, $title, $content, $custom_instruction = '' ) {
        $options = Smart_AI_SEO_Settings::get_settings();
        $provider = ! empty( $options['provider'] ) ? $options['provider'] : 'openai';

        $global_custom_prompt = ! empty( $options['custom_system_prompt'] ) ? trim( $options['custom_system_prompt'] ) : '';

        $system_prompt = self::build_system_prompt( $global_custom_prompt );
        $user_prompt   = self::build_user_prompt( $keyword, $current_rank, $title, $content, $custom_instruction );

        return self::dispatch_ai_request( $provider, $options, $system_prompt, $user_prompt );
    }

    /**
     * سیستم پرامپت اختصاصی جهت دریافت پاسخ ساختاریافته (JSON)
     */
    private static function build_system_prompt( $global_custom_prompt = '' ) {
        $prompt = "You are an elite Senior SEO Specialist, Answer Engine Optimization (AEO) expert, and Content Strategist.
Analyze the given article specifically targeting the provided focus keyword and current rank/position.
Your goal is to get this page to Rank #1 on Google and win the Google AI Overview snapshot.
Identify SEO issues, missing search intent, readability issues, keyword density/placement gaps, heading hierarchy problems, and provide specific, high-impact improvements.";

        if ( ! empty( $global_custom_prompt ) ) {
            $prompt .= "\n\nADDITIONAL USER-DEFINED GUIDELINES & CONSTRAINTS:\n" . $global_custom_prompt;
        }

        $prompt .= "\n\nCRITICAL REQUIREMENT:
You MUST output strictly a valid JSON object without markdown fences, without ```json wrapper, and with NO preamble/postamble.
The JSON must follow this exact structure:
{
    \"seo_score\": 75,
    \"summary\": \"خلاصه سریع از وضعیت مقاله و پتانسیل رشد آن\",
    \"issues\": [
        {\"type\": \"error|warning|info\", \"title\": \"عنوان مشکل\", \"description\": \"توضیح کامل علت و راه رفع\"}
    ],
    \"recommendations\": [
        {\"action\": \"اقدام پیشنهادی\", \"priority\": \"high|medium|low\", \"impact\": \"تاثیر روی سئو\"}
    ],
    \"optimized_title\": \"عنوان سئو شده پیشنهادی اصلی\",
    \"title_variants\": [
        {\"type\": \"عددی و فکت‌محور\", \"title\": \"۵ راز مهم در...\"},
        {\"type\": \"کنجکاوی و ترغیب‌کننده\", \"title\": \"آیا می‌دانید چگونه...\"},
        {\"type\": \"جامع و تضمینی\", \"title\": \"راهنمای صفر تا صد...\"}
    ],
    \"meta_description\": \"متای توضیحات پیشنهادی بهینه‌شده با کلمه کلیدی\",
    \"lsi_keywords\": [
        {\"keyword\": \"عبارت مکمل یا هم‌معنی LSI\", \"found_in_content\": true, \"suggestion\": \"توضیح کوتاه نحوه به‌کارگیری\"}
    ],
    \"schema_faq\": [
        {\"question\": \"سوال متداول کاربران؟\", \"answer\": \"پاسخ جامع و مفید و سئو شده برای اسکیما گوگل\"}
    ],
    \"internal_linking\": [
        {\"anchor_text\": \"انکرتکست پیشنهادی در متن\", \"target_topic\": \"موضوع مقاله مرتبط در سایت جهت پیوند دادن\"}
    ],
    \"optimized_content\": \"متن کامل ویرایش و ارتقا یافته مقاله به زبان اصلی (HTML format with headings, paragraphs, lists) شامل تمام تغییرات و افزودن بخش‌های غنی‌ساز بدون حذف بخش‌های مفید فعلی\"
}
All explanatory text inside JSON must be in Persian (فارسی). The optimized_content should preserve the original article language and tone.";

        return $prompt;
    }

    /**
     * آماده‌سازی پرامپت کاربر
     */
    private static function build_user_prompt( $keyword, $current_rank, $title, $content, $custom_instruction = '' ) {
        $clean_content = wp_strip_all_tags( $content );
        // محدودسازی برای طول‌های بسیار زیاد در صورت نیاز
        if ( mb_strlen( $clean_content ) > 30000 ) {
            $content = mb_substr( $content, 0, 30000 );
        }

        $prompt = "Focus Keyword: {$keyword}\n"
             . "Current Position / Rank: " . ( $current_rank ? $current_rank : 'مشخص نشده' ) . "\n"
             . "Current Title: {$title}\n";

        if ( ! empty( $custom_instruction ) ) {
            $prompt .= "SPECIFIC USER PROMPT / INSTRUCTIONS FOR THIS ARTICLE:\n" . trim( $custom_instruction ) . "\n";
        }

        $prompt .= "\nCurrent Article HTML Content:\n" . $content;

        return $prompt;
    }

    /**
     * ارسال درخواست به اندپوینت‌های سازگار با استاندارد OpenAI (مانند OpenAI, DeepSeek, vLLM, Ollama, LM Studio, Groq, OpenRouter)
     */
    private static function call_openai_compatible( $endpoint, $api_key, $model, $system_prompt, $user_prompt, $extra_headers_str = '' ) {
        $headers = array(
            'Content-Type' => 'application/json',
        );

        if ( ! empty( $api_key ) ) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        // پردازش هدرهای سفارشی در صورت وجود
        if ( ! empty( $extra_headers_str ) ) {
            $lines = explode( "\n", str_replace( "\r", '', $extra_headers_str ) );
            foreach ( $lines as $line ) {
                if ( strpos( $line, ':' ) !== false ) {
                    list( $h_key, $h_val ) = explode( ':', $line, 2 );
                    $headers[ trim( $h_key ) ] = trim( $h_val );
                }
            }
        }

        $body = array(
            'messages' => array(
                array( 'role' => 'system', 'content' => $system_prompt ),
                array( 'role' => 'user', 'content' => $user_prompt ),
            ),
            'temperature' => 0.3,
            'stream'      => false,
            'response_format' => array( 'type' => 'json_object' ),
        );

        if ( ! empty( $model ) ) {
            $body['model'] = $model;
        }

        $response = wp_remote_post( $endpoint, array(
            'headers'     => $headers,
            'body'        => wp_json_encode( $body ),
            'timeout'     => 120,
            'data_format' => 'body',
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_res = wp_remote_retrieve_body( $response );

        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'api_error', "خطای API (کد {$code}): " . $body_res );
        }

        // بررسی اینکه آیا پاسخ به صورت استاندارد JSON است یا Stream (SSE)
        $raw_reply = '';
        $json = json_decode( $body_res, true );

        if ( isset( $json['choices'][0]['message']['content'] ) ) {
            $raw_reply = trim( $json['choices'][0]['message']['content'] );
        } elseif ( strpos( $body_res, 'data:' ) !== false ) {
            // پشتیبانی از خروجی‌های استریم (Server-Sent Events) در برخی روترها
            $lines = explode( "\n", $body_res );
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( strpos( $line, 'data:' ) === 0 ) {
                    $json_part = trim( substr( $line, 5 ) );
                    if ( $json_part === '[DONE]' ) {
                        continue;
                    }
                    $chunk = json_decode( $json_part, true );
                    if ( isset( $chunk['choices'][0]['delta']['content'] ) ) {
                        $raw_reply .= $chunk['choices'][0]['delta']['content'];
                    }
                }
            }
        }

        if ( empty( $raw_reply ) ) {
            return new WP_Error( 'empty_response', 'پاسخی از مدل هوش مصنوعی دریافت نشد. بدنه پاسخ: ' . mb_substr( $body_res, 0, 300 ) );
        }

        return self::extract_json_response( $raw_reply );
    }

    /**
     * ارتباط مستقیم با Google Gemini API
     */
    private static function call_gemini( $api_key, $model, $system_prompt, $user_prompt ) {
        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_key', 'کلید API سرویس Google Gemini ثبت نشده است.' );
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $body = array(
            'contents' => array(
                array(
                    'role'  => 'user',
                    'parts' => array(
                        array( 'text' => $system_prompt . "\n\n" . $user_prompt )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature'       => 0.4,
                'responseMimeType'  => 'application/json',
            )
        );

        $response = wp_remote_post( $endpoint, array(
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'body'        => wp_json_encode( $body ),
            'timeout'     => 90,
            'data_format' => 'body',
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_res = wp_remote_retrieve_body( $response );

        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'gemini_error', "خطای Gemini (کد {$code}): " . $body_res );
        }

        $json = json_decode( $body_res, true );
        if ( empty( $json['candidates'][0]['content']['parts'][0]['text'] ) ) {
            return new WP_Error( 'gemini_empty', 'پاسخ معتبری از Gemini دریافت نشد.' );
        }

        $raw_reply = trim( $json['candidates'][0]['content']['parts'][0]['text'] );
        return self::extract_json_response( $raw_reply );
    }

    /**
     * پالایش و تبدیل خروجی متنی مدل به آرایه JSON استاندارد
     */
    private static function extract_json_response( $text ) {
        $text = trim( $text );

        // حذف مارک‌داون fences احتمالی
        if ( preg_match( '/```(?:json)?\s*([\s\S]*?)\s*```/u', $text, $matches ) ) {
            $text = trim( $matches[1] );
        }

        // استخراج محدوده اولین { تا آخرین } در صورتی که متن توضیحی قبل یا بعد از آن وجود داشته باشد
        $first_brace = mb_strpos( $text, '{' );
        $last_brace  = mb_strrpos( $text, '}' );
        if ( $first_brace !== false && $last_brace !== false && $last_brace > $first_brace ) {
            $text = mb_substr( $text, $first_brace, ( $last_brace - $first_brace ) + 1 );
        }

        // تلاش اول برای دیکود استاندارد
        $decoded = json_decode( $text, true );
        if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
            return $decoded;
        }

        // در صورتی که کاراکترهای کنترلی کنترل‌نشده (مانند newline یا tab داخل استرینگ‌های HTML) مانع شده باشند
        $clean_text = preg_replace( '/[\x00-\x1F\x7F]/u', ' ', $text );
        $decoded_clean = json_decode( $clean_text, true );
        if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_clean ) ) {
            return $decoded_clean;
        }

        // تلاش منعطف با پاکسازی دستی کاراکترهای سطرشکنی داخل استرینگ‌ها
        $escaped = preg_replace_callback( '/"(.*?)"/s', function( $m ) {
            return '"' . str_replace( array( "\r", "\n", "\t" ), array( '', '\n', '\t' ), $m[1] ) . '"';
        }, $text );

        $decoded_escaped = json_decode( $escaped, true );
        if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_escaped ) ) {
            return $decoded_escaped;
        }

        return new WP_Error( 'json_parse_error', 'امکان تبدیل پاسخ هوش مصنوعی به داده ساختاریافته وجود ندارد: ' . mb_substr( $text, 0, 400 ) );
    }
}
