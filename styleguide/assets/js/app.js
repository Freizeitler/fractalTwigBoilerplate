
$(".js-openNavigation").on("click",function(){
  $(".pageNavigation").toggleClass("pageNavigation--active");
  $('#share-panel').prop('checked', false);
  $('#font-size-panel').prop('checked', false);
});

$(".js-fontSize-1").on("click",function(){
  $('body').removeClass('font-size-1 font-size-2 font-size-3').addClass('font-size-1');
});

$(".js-fontSize-2").on("click",function(){
  $('body').removeClass('font-size-1 font-size-2 font-size-3').addClass('font-size-2');
});

$(".js-fontSize-3").on("click",function(){
  $('body').removeClass('font-size-1 font-size-2 font-size-3').addClass('font-size-3');
});

$('#share-panel').on('click', function() {
  $('#font-size-panel').prop('checked', false);
});

$('#font-size-panel').on('click', function() {
  $('#share-panel').prop('checked', false);
});

$.fn.triggerOnScroll = function(element, offset, addClass) {
  var $this = element;
  var element_position = $this.offset().top;
  var screen_height = $(window).height();
  var activation_offset = offset;
  var activation_point = element_position - (screen_height * activation_offset);
  var max_scroll_height = $('body').height() - screen_height - 5;

  $(window).on('scroll', function() {
      var y_scroll_pos = window.pageYOffset;

      var element_in_view = y_scroll_pos > activation_point;
      var has_reached_bottom_of_page = max_scroll_height <= y_scroll_pos && !element_in_view;

      if(element_in_view || has_reached_bottom_of_page) {
        $this.addClass(addClass);
      }
  });
}

// Init
$(function() {
  if ($('.js-triggerOffsetQuote').length) {
    $(this).triggerOnScroll($('.js-triggerOffsetQuote'), 0.5, 'templateIndex__main--active');
  }
  if ($('.js-triggerOffsetPagination').length) {
    $(this).triggerOnScroll($('.js-triggerOffsetPagination'), 0.2, 'pagination--active');
  }
});
