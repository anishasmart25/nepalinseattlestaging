(()=>{var e;(e=jQuery)((function(){e(".store-email-label").on("click",(function(){e(this).parents(".store-email").find("#store-email-area").slideDown()})),e(".fade-anchor .fade-anchor-text").on("click",(function(t){return t.preventDefault(),e("#store-details-modal").modal("show"),!1})),e(".rtcl-promotions-heading").on("click",(function(){var t=e(this).attr("id");e(this).hasClass("active")?"rtcl-regular-promotions-heading"===t?(e("#rtcl-regular-promotions-heading").removeClass("active"),e("#rtcl-checkout-form, #rtcl-woo-checkout-form").slideUp((function(){e(document.body).trigger("rtcl_checkout_form_hidden")})),e("#rtcl-membership-promotions-heading").addClass("active"),e(".rtcl-membership-promotions-form-wrap").slideDown((function(){e(document.body).trigger("rtcl_membership_promotions_form_opened")}))):(e("#rtcl-membership-promotions-heading").removeClass("active"),e(".rtcl-membership-promotions-form-wrap").slideUp((function(){e(document.body).trigger("rtcl_membership_promotions_form_hidden")})),e("#rtcl-regular-promotions-heading").addClass("active"),e("#rtcl-checkout-form, #rtcl-woo-checkout-form").slideDown((function(){e(document.body).trigger("rtcl_checkout_form_opened")}))):"rtcl-regular-promotions-heading"===t?(e("#rtcl-regular-promotions-heading").addClass("active"),e("#rtcl-checkout-form, #rtcl-woo-checkout-form").slideDown((function(){e(document.body).trigger("rtcl_checkout_form_opened")})),e("#rtcl-membership-promotions-heading").removeClass("active"),e(".rtcl-membership-promotions-form-wrap").slideUp((function(){e(document.body).trigger("rtcl_membership_promotions_form_hidden")}))):(e("#rtcl-membership-promotions-heading").addClass("active"),e(".rtcl-membership-promotions-form-wrap").slideDown((function(){e(document.body).trigger("rtcl_membership_promotions_form_opened")})),e("#rtcl-regular-promotions-heading").removeClass("active"),e("#rtcl-checkout-form, #rtcl-woo-checkout-form").slideUp((function(){e(document.body).trigger("rtcl_checkout_form_hidden")})))})),e("#rtcl_store_load_more").on("click",(function(t){t.preventDefault();var r=e(this).parent(".load-more-wrapper"),a=r.data("page"),o=r.data("options"),c=r.data("query"),n=r.data("layout"),s=r.data("total-pages"),i=r.data("posts-per-page");e.ajax({type:"POST",url:rtcl_store_public.ajaxurl,data:{action:"rtcl_store_load_more_store",offset:a*i,layout:n,post_per_page:i,display:o,queryArg:c},beforeSend:function(){r.addClass("loading")},success:function(t){a++,r.data("page",a),e(".rtcl-el-store-widget-wrapper .rtcl-elementor-widget").append(t),r.removeClass("loading"),a>=s&&r.hide()},error:function(e){console.log(e)}})})),e(document).on("rtcl_recaptcha_loaded",(function(){var t=e("form.store-email-form, form#store-email-form");t.length&&"undefined"!=typeof grecaptcha&&-1!==e.inArray("store_contact",rtcl.recaptcha.on)&&t.each((function(t,r){var a=e(r);if(!a.data("reCaptchaId")){var o={sitekey:rtcl.recaptcha.site_key};a.find("#rtcl-store-contact-g-recaptcha").length?a.data("reCaptchaId",grecaptcha.render(a.find("#rtcl-store-contact-g-recaptcha")[0],o)):a.find(".rtcl-g-recaptcha-wrap").length&&a.data("reCaptchaId",grecaptcha.render(a.find(".rtcl-g-recaptcha-wrap")[0],o))}}))})),e.fn.validate&&(e("#rtcl-membership-promotions-form").validate({submitHandler:function(t){var r=e(t),a=new FormData(t);a.append("action","rtcl_store_ajax_membership_promotion"),a.append("__rtcl_wpnonce",rtcl_store_public.__rtcl_wpnonce),e.ajax({type:"POST",url:rtcl_store_public.ajaxurl,data:a,cache:!1,contentType:!1,processData:!1,beforeSend:function(){r.rtclBlock()},success:function(e){r.rtclUnblock(),e.success?(toastr.success(e.data.message),e.data.redirect_url&&(e.data.redirect_utl===window.location.href?window.location.reload(!0):window.location=e.data.redirect_url+"?t="+(new Date).getTime())):toastr.error(e.data)},error:function(e,t){r.rtclUnblock(),toastr.error(rtcl_validator.messages.server_error)}})}}),e("form.store-email-form, form#store-email-form").each((function(){e(this).validate({submitHandler:function(t){var r=e(t),a=r.find(".sc-submit"),o=r.find(".rtcl-response"),c=e("<div class='alert'></div>"),n=r.data("reCaptchaId");if(rtcl.recaptcha&&"undefined"!=typeof grecaptcha&&rtcl.recaptcha.on&&-1!==e.inArray("store_contact",rtcl.recaptcha.on)){if(2===rtcl.recaptcha.v&&void 0!==n){var s=grecaptcha.getResponse(n);return o.html(""),0===s.length?(o.removeClass("text-success").addClass("text-danger").html(rtcl.recaptcha.msg.invalid),grecaptcha.reset(n),!1):(i(s),!1)}if(3===rtcl.recaptcha.v)return grecaptcha.ready((function(){r.rtclBlock(),grecaptcha.execute(rtcl.recaptcha.site_key,{action:"store_contact"}).then((function(e){r.rtclUnblock(),i(e)}))})),!1}return i(),!1;function i(s){var i=new FormData(t);i.append("action","rtcl_send_mail_to_store_owner"),i.append("store_id",rtcl_store_public.store_id||0),i.append("__rtcl_wpnonce",rtcl.__rtcl_wpnonce),s&&i.append("g-recaptcha-response",s),e.ajax({url:rtcl_store_public.ajaxurl,dataType:"json",data:i,type:"POST",processData:!1,contentType:!1,cache:!1,beforeSend:function(){r.rtclBlock(),r.addClass("rtcl-loading"),r.find("input textarea").prop("disabled",!0),a.prop("disabled",!0),o.html(""),e('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(a)},success:function(e){r.rtclUnblock(),a.prop("disabled",!1).next(".rtcl-icon-spinner").remove(),r.find("input textarea").prop("disabled",!1),r.removeClass("rtcl-loading"),e.success?(c.removeClass("alert-danger").addClass("alert-success").html(e.data.message).appendTo(o),r[0].reset(),0!==r.parent("#store-email-area").parent().data("hide")&&setTimeout((function(){o.html(""),r.parent("#store-email-area").slideUp()}),1e3)):c.removeClass("alert-success").addClass("alert-danger").html(e.data.error).appendTo(o),rtcl.recaptcha&&2===rtcl.recaptcha.v&&void 0!==n&&grecaptcha.reset(n)},error:function(e){r.rtclUnblock(),r.find("input textarea").prop("disabled",!1),c.removeClass("alert-success").addClass("alert-danger").html(e.responseText).appendTo(o),a.prop("disabled",!1).next(".rtcl-icon-spinner").remove(),r.removeClass("rtcl-loading")}})}}})}))),e.fn.owlCarousel&&e(".rtcl-store-slider").each((function(){var t=e(this),r=t.data("settings");t.addClass("owl-carousel").owlCarousel({responsive:{0:{items:2},200:{items:2},400:{items:2},600:{items:3},800:{items:r.items||4}},margin:15,rtl:!!rtcl_store_public.is_rtl,nav:!0,navText:['<i class="rtcl-icon-angle-left"></i>','<i class="rtcl-icon-angle-right"></i>']})}));var t,r=e(".store-ad-listing-wrapper");if(r.length){var a=e(".rtcl-listing-wrapper",r);(t=a.data("pagination")||{}).disable=!1,t.loading=!1,e(window).on("scroll load",(function(){!function(a){var o=r.offset().top+r.outerHeight(!0),c=e(window).scrollTop()+e(window).height();if(o<=c&&o+e(window).height()>c&&t.max_num_pages>t.current_page&&!t.loading&&!t.disable){var n={action:"rtcl_store_ad_load_more",current_page:t.current_page,max_num_pages:t.max_num_pages,found_posts:t.found_posts,posts_per_page:t.posts_per_page,store_id:rtcl_store_public.store_id};e.ajax({url:rtcl_store_public.ajaxurl,data:n,type:"POST",beforeSend:function(){t.loading=!0,e('<span class="rtcl-icon-spinner animate-spin"></span>').insertAfter(a)},success:function(e){a.next(".rtcl-icon-spinner").remove(),t.loading=!1,t.current_page=e.current_page,t.max_num_pages===e.current_page&&(t.disable=!0),e.complete&&e.html&&a.append(e.html)},error:function(e){t.loading=!1,a.next(".rtcl-icon-spinner").remove()}})}}(a)}))}}))})();