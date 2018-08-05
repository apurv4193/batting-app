$(function() {
   var thumbnail =  $('.thumbnail_cst');
   var container = $('.viewer-body');
   var close = $('.close');
   var next = $('.cst_next');
   var prev = $('.prev');
  var delimg = $('.delete_img');
  
   thumbnail.click(function(){
    var content = $(this).html();
    thumbnail.removeClass('open');
    $(this).addClass('open');
    $('body').addClass('view-open');
    container.html(content);
  });
    
  next.click(function() {
    var total = $('.thumbnail_cst').length;

     if ($('.open').index() === total- 1){
         $('.thumbnail_cst:last-child').addClass('open');
      }
    else{$('.open').removeClass('open').parent().next().find('.thumbnail_cst').toggleClass('open');}
       var content = $('.open').html();
       container.html(content);
    });
  
    prev.click(function() {
      if ($('.open').index() == 0){$('.thumbnail_cst:first-child').addClass('open');}
      else{ $('.open').removeClass('open').parent().prev().find('.thumbnail_cst').toggleClass('open');}
       var content = $('.open').html();
       container.html(content);
    });
    
   close.click(function() {$('body').removeClass('view-open'); }); 
   delimg.click(function() 
    {
        $(this).parent('.img_main').find('.thumbnail_cst img').remove()
        $(this).parent('.img_main').find('.thumbnail_cst').append('<img src="../../images/no_image_available.jpeg"/>');

    }); 
  
});
