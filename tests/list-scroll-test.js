function set_list_header_stickiness() {
    var scroll_position = $(window).scrollTop();

	// var window.list_header_top = ($("#list-header").offset().top - scroll_position) < (80 + parseInt($("#list-header").css("margin-top").replace("px", "")));
	// var window.list_header_bottom = ($("#list-header").offset().top + $("#list-header").height()) > ($(window).height() + scroll_position));

	console.log(scroll_position);
	
	distance_from_top = $("#header-image").height();
	distance_from_bottom = 600;
	height = $("#list-header").height();
	
	if(scroll_position < distance_from_top) {
		
		$("#list-header").removeClass("sticky").removeClass("sticky-top").removeClass("sticky-bottom");
		// console.log("top");
		// $("#list-header").css("position", "fixed").css("bottom", "").css("top", "80px");
	} else if(scroll_position > distance_from_top && scroll_position < distance_from_bottom) {
		
		if($("#list-header").height() > $(window).height()) {
			$("#list-header").removeClass("sticky").removeClass("sticky-top").removeClass("sticky-bottom");
		} else {

			$("#list-header").addClass("sticky").addClass("sticky-top");
		}

		// $("#list-header").css("position", "fixed").css("bottom", "").css("top", "80px");
	} else if(scroll_position > distance_from_bottom && scroll_position < height) {
		
		$("#list-header").addClass("sticky").removeClass("sticky-top").addClass("sticky-bottom");
		
		
		// console.log("bottom");

		// $("#list-header").css("position", "fixed").css("bottom", "80px").css("top", "");

	}
	
}

$(document).ready(function() { 

	window.list_header_top = $("#list-header").offset().top;
	
	window.list_header_sticky = 1;
	set_list_header_stickiness();
	
	$("a#show-more-categories").click(function() {
		set_list_header_stickiness();
		$("#subcategory-index").removeClass("collapsed");
		if(($("#subcategory-index").children(".subcategory").height() * $("#subcategory-index").children(".subcategory").size() + 300) > $(window).height()) {
			$("#list-header").removeClass("sticky").removeClass("sticky-top");
	set_list_header_stickiness();
			// window.list_header_sticky = 0;
		}

		$(this).hide();
	});
	
	$(".place .place-image").hover(function() {
		$(this).parent().parent().parent().children(".place-info").children(".title").children("a").addClass("hover");
	}).mouseleave(function() {
		$(this).parent().parent().parent().children(".place-info").children(".title").children("a").removeClass("hover");
	});
	
	window.list_header_position = $("#list-header").offset().top - (80 + parseInt($("#list-header").css("margin-top").replace("px", "")));

});

$(window).scroll(set_list_header_stickiness);