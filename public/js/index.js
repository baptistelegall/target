$(document).ready(function() {
  var duration = 500;
  $('.scroll').click(function(event) {
    // Un clic provoque le retour en haut animé.
    event.preventDefault();
    $('html, body').animate({scrollTop: $("#presentation2").offset().top}, duration);
    return false;
  })
});
  $( "#facebook" ).hover(
    function() {
      $( "#fb1" ).css({"visibility":"hidden"});
      $("#fb2" ).css({"visibility":"visible"});
    }, function() {
      $("#fb1").css({"visibility":"visible"});
      $("#fb2" ).css({"visibility":"hidden"});
    }
  );

    $( "#instagram" ).hover(
    function() {
      $( "#insta1" ).css({"visibility":"hidden"});
      $("#insta2" ).css({"visibility":"visible"});
    }, function() {
      $("#insta1").css({"visibility":"visible"});
      $("#insta2" ).css({"visibility":"hidden"});
    }
  );
    $( "#location" ).hover(
    function() {
      $( "#location1" ).css({"visibility":"hidden"});
      $("#location2" ).css({"visibility":"visible"});
    }, function() {
      $("#location1").css({"visibility":"visible"});
      $("#location2" ).css({"visibility":"hidden"});
    }
  );
 $("#link1").click(function() {
    $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      $('#reserver').css({"border":"solid #f3cf12 3px"});



    $('html, body').animate({
        scrollTop: $("#presentation").offset().top
    }, 500);
});

    $("#link2").click(function() {
      $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      $('#reserver').css({"border":"solid #f3cf12 3px"});


    $('html, body').animate({
        scrollTop: $("#corps").offset().top
    }, 500);
});

    $("#link3").click(function() {
      $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      $('#reserver').css({"border":"solid #f3cf12 3px"});

    $('html, body').animate({
        scrollTop: $("#menuContainer").offset().top
    }, 500);
});


    $("#link4").click(function() {
      $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      $('#reserver').css({"border":"solid #f3cf12 3px"});

    $('html, body').animate({
        scrollTop: $("#footer").offset().top
    }, 500);
});


    $("#link5").click(function() {
      $('.popup').animate({width:'0'},350);
	    $('#menu2').css({"display":"flex"});
      $('#menu4').css({"display":"none"});
      $('#reserver').css({"border":"solid #f3cf12 3px"});

    $('html, body').animate({
        scrollTop: $("#footer").offset().top
    }, 500);
});

           ;(function(){
             function id(v){ return document.getElementById(v); }
             function loadbar() {
               var ovrl = id("overlay"),
                   prog = id("progress"),
                   stat = id("progstat"),
                   img = document.images,
                   c = 0,
                   tot = img.length;
               if(tot == 0) return doneLoading();

               function imgLoaded(){
                 c += 1;
                 var perc = ((100/tot*c) << 0) +"%";
                 prog.style.width = perc;
                 stat.innerHTML = perc;
                 if(c===tot) return doneLoading();
               }
               function doneLoading(){
                 setTimeout(function(){
                   ovrl.style.display = "none";
                   $('body').addClass('loaded');
                 }, 1200);
               }
               for(var i=0; i<tot; i++) {
                 var tImg     = new Image();
                 tImg.onload  = imgLoaded;
                 tImg.onerror = imgLoaded;
                 tImg.src     = img[i].src;
               }
             }
             document.addEventListener('DOMContentLoaded', loadbar, false);
           }());

      AOS.init();

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




