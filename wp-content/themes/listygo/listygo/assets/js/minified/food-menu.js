!function(o){"use strict";function n(){o(".rn-recipe-wrap").find(".rn-recipe-item").each(function(t,e){o("input, textarea",e).each(function(){var e=o(this);e.attr("name",e.attr("name").replace(/listygo_food_list\[[^\]]*\]/,"listygo_food_list["+t+"]")),e.attr("name",e.attr("name").replace(/listygo_food_images\[[^\]]*\]/,"listygo_food_images["+t+"]"))}),o(".rn-ingredient-item",e).each(function(t,e){o("input, textarea",e).each(function(){var e=o(this);e.attr("name",e.attr("name").replace(/\[food_list\]\[[^\]]*\]/,"[food_list]["+t+"]"))})})})}function r(){return'<div class="rn-ingredient-item"><div class="rn-ingredient-fields"><input name="listygo_food_list[][food_list][][title]" type="text" placeholder="'+ListygoObj.title+'" class="form-control"><input name="listygo_food_list[][food_list][][foodprice]" type="text" placeholder="'+ListygoObj.price+'" class="form-control"><textarea name="listygo_food_list[][food_list][][description]" placeholder="'+ListygoObj.description+'" class="form-control"></textarea><div class="food-image-wrap"><div class="floor-input-wrapper"><input name="listygo_food_images[][food_list][]" class="listygo-food-image" type="file"/></div></div></div><span class="rn-remove"><i class="fa fa-times" aria-hidden="true"></i></span></div>'}o(document).on("click",".food-menu-wrapper .add-recipe",function(e){e.preventDefault();var e=ListygoObj.gtitle,t=ListygoObj.addfood,i=o(this),e='<div class="rn-recipe-item"><span class="rn-remove"><i class="fa fa-times" aria-hidden="true"></i></span><div class="rn-recipe-title"><input type="text" name="listygo_food_list[][gtitle]" class="form-control" placeholder="'+e+'"></div><div class="rn-ingredient-wrap">'+r()+'</div><div class="rn-ingredient-actions"><a href="javascript:void()" class="btn-upload add-ingredient btn-sm btn-primary">'+t+"</a></div></div>";i.closest(".food-menu-wrapper").find(".rn-recipe-wrap").append(e),n()}),o(document).on("click",".rn-recipe-item > .rn-remove",function(e){e.preventDefault();e=o(this);0<=e.closest(".food-menu-wrapper").find(".rn-recipe-item").length?e.closest(".rn-recipe-item").slideUp("slow",function(){o(this).remove(),n()}):alert("You are not permited to remove all recipe")}),o(document).on("click",".rn-recipe-item .add-ingredient",function(e){e.preventDefault();var e=o(this),t=r();e.closest(".rn-recipe-item").find(".rn-ingredient-wrap").append(t),n()}),o(document).on("click",".rn-ingredient-item .rn-remove",function(e){e.preventDefault();e=o(this);0<=e.closest(".rn-ingredient-wrap").find(".rn-ingredient-item").length?e.closest(".rn-ingredient-item").slideUp("slow",function(){o(this).remove(),n()}):alert("You are not permited to remove all ingradient")}),o(".remove-food-image a").on("click",function(e){e.preventDefault();var e=o(this).data("attachment_id"),t=o(this).data("post_id");let i=o(this).parents(".food-image"),n=o(".food-input-wrapper");confirm("Are you want to delete this attachment?")&&o.ajax({type:"post",url:rtcl.ajaxurl,data:{action:"delete_food_attachment",attachment_id:e,post_id:t},success:function(e){"success"===e&&i.fadeOut(function(){i.remove(),n.toggleClass("d-none")})}})})}(jQuery);