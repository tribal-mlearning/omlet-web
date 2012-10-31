$(function() {
       var el, newPoint, newPlace, offset;
       $("input[type='range']").change(function() {
         
         el = $(this);
         width = el.width()-20;
         newPoint = (el.val() - el.attr("min")) / (el.attr("max") - el.attr("min"));
         offset = 0;
         if (newPoint < 0) { newPlace = 0;  }
         else if (newPoint > 1) { newPlace = width; }
         else { newPlace = width * newPoint + offset; offset -= newPoint;}
         el
           .next("output")
           .css({
             left: newPlace,
             marginLeft: offset + "px"
           })
           .text(el.val());
           el.parent(".fieldcontain").find(".range-value").html(el.val());
           
       })
       .trigger('change');

});