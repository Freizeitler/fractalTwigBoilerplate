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
