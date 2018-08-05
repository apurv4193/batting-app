$(document).ready(function() {
    $('.serach-drop-box .drop-arrow').click(function() {
    	
    	$(".overlay-check").remove();
        $('.drop_view').slideToggle();
        $('.search-area').append('<div class="overlay-check"></div>');
        $(this).toggleClass("active");
    });
    $(document).on('click', '.overlay-check',  function() {
        $(this).remove();
        $('.drop_view').slideUp();
    });
    $('.select-serch-item').click(function(){
    	$('.drop_view').slideUp();
    });
    
});