!function(o){function r(){"membership"==o("#pricing-type").val()?(o(".form-group.allowed").slideUp(),o(".form-group.regular-listings").slideDown(),o(".form-group.membership-categories").slideDown(),o(".form-group.rtcl-membership-promotions").slideDown()):(o(".form-group.rtcl-promotions").slideUp(),o(".form-group.membership-categories").slideUp(),o(".form-group.regular-listings").slideUp(),o(".form-group.rtcl-membership-promotions").slideUp(),o(".form-group.allowed").slideDown())}o((function(){r(),o("#pricing-type").on("change",r)}))}(jQuery);