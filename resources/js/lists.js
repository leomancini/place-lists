function run_search(search_query, keyboard) {
	search_query = search_query.toUpperCase();
	lists = $("#lists").children(".list");
	section_headers = $("#lists").children(".section-header");

	$.each(lists, function(key, list) {
       	$("#lists").children(".list.positive-search-result").removeClass("selected");
		if($(list).attr("data-search-terms").toUpperCase().indexOf(search_query) > -1) {
			$(this).show().removeClass("negative-search-result").addClass("positive-search-result");
		} else {
			$(this).hide().removeClass("positive-search-result").addClass("negative-search-result");
		}
	});
	
	if($('.positive-search-result').length >= 1) {
		$("#empty-search-results").hide();
		is_keyboard_navigation_enabled = 1;
	} else {
		$("#empty-search-results").show();
		is_keyboard_navigation_enabled = 0;
	}

	if(keyboard != "onload") {
	    if(keyboard.keyCode == 13) {
			// keyboard enter, go to href of selected item
			window.selected_search_result.addClass("selected");
			window.location.href = "./" + window.selected_search_result.children("a").attr("href");
			$("input#search").attr("autocomplete", "on");
	    } else if(keyboard.keyCode == 40 && is_keyboard_navigation_enabled) {
			// keyboard down
			window.selected_search_result.addClass("selected");
			if(window.selected_search_result.html() == $("#lists .list.positive-search-result:last").html()) {
				// end of list, highlight first item (select last of previous items)
				window.selected_search_result = $("#lists .list.selected").prevAll('.positive-search-result:last');
				$("#lists .list.selected").removeClass("selected").prevAll('.positive-search-result:last').addClass("selected");
			} else {
				// not end of list, highlight next item (select first of next items)
				window.selected_search_result = $("#lists .list.selected").nextAll('.positive-search-result:first');
				$("#lists .list.selected").removeClass("selected").nextAll('.positive-search-result:first').addClass("selected");
			}
	    } else if(keyboard.keyCode == 38 && is_keyboard_navigation_enabled) {
			// keyboard up		
			window.selected_search_result.addClass("selected");
			if(window.selected_search_result.html() == $("#lists .list.positive-search-result:first").html()) {
				// beginning of list, highlight last item (select last of next items)
				window.selected_search_result = $("#lists .list.selected").nextAll('.positive-search-result:last');
				$("#lists .list.selected").removeClass("selected").nextAll('.positive-search-result:last').addClass("selected");
			} else {
				// not beginning of list, highlight next item (select first of previous items)
				window.selected_search_result = $("#lists .list.selected").prevAll('.positive-search-result:first');
				$("#lists .list.selected").removeClass("selected").prevAll('.positive-search-result:first').addClass("selected");
			}
	    } else {
			// select first result by default
	        first_search_result = $("#lists").children(".list.positive-search-result:first");
			first_search_result.addClass("selected");
			window.selected_search_result = first_search_result;
	    }
	}
	

	$.each(section_headers, function() {
		if($(".list.positive-search-result[data-section='"+$(this).attr("id")+"']").length > 0) {
			$(this).show();
		} else {
			$(this).hide();
		}		
	});
}

$(document).ready(function() { 
	if($("input#search").val() != "") {
		run_search($("input#search").val(), "onload");
	}
	
	$("input#search").focus().attr("autocomplete", "off").on('input change paste keydown', function(keyboard) {
		search_query = $(this).val();
		run_search(search_query, keyboard);
	}).blur(function() {
		$(".list").removeClass("selected");
	});
	
	$(".list a").click(function() {
		$("input#search").attr("autocomplete", "on");
	}).hover(function() {
		window.selected_search_result = "";
		$(".list a").not($(this)).parent().removeClass("selected");
	});
	
	$("#empty-search-results a#clear-search").click(function() {
		$("input#search").val("").keydown().focus();
	});

	$("#master").css("opacity", 1);
});

$(window).unload(function() {
	$("input#search").attr("autocomplete", "on");
});