$(document).ready(function(){
	$('input').focus(function(){
		$(this).css("background-color","#abeeed");
	});
	$('input').blur(function(){
		$(this).css("background-color","#ffffff")
	});
});
