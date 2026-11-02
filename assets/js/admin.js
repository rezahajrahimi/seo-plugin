/**
 * تعاملات جاوااسکریپت برای تحلیل، تب‌ها و اعمال تغییرات روی ویرایشگرهای گوتنبرگ و کلاسیک
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // مدیریت تب‌ها
        $(document).on('click', '.smart-tab-btn', function(e) {
            e.preventDefault();
            var target = $(this).data('tab');
            if (!target) return;

            var $container = $(this).closest('.smart-tabs-nav').parent();
            if ($container.length) {
                $container.find('.smart-tab-btn').removeClass('active');
                $(this).addClass('active');

                $container.find('.smart-tab-content').removeClass('active');
                $container.find('#tab-content-' + target).addClass('active');
            } else {
                $('.smart-tab-btn').removeClass('active');
                $(this).addClass('active');

                $('.smart-tab-content').removeClass('active');
                $('#tab-content-' + target).addClass('active');
            }
        });

        // دریافت محتوای فعلی نوشته (پشتیبانی از گوتنبرگ و ادیتور کلاسیک)
        function getCurrentPostContent() {
            // ویرایشگر بلاک / گوتنبرگ
            if (window.wp && wp.data && wp.data.select && wp.data.select('core/editor')) {
                return wp.data.select('core/editor').getEditedPostContent();
            }
            // ویرایشگر کلاسیک (TinyMCE)
            if (window.tinymce && tinymce.get('content') && !tinymce.get('content').isHidden()) {
                return tinymce.get('content').getContent();
            }
            // تکست‌اریا ساده وردپرس
            var $contentArea = $('#content');
            if ($contentArea.length) {
                return $contentArea.val();
            }
            return '';
        }

        // دریافت عنوان فعلی نوشته
        function getCurrentPostTitle() {
            if (window.wp && wp.data && wp.data.select && wp.data.select('core/editor')) {
                return wp.data.select('core/editor').getEditedPostAttribute('title') || '';
            }
            return $('#title').val() || '';
        }

        // اعمال متن جدید در ویرایشگر (سازگار با هر دو نوع ادیتور)
        function applyContentToEditor(newContent) {
            // ویرایشگر بلاک گوتنبرگ
            if (window.wp && wp.data && wp.data.dispatch && wp.data.select && wp.data.select('core/editor')) {
                if (wp.blocks && wp.blocks.rawHandler) {
                    var parsedBlocks = wp.blocks.rawHandler({ HTML: newContent });
                    wp.data.dispatch('core/block-editor').resetBlocks(parsedBlocks);
                } else {
                    wp.data.dispatch('core/editor').editPost({ content: newContent });
                }
                return true;
            }

            // ادیتور کلاسیک TinyMCE
            if (window.tinymce && tinymce.get('content') && !tinymce.get('content').isHidden()) {
                tinymce.get('content').setContent(newContent);
                return true;
            }

            // تکست‌اریا پیش‌فرض
            var $contentArea = $('#content');
            if ($contentArea.length) {
                $contentArea.val(newContent);
                return true;
            }

            return false;
        }

        // درج یک کلمه یا عبارت LSI در انتهای ویرایشگر
        function insertKeywordToEditor(keyword) {
            if (!keyword) return;

            // ویرایشگر بلاک گوتنبرگ: ایجاد یک پاراگراف جدید
            if (window.wp && wp.data && wp.data.dispatch && wp.blocks && wp.blocks.createBlock) {
                var newBlock = wp.blocks.createBlock('core/paragraph', {
                    content: '<strong>نکته کلیدی:</strong> ' + keyword
                });
                wp.data.dispatch('core/block-editor').insertBlocks(newBlock);
                return true;
            }

            // ویرایشگر کلاسیک TinyMCE
            if (window.tinymce && tinymce.get('content') && !tinymce.get('content').isHidden()) {
                tinymce.get('content').insertContent('<p><strong>نکته کلیدی:</strong> ' + keyword + '</p>');
                return true;
            }

            // تکست اریا پیش فرض
            var $contentArea = $('#content');
            if ($contentArea.length) {
                var cur = $contentArea.val();
                $contentArea.val(cur + '\n<p><strong>نکته کلیدی:</strong> ' + keyword + '</p>');
                return true;
            }

            return false;
        }

        // رویداد کلیک برای درج کلمه کلیدی LSI در متن
        $(document).on('click', '.smart-insert-lsi-btn', function(e) {
            e.preventDefault();
            var $chip = $(this).closest('.smart-lsi-chip');
            var kw = $chip.attr('data-keyword') || $chip.find('strong').text();

            if (!kw) return;

            var inserted = insertKeywordToEditor(kw);
            if (inserted) {
                $chip.removeClass('is-missing').addClass('is-found');
                $chip.find('.lsi-status').text('✓ درج شد');
                $(this).text('✓ اضافه شد').prop('disabled', true);
            } else {
                alert('امکان درج خودکار در ویرایشگر فراهم نشد. می‌توانید متن را دستی کپی نمایید.');
            }
        });

        // تبدیل انکرتکست به لینک در متن نوشته
        function linkAnchorInContent(anchor, url) {
            var content = getCurrentPostContent();
            if (!content || !anchor || !url) return false;

            // جستجوی انکرتکست در صورتی که قبلاً لینک نشده باشد
            var regex = new RegExp('(?!(?:[^<]+>|[^>]+<\\/a>))(' + anchor.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'i');
            if (!regex.test(content)) {
                // اگر انکرتکست دقیق پیدا نشد
                return false;
            }

            var replacement = '<a href="' + url + '" title="' + anchor + '">$1</a>';
            var newContent = content.replace(regex, replacement);
            return applyContentToEditor(newContent);
        }

        // رویداد دکمه کپی انکر تکست
        $(document).on('click', '.smart-copy-anchor-btn', function(e) {
            e.preventDefault();
            var $li = $(this).closest('.linking-item');
            var anchor = $li.attr('data-anchor') || '';
            if (navigator.clipboard) {
                navigator.clipboard.writeText(anchor).then(function() {
                    alert('انکر تکست کپی شد: ' + anchor);
                });
            } else {
                prompt('کپی دستی انکرتکست:', anchor);
            }
        });

        // رویداد دکمه «لینک‌دار کردن در متن»
        $(document).on('click', '.smart-auto-link-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $li = $btn.closest('.linking-item');
            var anchor = $li.attr('data-anchor') || '';
            var topic = $li.attr('data-topic') || '';
            var $resultsDiv = $li.find('.linking-quick-search-result');

            if (!anchor) return;

            // ابتدا جستجوی خودکار برای یافتن مقاله مرتبط با این موضوع
            $btn.prop('disabled', true).text('در حال جستجوی مقاله...');
            $resultsDiv.html('<span class="spinner is-active" style="float:none; margin:0 5px;"></span> در حال یافتن صفحات سایت مرتبط با: ' + $('<div>').text(topic).html());

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_search_posts',
                    nonce: smartAiSeoData.nonce,
                    query: topic || anchor
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('🔗 لینک‌دار کردن در متن');
                    if (res.success && res.data && res.data.results && res.data.results.length) {
                        var html = '<div class="links-popup"><strong>مقاله مورد نظر را برای لینک‌دهی انتخاب کنید:</strong><ul>';
                        res.data.results.forEach(function(item) {
                            html += '<li><a href="#" class="smart-choose-link-target" data-url="' + item.url + '" data-title="' + $('<div>').text(item.title).html() + '">' + $('<div>').text(item.title).html() + '</a> <small style="color:#64748b;">(' + item.url + ')</small></li>';
                        });
                        html += '</ul><div style="margin-top:6px;"><input type="text" placeholder="یا آدرس دلخواه وارد کنید..." class="custom-url-input regular-text" style="width:70%;"> <button type="button" class="button button-small smart-apply-custom-url">اعمال لینک</button></div></div>';
                        $resultsDiv.html(html);
                    } else {
                        // در صورت نیافتن، امکان وارد کردن URL دستی
                        var manualHtml = '<div class="links-popup"><span style="color:#b45309;">مقاله آماده‌ای با این عنوان پیدا نشد.</span> آدرس لینک مقصد را وارد نمایید: <input type="url" placeholder="https://yoursite.com/slug" class="custom-url-input regular-text" style="width:60%;"> <button type="button" class="button button-small smart-apply-custom-url">اعمال لینک</button></div>';
                        $resultsDiv.html(manualHtml);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('🔗 لینک‌دار کردن در متن');
                    $resultsDiv.html('<span style="color:#ef4444;">خطا در جستجو</span>');
                }
            });
        });

        // انتخاب لینک از لیست پیشنهادی
        $(document).on('click', '.smart-choose-link-target', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var $li = $(this).closest('.linking-item');
            var anchor = $li.attr('data-anchor') || '';

            var success = linkAnchorInContent(anchor, url);
            if (success) {
                alert('پیوند با موفقیت روی عبارت «' + anchor + '» در متن اعمال شد!');
                $li.find('.linking-quick-search-result').html('<span style="color:#15803d; font-weight:bold;">✅ لینک «' + anchor + '» در متن ثبت شد.</span>');
            } else {
                alert('عبارت «' + anchor + '» در متن فعلی نوشته پیدا نشد یا قبلاً لینک شده است. در صورت تمایل متن را دستی لینک کنید.');
            }
        });

        // اعمال لینک با URL دستی
        $(document).on('click', '.smart-apply-custom-url', function(e) {
            e.preventDefault();
            var $li = $(this).closest('.linking-item');
            var url = $li.find('.custom-url-input').val().trim();
            var anchor = $li.attr('data-anchor') || '';

            if (!url) {
                alert('لطفاً آدرس لینک را وارد کنید.');
                return;
            }

            var success = linkAnchorInContent(anchor, url);
            if (success) {
                alert('پیوند با موفقیت روی عبارت «' + anchor + '» در متن اعمال شد!');
                $li.find('.linking-quick-search-result').html('<span style="color:#15803d; font-weight:bold;">✅ لینک «' + anchor + '» در متن ثبت شد.</span>');
            } else {
                alert('عبارت «' + anchor + '» در متن فعلی نوشته پیدا نشد یا قبلاً لینک شده است.');
            }
        });

        // اعمال عنوان جدید
        function applyTitleToEditor(newTitle) {
            if (window.wp && wp.data && wp.data.dispatch && wp.data.select && wp.data.select('core/editor')) {
                wp.data.dispatch('core/editor').editPost({ title: newTitle });
                return;
            }
            $('#title').val(newTitle).trigger('change');
        }

        // کلیک روی پرامپت آماده برای رفرش محتوای کهنه
        $('#smart-quick-decay-refresh-btn').on('click', function(e) {
            e.preventDefault();
            var decayPrompt = 'این محتوا قدیمی است؛ لطفاً آمار، تاریخ‌ها و اطلاعات را به جدیدترین سال جاری به‌روز کن، ۲ سرفصل و زاویه دید نوین و جذاب مطابق ترندهای جدید به متن اضافه کن، بخش سوالات متداول و نکات کلیدی را بازطراحی کن تا مقاله تازگی (Freshness) کامل پیدا کند.';
            var currentVal = $('#smart_custom_prompt').val().trim();
            if (currentVal) {
                $('#smart_custom_prompt').val(currentVal + '\n' + decayPrompt);
            } else {
                $('#smart_custom_prompt').val(decayPrompt);
            }
            $('#smart_custom_prompt').focus();
            alert('دستورالعمل رفرش محتوا در کادر پرامپت سفارشی قرار گرفت. اکنون روی دکمه «تحلیل و بازنویسی با هوش مصنوعی» کلیک کنید.');
        });

        // کلیک دکمه شروع تحلیل
        $('#smart-ai-start-analysis').on('click', function(e) {
            e.preventDefault();

            var keyword = $('#smart_target_keyword').val().trim();
            var rank = $('#smart_target_rank').val().trim();
            var customPrompt = $('#smart_custom_prompt').val() ? $('#smart_custom_prompt').val().trim() : '';
            var title = getCurrentPostTitle();
            var content = getCurrentPostContent();
            var postId = $('#post_ID').val() || 0;

            if (!keyword) {
                alert(smartAiSeoData.i18n.noKeyword);
                $('#smart_target_keyword').focus();
                return;
            }

            var $btn = $(this);
            var $spinner = $('#smart-ai-spinner');

            $btn.prop('disabled', true).text(smartAiSeoData.i18n.analyzing);
            $spinner.addClass('is-active');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_analyze',
                    nonce: smartAiSeoData.nonce,
                    post_id: postId,
                    keyword: keyword,
                    rank: rank,
                    custom_prompt: customPrompt,
                    title: title,
                    content: content
                },
                success: function(response) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-search" style="vertical-align: middle;"></span> تحلیل مجدد');
                    $spinner.removeClass('is-active');

                    if (response.success && response.data && response.data.data) {
                        renderResults(response.data.data);
                    } else {
                        var msg = response.data && response.data.message ? response.data.message : smartAiSeoData.i18n.errorOccurred;
                        alert('خطا: ' + msg);
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-search" style="vertical-align: middle;"></span> تحلیل مجدد');
                    $spinner.removeClass('is-active');
                    alert('خطای ارتباط با سرور: ' + error);
                }
            });
        });

        // پر کردن اطلاعات در اینترفیس بعد از دریافت نتایج
        function renderResults(data) {
            $('#smart-ai-results-container').removeClass('smart-hidden');

            if (data.seo_score !== undefined) {
                $('#smart-score-value').text(data.seo_score);
            }

            if (data.summary) {
                $('#smart-summary-text').text(data.summary);
            }

            // لیست مشکلات
            var $issuesList = $('#smart-issues-list').empty();
            if (data.issues && data.issues.length) {
                $('#issues-count').text(data.issues.length);
                data.issues.forEach(function(item) {
                    var typeClass = 'issue-' + (item.type || 'warning');
                    var li = $('<li></li>').addClass('issue-item ' + typeClass);
                    li.append($('<strong></strong>').text((item.title || '') + ': '));
                    li.append($('<span></span>').text(item.description || ''));
                    $issuesList.append(li);
                });
            } else {
                $('#issues-count').text(0);
                $issuesList.append('<li>مشکل خاصی شناسایی نشد.</li>');
            }

            // لیست توصیه‌ها
            var $recommsList = $('#smart-recomms-list').empty();
            if (data.recommendations && data.recommendations.length) {
                $('#recomms-count').text(data.recommendations.length);
                data.recommendations.forEach(function(rec) {
                    var pri = (rec.priority || 'medium').toLowerCase();
                    var li = $('<li></li>').addClass('recomm-item priority-' + pri);
                    li.append($('<span class="badge"></span>').text(pri.toUpperCase()));
                    li.append($('<strong></strong>').text(rec.action || ''));
                    if (rec.impact) {
                        li.append($('<p class="impact"></p>').text(rec.impact));
                    }
                    $recommsList.append(li);
                });
            } else {
                $('#recomms-count').text(0);
            }

            // لیست کلمات LSI
            var $lsiContainer = $('#smart-lsi-container').empty();
            if (data.lsi_keywords && data.lsi_keywords.length) {
                data.lsi_keywords.forEach(function(lsi) {
                    var chip = $('<div class="smart-lsi-chip"></div>');
                    chip.attr('data-keyword', lsi.keyword || '');
                    chip.addClass(lsi.found_in_content ? 'is-found' : 'is-missing');
                    
                    var topRow = $('<div class="lsi-top-row"></div>');
                    topRow.append('<span class="lsi-status">' + (lsi.found_in_content ? '✓ در متن موجود است' : '+ نیاز به درج') + '</span>');
                    topRow.append('<button type="button" class="smart-insert-lsi-btn button button-small" title="درج این کلمه در انتهای متن ویرایشگر">+ درج در متن</button>');
                    chip.append(topRow);

                    chip.append('<strong>' + $('<div>').text(lsi.keyword || '').html() + '</strong>');
                    if (lsi.suggestion) {
                        chip.append('<small>' + $('<div>').text(lsi.suggestion).html() + '</small>');
                    }
                    $lsiContainer.append(chip);
                });
            } else {
                $lsiContainer.append('<p class="description">کلمه LSI خاصی یافت نشد.</p>');
            }

            // لیست لینک‌سازی داخلی
            var $linkingList = $('#smart-linking-list').empty();
            if (data.internal_linking && data.internal_linking.length) {
                data.internal_linking.forEach(function(link) {
                    var li = $('<li class="linking-item"></li>');
                    li.attr('data-anchor', link.anchor_text || '');
                    li.attr('data-topic', link.target_topic || '');

                    var info = $('<div class="linking-info"></div>');
                    info.append('<span class="dashicons dashicons-admin-links"></span> انکر تکست پیشنهادی: <code>' + $('<div>').text(link.anchor_text || '').html() + '</code>');
                    info.append('<span class="target-topic"> 👈 پیوند به مقاله مرتبط با موضوع: <strong>' + $('<div>').text(link.target_topic || '').html() + '</strong></span>');
                    li.append(info);

                    var actions = $('<div class="linking-actions"></div>');
                    actions.append('<button type="button" class="smart-auto-link-btn button button-small button-primary" title="یافتن انکر تکست در متن و تبدیل آن به پیوند">🔗 لینک‌دار کردن در متن</button>');
                    actions.append('<button type="button" class="smart-copy-anchor-btn button button-small" title="کپی انکر تکست">کپی عبارت</button>');
                    li.append(actions);

                    li.append('<div class="linking-quick-search-result"></div>');
                    $linkingList.append(li);
                });
            } else {
                $linkingList.append('<p class="description">پیشنهاد لینک داخلی خاصی ثبت نشده است.</p>');
            }

            // لیست اسکیما FAQ
            var $faqList = $('#smart-schema-faq-list').empty();
            if (data.schema_faq && data.schema_faq.length) {
                data.schema_faq.forEach(function(faq) {
                    var card = $('<div class="faq-card"></div>');
                    card.append('<div class="faq-q">❓ ' + $('<div>').text(faq.question || '').html() + '</div>');
                    card.append('<div class="faq-a">💡 ' + $('<div>').text(faq.answer || '').html() + '</div>');
                    $faqList.append(card);
                });
                $('#smart_enable_faq_schema').prop('checked', true);
            }

            // پیش‌نمایش بهینه
            if (data.optimized_title) {
                $('#smart-suggested-title').val(data.optimized_title);
            }

            // نمایش گزینه‌های جایگزین عنوان (CTR Booster)
            var $variantsList = $('#smart-title-variants-list').empty();
            if (data.title_variants && data.title_variants.length) {
                data.title_variants.forEach(function(tv) {
                    var row = $('<div class="title-variant-row" style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; padding:4px 0; border-bottom:1px solid #f1f5f9;"></div>');
                    var left = $('<div style="font-size: 12.5px;"></div>');
                    left.append('<span class="variant-badge" style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:bold; margin-left:6px;">' + $('<div>').text(tv.type || 'پیشنهادی').html() + '</span>');
                    left.append('<span class="variant-text">' + $('<div>').text(tv.title || '').html() + '</span>');
                    row.append(left);

                    var btn = $('<button type="button" class="button button-small smart-pick-variant-btn">انتخاب این عنوان</button>');
                    btn.attr('data-title', tv.title || '');
                    row.append(btn);

                    $variantsList.append(row);
                });
            } else {
                $variantsList.append('<p class="description" style="margin:0;">عنوان جایگزین ثبت نشده است.</p>');
            }

            if (data.meta_description) {
                $('#smart-suggested-meta').val(data.meta_description);
            }
            if (data.optimized_content) {
                $('#smart-optimized-preview').html(data.optimized_content);
            }

            // اسکرول نرم به بخش نتایج
            $('html, body').animate({
                scrollTop: $('#smart-ai-results-container').offset().top - 80
            }, 500);
        }

        // اعمال متن نوشته بهینه‌شده با بکاپ‌گیری خودکار
        $('#smart-apply-content-btn').on('click', function(e) {
            e.preventDefault();
            var optimizedContent = $('#smart-optimized-preview').html();
            if (!optimizedContent || !optimizedContent.trim()) {
                alert('محتوای بهینه‌شده‌ای برای اعمال وجود ندارد.');
                return;
            }

            if (confirm(smartAiSeoData.i18n.applyConfirm)) {
                var currentContent = getCurrentPostContent();
                var currentTitle   = getCurrentPostTitle();
                var postId = $('#post_ID').val() || 0;

                // ذخیره بکاپ قبل از اعمال
                if (currentContent && postId) {
                    $.ajax({
                        url: smartAiSeoData.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'smart_ai_seo_backup_content',
                            nonce: smartAiSeoData.nonce,
                            post_id: postId,
                            content: currentContent,
                            title: currentTitle
                        }
                    });
                }

                var applied = applyContentToEditor(optimizedContent);
                if (applied) {
                    alert(smartAiSeoData.i18n.applied + '\n(یک نسخه پشتیبان از متن قبلی شما ذخیره شد تا در صورت نیاز بتوانید Undo کنید)');
                } else {
                    alert('خطا در یافتن ویرایشگر فعال.');
                }
            }
        });

        // بازگردانی نسخه قبلی (Undo)
        $('#smart-restore-content-btn').on('click', function(e) {
            e.preventDefault();
            var postId = $('#post_ID').val() || 0;
            if (!postId) {
                alert('شناسه نوشته نامعتبر است.');
                return;
            }

            if (!confirm('آیا می‌خواهید محتوای قبل از آخرین تغییرات هوش مصنوعی بازگردانی شود؟')) {
                return;
            }

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_restore_content',
                    nonce: smartAiSeoData.nonce,
                    post_id: postId
                },
                success: function(res) {
                    if (res.success && res.data && res.data.content) {
                        applyContentToEditor(res.data.content);
                        if (res.data.title) {
                            applyTitleToEditor(res.data.title);
                        }
                        alert('متن قبلی با موفقیت بازگردانی شد (تاریخ نسخه: ' + (res.data.time || '') + ')');
                    } else {
                        var err = res.data && res.data.message ? res.data.message : 'نسخه پشتیبانی یافت نشد.';
                        alert('خطا: ' + err);
                    }
                },
                error: function() {
                    alert('خطای ارتباط با سرور.');
                }
            });
        });

        // همگام سازی مستقیم با Rank Math و Yoast SEO
        function syncWithSeoTool(toolName) {
            var postId = $('#post_ID').val() || 0;
            var title = $('#smart-suggested-title').val();
            var desc = $('#smart-suggested-meta').val();
            var keyword = $('#smart_target_keyword').val();
            var $status = $('#smart-sync-status');

            if (!title && !desc) {
                alert('لطفاً ابتدا تحلیل را اجرا کنید.');
                return;
            }

            $status.css('color', '#666').text('در حال همگام‌سازی...');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_sync_plugin',
                    nonce: smartAiSeoData.nonce,
                    post_id: postId,
                    target_tool: toolName,
                    title: title,
                    desc: desc,
                    keyword: keyword
                },
                success: function(res) {
                    if (res.success) {
                        $status.css('color', '#15803d').text('✅ ' + res.data.message);
                    } else {
                        $status.css('color', '#b91c1c').text('❌ ' + (res.data.message || 'خطا'));
                    }
                },
                error: function() {
                    $status.css('color', '#b91c1c').text('❌ خطای ارتباط');
                }
            });
        }

        $('#smart-sync-rankmath-btn').on('click', function(e) {
            e.preventDefault();
            syncWithSeoTool('rank_math');
        });

        $('#smart-sync-yoast-btn').on('click', function(e) {
            e.preventDefault();
            syncWithSeoTool('yoast');
        });

        // -------------------------------------------------------------
        // رویدادهای صفحه تولید محتوای هوشمند (AI Writer & Topic Cluster)
        // -------------------------------------------------------------

        // ۱. نگارش مقاله تکی
        $('#smart-start-single-gen').on('click', function(e) {
            e.preventDefault();

            var kw = $('#gen_keyword').val().trim();
            var lsi = $('#gen_lsi').val().trim();
            var tone = $('#gen_tone').val();
            var length = $('#gen_length').val() || 'standard';
            var goal = $('#gen_goal').val() || 'traffic';
            var audience = $('#gen_target_audience').val().trim();
            var ctaUrl = $('#gen_cta_url').val().trim();
            var ctaAnchor = $('#gen_cta_anchor').val().trim();
            var sourceRef = $('#gen_source_reference').val().trim();
            var status = $('#gen_status').val() || 'draft';
            var categoryId = $('#gen_category').val() || 0;
            var imageSource = $('#gen_image_source').val() || 'ai_pollinations';
            var instructions = $('#gen_instructions').val().trim();

            if (!kw) {
                alert('لطفاً کلمه کلیدی اصلی را وارد کنید.');
                $('#gen_keyword').focus();
                return;
            }

            var $btn = $(this);
            var $spinner = $('#gen-single-spinner');
            var $resultBox = $('#gen-single-result-box');

            $btn.prop('disabled', true).text('در حال نگارش مقاله سئو شده و استراتژیک...');
            $spinner.addClass('is-active');
            $resultBox.addClass('smart-hidden');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_gen_single',
                    nonce: smartAiSeoData.nonce,
                    keyword: kw,
                    lsi: lsi,
                    tone: tone,
                    length: length,
                    goal: goal,
                    audience: audience,
                    cta_url: ctaUrl,
                    cta_anchor: ctaAnchor,
                    source_reference: sourceRef,
                    status: status,
                    category_id: categoryId,
                    image_source: imageSource,
                    instructions: instructions
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('🚀 نگارش و ایجاد پیش‌نویس کامل در وردپرس');
                    $spinner.removeClass('is-active');

                    if (res.success && res.data) {
                        $('#res-single-title').text(res.data.title);
                        $('#res-single-slug').text(res.data.slug);
                        $('#res-single-meta').text(res.data.meta_desc);
                        $('#res-single-edit-link').attr('href', res.data.edit_url);
                        $('#res-single-preview-link').attr('href', res.data.preview_url);

                        var statusLabel = (status === 'publish') ? '«منتشر شده»' : '«پیش‌نویس»';
                        $('#gen-single-result-box h3').text('✅ مقاله با موفقیت تولید و به صورت ' + statusLabel + ' ذخیره شد!');

                        $resultBox.removeClass('smart-hidden');
                        $('html, body').animate({
                            scrollTop: $resultBox.offset().top - 60
                        }, 400);
                    } else {
                        var err = res.data && res.data.message ? res.data.message : 'خطا در تولید محتوا';
                        alert('خطا: ' + err);
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).text('🚀 نگارش و ایجاد پیش‌نویس کامل در وردپرس');
                    $spinner.removeClass('is-active');
                    alert('خطای سرور: ' + error);
                }
            });
        });

        // ۲. طراحی نقشه استراتژی خوشه موضوعی
        var currentClusterPlan = null;

        $('#smart-start-cluster-plan').on('click', function(e) {
            e.preventDefault();

            var topic = $('#cluster_core_topic').val().trim();
            var count = $('#cluster_count').val();
            var clusterGoal = $('#cluster_goal').val() || 'traffic';
            var clusterAudience = $('#cluster_target_audience').val().trim();
            var clusterCtaUrl = $('#cluster_cta_url').val().trim();
            var clusterCtaAnchor = $('#cluster_cta_anchor').val().trim();
            var clusterSourceRef = $('#cluster_source_reference').val().trim();

            if (!topic) {
                alert('لطفاً موضوع کلان اصلی را وارد نمایید.');
                $('#cluster_core_topic').focus();
                return;
            }

            var $btn = $(this);
            var $spinner = $('#cluster-spinner');
            var $resContainer = $('#cluster-result-container');

            $btn.prop('disabled', true).text('در حال طراحی استراتژی خوشه محتوایی...');
            $spinner.addClass('is-active');
            $resContainer.addClass('smart-hidden');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_gen_cluster_plan',
                    nonce: smartAiSeoData.nonce,
                    core_topic: topic,
                    count: count,
                    goal: clusterGoal,
                    audience: clusterAudience,
                    cta_url: clusterCtaUrl,
                    cta_anchor: clusterCtaAnchor,
                    source_reference: clusterSourceRef
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('🕸️ طراحی نقشه خوشه محتوایی');
                    $spinner.removeClass('is-active');

                    if (res.success && res.data && res.data.plan) {
                        currentClusterPlan = res.data.plan;
                        renderClusterPlan(currentClusterPlan);
                        $resContainer.removeClass('smart-hidden');
                        $('html, body').animate({
                            scrollTop: $resContainer.offset().top - 60
                        }, 400);
                    } else {
                        var err = res.data && res.data.message ? res.data.message : 'خطا در طراحی خوشه';
                        alert('خطا: ' + err);
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).text('🕸️ طراحی نقشه خوشه محتوایی');
                    $spinner.removeClass('is-active');
                    alert('خطای سرور: ' + error);
                }
            });
        });

        // رندر کردن ساختار خوشه در صفحه
        function renderClusterPlan(plan) {
            if (!plan || !plan.pillar) return;

            // نمایش ستون اصلی
            $('#pillar-title').text(plan.pillar.title);
            $('#pillar-keyword').text(plan.pillar.keyword);
            $('#pillar-slug').text(plan.pillar.slug);
            $('#pillar-desc').text(plan.pillar.meta_description);

            var $outlines = $('#pillar-outlines').empty();
            if (plan.pillar.outline && plan.pillar.outline.length) {
                $outlines.append('<strong>سرفصل‌های پیشنهادی ستون:</strong> ');
                var ul = $('<ul style="margin:6px 15px;"></ul>');
                plan.pillar.outline.forEach(function(h) {
                    ul.append('<li>' + $('<div>').text(h).html() + '</li>');
                });
                $outlines.append(ul);
            }

            // نمایش مقالات فرعی
            var $clustersList = $('#cluster-subpages-list').empty();
            if (plan.clusters && plan.clusters.length) {
                plan.clusters.forEach(function(item, idx) {
                    var card = $('<div class="cluster-card" data-index="' + idx + '"></div>');
                    var header = $('<div style="display:flex; justify-content:space-between; align-items:center;"></div>');
                    header.append('<span class="cluster-badge">اقماری #' + (idx + 1) + ' (' + (item.search_intent || 'اطلاعاتی') + ')</span>');
                    header.append('<button type="button" class="smart-remove-cluster-item-btn" title="حذف این مقاله از خوشه" style="background:none; border:none; color:#ef4444; font-size:16px; cursor:pointer; padding:0 4px;">✕ حذف</button>');
                    card.append(header);

                    card.append('<h4>' + $('<div>').text(item.title || '').html() + '</h4>');
                    card.append('<p><strong>کلمه هدف:</strong> ' + $('<div>').text(item.keyword || '').html() + '</p>');
                    card.append('<p><strong>انکر به ستون:</strong> <code>' + $('<div>').text(item.anchor_to_pillar || '').html() + '</code></p>');
                    card.append('<p class="description">' + $('<div>').text(item.meta_description || '').html() + '</p>');
                    card.append('<div style="margin-top:10px;"><button type="button" class="button button-small button-primary smart-gen-single-subcluster" data-index="' + idx + '">✍️ نگارش پیش‌نویس این مقاله</button> <span class="cluster-item-status" style="margin-right:8px;"></span></div>');
                    $clustersList.append(card);
                });
            }
        }

        // ایجاد پیش نویس برای مقاله ستون
        $(document).on('click', '.smart-generate-cluster-item-btn', function(e) {
            e.preventDefault();
            if (!currentClusterPlan || !currentClusterPlan.pillar) return;

            var $btn = $(this);
            var $status = $('.pillar-status-msg');
            $btn.prop('disabled', true).text('در حال نگارش مقاله ستون...');
            $status.css('color', '#666').text('⏳ در حال تولید متن جامع...');

            var pillar = currentClusterPlan.pillar;
            var instructions = 'این یک مقاله ستون (Pillar Page) جامع است. سرفصل‌های الزامی: ' + (pillar.outline ? pillar.outline.join(' ، ') : '');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_gen_single',
                    nonce: smartAiSeoData.nonce,
                    keyword: pillar.keyword,
                    lsi: pillar.title,
                    tone: 'جامع و مرجع سئو',
                    instructions: instructions
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('✍️ ایجاد مجدد پیش‌نویس');
                    if (res.success && res.data) {
                        $status.html('✅ پیش‌نویس ایجاد شد! <a href="' + res.data.edit_url + '" target="_blank" class="button button-small">ویرایش مقاله</a>');
                    } else {
                        $status.css('color', '#ef4444').text('خطا: ' + (res.data.message || 'ناموفق'));
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('✍️ ایجاد پیش‌نویس');
                    $status.css('color', '#ef4444').text('خطای ارتباط');
                }
            });
        });

        // ایجاد پیش نویس برای تک تک مقالات فرعی خوشه
        $(document).on('click', '.smart-gen-single-subcluster', function(e) {
            e.preventDefault();
            var idx = $(this).data('index');
            if (!currentClusterPlan || !currentClusterPlan.clusters || !currentClusterPlan.clusters[idx]) return;

            var item = currentClusterPlan.clusters[idx];
            var $btn = $(this);
            var $status = $btn.siblings('.cluster-item-status');

            $btn.prop('disabled', true).text('در حال نگارش...');
            $status.css('color', '#666').text('⏳ تولید محتوا...');

            var instructions = 'این مقاله فرعی یک خوشه است. انکرتکست «' + (item.anchor_to_pillar || '') + '» برای لینک دادن به مقاله ستون موضوع در متن در نظر گرفته شود.';

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_gen_single',
                    nonce: smartAiSeoData.nonce,
                    keyword: item.keyword,
                    lsi: item.title,
                    tone: 'آموزشی و کاربردی',
                    instructions: instructions
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('✍️ بازنویسی');
                    if (res.success && res.data) {
                        $status.html('✅ <a href="' + res.data.edit_url + '" target="_blank">ویرایش پیش‌نویس</a>');
                    } else {
                        $status.css('color', '#ef4444').text('خطا');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('✍️ نگارش');
                    $status.css('color', '#ef4444').text('خطا');
                }
            });
        });

        // رویداد حذف یک مقاله فرعی از خوشه قبل از تولید
        $(document).on('click', '.smart-remove-cluster-item-btn', function(e) {
            e.preventDefault();
            var $card = $(this).closest('.cluster-card');
            var idx = parseInt($card.attr('data-index'), 10);

            if (confirm('آیا می‌خواهید این موضوع فرعی را از این خوشه حذف کنید؟')) {
                if (currentClusterPlan && currentClusterPlan.clusters && currentClusterPlan.clusters[idx] !== undefined) {
                    currentClusterPlan.clusters.splice(idx, 1);
                    renderClusterPlan(currentClusterPlan);
                }
            }
        });

        // متغیر کنترل لغو و توقف صف تولید خودکار
        var isBulkGeneratingCancelled = false;

        // دکمه لغو و توقف تولید خودکار
        $('#smart-cancel-bulk-generate').on('click', function(e) {
            e.preventDefault();
            if (confirm('آیا می‌خواهید ادامه فرآیند تولید مقالات خوشه متوقف شود؟ (مقالات تولید شده تا این لحظه در وردپرس باقی می‌مانند)')) {
                isBulkGeneratingCancelled = true;
                $('#cluster-bulk-status-text').html('<span style="color:#b91c1c;">⚠️ عملیات تولید توسط شما متوقف شد.</span>');
                $('#smart-bulk-generate-cluster').prop('disabled', false).text('⚡ ادامه یا تولید مجدد کل خوشه');
                $(this).prop('disabled', true);
            }
        });

        // ۳. تولید و زمان‌بندی خودکار کل خوشه (Bulk Drip Generation)
        $('#smart-bulk-generate-cluster').on('click', function(e) {
            e.preventDefault();
            if (!currentClusterPlan || !currentClusterPlan.pillar || !currentClusterPlan.clusters) {
                alert('ابتدا باید نقشه خوشه طراحی شود.');
                return;
            }

            var intervalSetting = $('#cluster_schedule_interval').val();
            var clusterCategoryId = $('#cluster_category').val() || 0;
            var clusterImageSource = $('#cluster_image_source').val() || 'ai_pollinations';
            var confirmMsg = 'آیا مطمئن هستید که می‌خواهید مقاله ستون و تمام ' + currentClusterPlan.clusters.length + ' مقاله اقماری به صورت خودکار تولید و طبق برنامه زمان‌بندی ثبت شوند؟';
            if (!confirm(confirmMsg)) {
                return;
            }

            isBulkGeneratingCancelled = false; // ریست فلگ لغو
            $('#smart-cancel-bulk-generate').prop('disabled', false);

            var $bulkBtn = $(this);
            var $progressBar = $('#cluster-bulk-progress');
            var $statusText = $('#cluster-bulk-status-text');

            $bulkBtn.prop('disabled', true);
            $progressBar.removeClass('smart-hidden');

            // ساخت صف مقالات (مقاله ستون + مقالات فرعی)
            var queue = [];
            var intervalDays = (intervalSetting === 'publish_all' || intervalSetting === '0') ? 0 : parseInt(intervalSetting, 10);
            var statusMode = (intervalSetting === 'publish_all') ? 'publish' : 'draft';

            // آیتم ۱: مقاله ستون (روز صفر)
            queue.push({
                type: 'pillar',
                keyword: currentClusterPlan.pillar.keyword,
                lsi: currentClusterPlan.pillar.title,
                instructions: 'مقاله ستون (Pillar Page) مرجع کل موضوع. سرفصل‌ها: ' + (currentClusterPlan.pillar.outline ? currentClusterPlan.pillar.outline.join(' ، ') : ''),
                schedule_days: 0,
                status: statusMode,
                category_id: clusterCategoryId,
                image_source: clusterImageSource,
                title: currentClusterPlan.pillar.title,
                goal: $('#cluster_goal').val() || 'traffic',
                audience: $('#cluster_target_audience').val().trim(),
                cta_url: $('#cluster_cta_url').val().trim(),
                cta_anchor: $('#cluster_cta_anchor').val().trim(),
                source_reference: $('#cluster_source_reference').val().trim(),
                length: 'pillar'
            });

            // آیتم‌های بعدی: مقالات فرعی با فواصل زمانی
            currentClusterPlan.clusters.forEach(function(item, idx) {
                var daysFromNow = (intervalDays > 0) ? (idx + 1) * intervalDays : 0;
                queue.push({
                    type: 'cluster',
                    index: idx,
                    keyword: item.keyword,
                    lsi: item.title,
                    instructions: 'مقاله فرعی اقماری. انکر تکست به ستون: ' + (item.anchor_to_pillar || ''),
                    schedule_days: daysFromNow,
                    status: statusMode,
                    category_id: clusterCategoryId,
                    image_source: clusterImageSource,
                    title: item.title,
                    goal: $('#cluster_goal').val() || 'traffic',
                    audience: $('#cluster_target_audience').val().trim(),
                    cta_url: $('#cluster_cta_url').val().trim(),
                    cta_anchor: $('#cluster_cta_anchor').val().trim(),
                    source_reference: $('#cluster_source_reference').val().trim(),
                    length: 'standard'
                });
            });

            var total = queue.length;
            var currentIdx = 0;

            function processQueue() {
                // اگر کاربر دکمه لغو را زده باشد، متوقف شو
                if (isBulkGeneratingCancelled) {
                    return;
                }

                if (currentIdx >= total) {
                    $statusText.html('🎉 تمام ' + total + ' مقاله خوشه با تصاویر و متادیتاها در وردپرس ثبت شدند!');
                    $bulkBtn.prop('disabled', false).text('✅ فرآیند تولید کامل شد');
                    return;
                }

                var item = queue[currentIdx];
                var scheduleNote = (item.schedule_days > 0) ? (' [زمان‌بندی: ' + item.schedule_days + ' روز بعد]') : (' [' + statusMode + ']');
                $statusText.text('در حال نگارش و تولید تصویر (' + (currentIdx + 1) + ' از ' + total + '): ' + item.title + scheduleNote);

                $.ajax({
                    url: smartAiSeoData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'smart_ai_seo_gen_single',
                        nonce: smartAiSeoData.nonce,
                        keyword: item.keyword,
                        lsi: item.lsi,
                        tone: 'جامع و کاربردی',
                        status: item.status,
                        category_id: item.category_id,
                        image_source: item.image_source,
                        schedule_days: item.schedule_days,
                        goal: item.goal,
                        audience: item.audience,
                        cta_url: item.cta_url,
                        cta_anchor: item.cta_anchor,
                        source_reference: item.source_reference,
                        length: item.length,
                        instructions: item.instructions
                    },
                    success: function(res) {
                        if (item.type === 'pillar') {
                            $('.pillar-status-msg').html('✅ پیش‌نویس ستون ثبت شد');
                        } else {
                            var $card = $('.cluster-card').eq(item.index);
                            $card.find('.cluster-item-status').html('✅ ثبت شد' + (item.schedule_days > 0 ? ' (' + item.schedule_days + ' روز دیگر)' : ''));
                        }

                        currentIdx++;
                        if (!isBulkGeneratingCancelled) {
                            setTimeout(processQueue, 1500); // وقفه کوتاه برای حفظ پایداری سرور و ریت‌لیمیت
                        }
                    },
                    error: function() {
                        $statusText.text('خطا در تولید مقاله: ' + item.title + '. در حال ادامه مابقی مقالات...');
                        currentIdx++;
                        if (!isBulkGeneratingCancelled) {
                            setTimeout(processQueue, 2000);
                        }
                    }
                });
            }

            processQueue();
        });

        // اعمال عنوان بهینه‌شده
        $('#smart-apply-title-btn').on('click', function(e) {
            e.preventDefault();
            var newTitle = $('#smart-suggested-title').val();
            if (newTitle) {
                applyTitleToEditor(newTitle);
                alert('عنوان نوشته با موفقیت جایگزین شد.');
            }
        });

        // انتخاب عنوان از میان گزینه‌های CTR Booster
        $(document).on('click', '.smart-pick-variant-btn', function(e) {
            e.preventDefault();
            var pickedTitle = $(this).data('title');
            if (pickedTitle) {
                $('#smart-suggested-title').val(pickedTitle);
                applyTitleToEditor(pickedTitle);
                alert('عنوان نوشته با موفقیت به «' + pickedTitle + '» تغییر یافت.');
            }
        });

        // تست اتصال در صفحه تنظیمات
        $('#smart-test-connection').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $status = $('#test-connection-result');

            $btn.prop('disabled', true);
            $status.css('color', '#666').text('در حال تست ارتباط...');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_test_connection',
                    nonce: smartAiSeoData.nonce
                },
                success: function(res) {
                    $btn.prop('disabled', false);
                    if (res.success) {
                        $status.css('color', '#15803d').text('✅ ' + res.data.message);
                    } else {
                        var err = res.data && res.data.message ? res.data.message : 'خطا در ارتباط';
                        $status.css('color', '#b91c1c').text('❌ ' + err);
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false);
                    $status.css('color', '#b91c1c').text('❌ خطای سرور: ' + error);
                }
            });
        });

        // -------------------------------------------------------------
        // رویدادها و منطق بهینه‌سازی و رفرش گروهی مقالات (Bulk Optimizer)
        // -------------------------------------------------------------

        // اگر از طریق Bulk Actions به این برگه هدایت شده باشد، خودکار تب را باز کند
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'bulk-optimizer') {
            $('.smart-tab-btn[data-tab="bulk-optimizer"]').trigger('click');
            if (urlParams.get('bulk_added')) {
                alert('تعداد ' + urlParams.get('bulk_added') + ' مقاله با موفقیت از جدول نوشته‌ها به صف پردازش گروهی اضافه شدند.');
            }
        }

        // بارگذاری خودکار وضعیت صف هنگام ورود به صفحه
        function refreshQueueTable() {
            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_bulk_get_queue_status',
                    nonce: smartAiSeoData.nonce
                },
                success: function(res) {
                    if (res.success && res.data && res.data.queue) {
                        renderQueueRows(res.data.queue);
                    }
                }
            });
        }

        if ($('#bulk-queue-tbody').length) {
            refreshQueueTable();
        }

        function renderQueueRows(queue) {
            var $tbody = $('#bulk-queue-tbody').empty();
            var total = queue.length;
            var completedCount = 0;
            $('#queue-count-badge').text(total);

            if (!total) {
                $tbody.append('<tr><td colspan="6" style="text-align:center; color:#94a3b8; padding:20px;">هنوز مقاله‌ای در صف پردازش قرار ندارد.</td></tr>');
                $('#bulk-progress-container').hide();
                return;
            }

            queue.forEach(function(item) {
                var tr = $('<tr></tr>');
                tr.attr('id', 'queue-row-' + item.post_id);

                var statusBadge = '';
                if (item.status === 'completed') {
                    completedCount++;
                    statusBadge = '<span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;">✅ انجام شد</span>';
                } else if (item.status === 'processing') {
                    statusBadge = '<span style="background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;"><span class="spinner is-active" style="float:none; margin:0 3px;"></span> در حال پردازش</span>';
                } else if (item.status === 'error') {
                    statusBadge = '<span style="background:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;" title="' + $('<div>').text(item.error_msg || '').html() + '">❌ خطا</span>';
                } else {
                    statusBadge = '<span style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:10px; font-size:11px;">⏳ در انتظار</span>';
                }

                var modeLabel = 'احیا و رفرش محتوا';
                if (item.mode === 'auto_optimize') modeLabel = 'سئو و کلمات کلیدی';
                if (item.mode === 'update_title_meta_only') modeLabel = 'عنوان و متا';

                tr.append('<td>#' + item.post_id + '</td>');
                tr.append('<td><strong>' + $('<div>').text(item.title || ('نوشته ' + item.post_id)).html() + '</strong></td>');
                tr.append('<td><span style="font-size:11px; color:#64748b;">' + modeLabel + '</span></td>');
                tr.append('<td class="status-cell">' + statusBadge + '</td>');
                tr.append('<td>' + (item.new_score ? ('<strong>' + item.new_score + '/100</strong>') : '---') + '</td>');
                tr.append('<td><a href="post.php?post=' + item.post_id + '&action=edit" target="_blank" class="button button-small">مشاهده و ویرایش</a></td>');

                $tbody.append(tr);
            });

            // درصد پیشرفت
            if (total > 0) {
                $('#bulk-progress-container').show();
                var percent = Math.round((completedCount / total) * 100);
                $('#bulk-progress-bar').css('width', percent + '%').text(percent + '% (' + completedCount + ' از ' + total + ')');
            }
        }

        // بارگذاری مقالات کاندید
        $('#smart-bulk-fetch-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var filterType = $('#bulk_filter_type').val();
            var categoryId = $('#bulk_category_id').val();
            var limit = $('#bulk_limit').val();

            $btn.prop('disabled', true).text('در حال جستجو...');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_bulk_fetch_candidates',
                    nonce: smartAiSeoData.nonce,
                    filter_type: filterType,
                    category_id: categoryId,
                    limit: limit
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('🔍 بارگذاری لیست مقالات کاندید');
                    if (res.success && res.data && res.data.candidates) {
                        renderCandidates(res.data.candidates);
                    } else {
                        alert('مقاله‌ای یافت نشد.');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('🔍 بارگذاری لیست مقالات کاندید');
                    alert('خطای سرور');
                }
            });
        });

        function renderCandidates(candidates) {
            var $tbody = $('#bulk-candidates-tbody').empty();
            if (!candidates.length) {
                $tbody.append('<tr><td colspan="5" style="text-align:center; padding:15px;">مقاله‌ای با این مشخصات یافت نشد.</td></tr>');
            } else {
                candidates.forEach(function(item) {
                    var tr = $('<tr></tr>');
                    tr.append('<td><input type="checkbox" class="bulk-item-check" value="' + item.id + '" /></td>');
                    tr.append('<td><strong>' + $('<div>').text(item.title).html() + '</strong></td>');
                    tr.append('<td><span style="color:#64748b; font-size:12px;">' + $('<div>').text(item.keyword).html() + '</span></td>');
                    tr.append('<td><span class="badge" style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px;">' + item.score + '</span></td>');
                    tr.append('<td><small style="color:#94a3b8;">' + item.modified + ' (' + item.days_passed + ' روز پیش)</small></td>');
                    $tbody.append(tr);
                });
            }
            $('#bulk-candidates-container').removeClass('smart-hidden');
        }

        // چک باکس انتخاب همه
        $('#bulk-check-master').on('change', function() {
            $('.bulk-item-check').prop('checked', $(this).is(':checked'));
        });
        $('#bulk-select-all').on('click', function(e) {
            e.preventDefault();
            $('.bulk-item-check').prop('checked', true);
            $('#bulk-check-master').prop('checked', true);
        });
        $('#bulk-deselect-all').on('click', function(e) {
            e.preventDefault();
            $('.bulk-item-check').prop('checked', false);
            $('#bulk-check-master').prop('checked', false);
        });

        // افزودن انتخاب شده‌ها به صف
        $('#smart-add-selected-to-queue').on('click', function(e) {
            e.preventDefault();
            var selectedIds = [];
            $('.bulk-item-check:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (!selectedIds.length) {
                alert('لطفاً حداقل یک مقاله را برای افزودن به صف تیک بزنید.');
                return;
            }

            var mode = $('#bulk_opt_mode').val();
            var $btn = $(this);
            $btn.prop('disabled', true).text('در حال افزودن به صف...');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_bulk_add_to_queue',
                    nonce: smartAiSeoData.nonce,
                    post_ids: selectedIds,
                    mode: mode
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('➕ افزودن مقالات انتخاب‌شده به صف پردازش');
                    if (res.success && res.data) {
                        alert(res.data.message);
                        if (res.data.queue) {
                            renderQueueRows(res.data.queue);
                        }
                    } else {
                        alert('خطا در افزودن به صف');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('➕ افزودن مقالات انتخاب‌شده به صف پردازش');
                    alert('خطای سرور');
                }
            });
        });

        // پاکسازی کامل صف
        $('#smart-bulk-clear-queue').on('click', function(e) {
            e.preventDefault();
            if (!confirm('آیا مطمئن هستید که می‌خواهید تمام آیتم‌های صف را پاک کنید؟')) {
                return;
            }

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_bulk_clear_queue',
                    nonce: smartAiSeoData.nonce
                },
                success: function(res) {
                    if (res.success) {
                        renderQueueRows([]);
                    }
                }
            });
        });

        // پردازش زنده ترتیبی مقالات در مرورگر
        var isBulkRunning = false;
        var isBulkPaused = false;

        $('#smart-bulk-start-processing').on('click', function(e) {
            e.preventDefault();
            if (isBulkRunning) return;

            isBulkRunning = true;
            isBulkPaused = false;
            $(this).hide();
            $('#smart-bulk-pause-processing').show();

            processNextBulkItem();
        });

        $('#smart-bulk-pause-processing').on('click', function(e) {
            e.preventDefault();
            isBulkPaused = true;
            isBulkRunning = false;
            $(this).hide();
            $('#smart-bulk-start-processing').show().text('▶️ ادامه پردازش صف');
        });

        function processNextBulkItem() {
            if (isBulkPaused) return;

            // پیدا کردن اولین ردیفی که وضعیت در انتظار دارد
            var $pendingRow = $('#bulk-queue-tbody tr').filter(function() {
                return $(this).find('.status-cell').text().indexOf('در انتظار') !== -1;
            }).first();

            if (!$pendingRow.length) {
                isBulkRunning = false;
                $('#smart-bulk-pause-processing').hide();
                $('#smart-bulk-start-processing').show().text('▶️ شروع پردازش ترتیبی در مرورگر');
                alert('پردازش تمام مقالات موجود در صف با موفقیت به پایان رسید! 🎉');
                return;
            }

            var rowId = $pendingRow.attr('id');
            var postId = rowId.replace('queue-row-', '');
            var mode = $('#bulk_opt_mode').val() || 'smart_refresh';

            $pendingRow.find('.status-cell').html('<span style="background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;"><span class="spinner is-active" style="float:none; margin:0 3px;"></span> در حال هوشمندسازی...</span>');

            $.ajax({
                url: smartAiSeoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_ai_seo_bulk_process_single',
                    nonce: smartAiSeoData.nonce,
                    post_id: postId,
                    mode: mode
                },
                success: function(res) {
                    if (res.success && res.data) {
                        $pendingRow.find('.status-cell').html('<span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;">✅ انجام شد</span>');
                        $pendingRow.find('td:nth-child(5)').html('<strong>' + (res.data.seo_score || 85) + '/100</strong>');
                        if (res.data.title) {
                            $pendingRow.find('td:nth-child(2) strong').text(res.data.title);
                        }
                    } else {
                        var err = res.data && res.data.message ? res.data.message : 'خطا';
                        $pendingRow.find('.status-cell').html('<span style="background:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;" title="' + $('<div>').text(err).html() + '">❌ خطا</span>');
                    }

                    // بروزرسانی نوار پیشرفت
                    refreshQueueTable();

                    // رفتن به آیتم بعدی با تاخیر ۱.۵ ثانیه‌ای جهت عدم اسپم به سرور
                    setTimeout(function() {
                        if (!isBulkPaused) {
                            processNextBulkItem();
                        }
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    $pendingRow.find('.status-cell').html('<span style="background:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:bold;" title="' + $('<div>').text(error).html() + '">❌ خطای سرور</span>');
                    setTimeout(function() {
                        if (!isBulkPaused) {
                            processNextBulkItem();
                        }
                    }, 2000);
                }
            });
        }

    });
})(jQuery);
