
function popupFunction() {

	$('#menu2').click(function(){

	  	$('.popup').animate({width:'100%'},350).css({"opacity":"100%"});
	  	$('#menu2').css({"display":"none"});
      $('#menu4').css({"display":"flex"});


	});

	$('#menu4').click(function(){
	    $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      


	});
}


if (document.readyState === 'complete') {
  popupFunction();
} else {
  document.addEventListener('DOMContentLoaded', function() {
    popupFunction();
    if(window.location.pathname == '/'){
    	showSlides()
    }
    
  });
}




