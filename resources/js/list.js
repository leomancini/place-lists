function toggle_place(type, place) {
	if(type == "show") {
		place.show().removeClass("negative-search-result").addClass("positive-search-result");
	} else if(type == "hide") {
		place.hide().removeClass("positive-search-result").addClass("negative-search-result");
	}
}

function run_search(input_from_textfield) {
		if(input_from_textfield) {
			search_query = input_from_textfield;
			$(".search-separator").addClass("has-search-query");
			
			if(history.pushState) {
			    history.pushState(null, null, '#'+input_from_textfield);
			} else {
			    location.hash = '#'+input_from_textfield;
			}
			
			$(".index a, .subcategory-header a").each(function() {
				if($(this).attr("href")) {
					this_href = $(this).attr("href").split("#")[0];
					$(this).attr("href", this_href + "#" + input_from_textfield + "");
				}
			});	
			
		} else {
			search_query = "";
			$(".search-separator").removeClass("has-search-query");
			
			if(history.pushState) {
				history.pushState("", document.title, window.location.pathname + window.location.search);
			} else {
			    location.hash = '#';
			}
			
			$(".index a, .subcategory-header a").each(function() {
				if($(this).attr("href")) {
					this_href = $(this).attr("href").split("#")[0];
					$(this).attr("href", this_href);
				}
			});	
		}
		
		search_query = search_query.toUpperCase();

		neighborhoods = new Array();
		places = $("#places .place");
		subcategories = $("#places .subcategory-places");

		$.each(places, function(key, place) {
			if(search_query.indexOf(">") > -1) {
				rating_comparision_query = search_query.replace(">", "");
	
				if($(place).attr("data-rating") != "" && $(place).attr("data-rating") >= rating_comparision_query) {
					toggle_place("show", $(this));
				} else {
					toggle_place("hide", $(this));
				}
			} else if(search_query.indexOf("<") > -1) {
				rating_comparision_query = search_query.replace("<", "");
	
				if($(place).attr("data-rating") != "" && $(place).attr("data-rating") <= rating_comparision_query) {
					toggle_place("show", $(this));
				} else {
					toggle_place("hide", $(this));
				}
			} else {			
				match_set = Array();
	
				search_query_parts = search_query.split(' ');
				search_query_parts.forEach(function(term) {
					if($(place).attr("data-search-terms").toUpperCase().indexOf(term) > -1) {
						match_set.push(true);
					} else {
						match_set.push(false);
					}	
				});
	
				if(match_set.every((val, i, arr) => val == true)) {
					toggle_place("show", $(this));
				} else {
					toggle_place("hide", $(this));
				}	
			}

			neighborhoods.push($(this).attr("data-neighborhood"));
		});

		neighborhoods.forEach(function(neighborhood) {		
			neighborhood_count = $(".place.positive-search-result[data-neighborhood='"+neighborhood+"']").length;

			if(neighborhood) {
				if(neighborhood_count > 0) {
					$(".index#neighborhoods .item#"+neighborhood).show();
					$(".index#neighborhoods .item#"+neighborhood+" .count").html("&nbsp;&nbsp;"+neighborhood_count);
				} else {
					$(".index#neighborhoods .item#"+neighborhood).hide();
				}
			}
		});
	
		if($("#places .place.positive-search-result").length == 0) {
			$("#empty-search-results").show();

			// update index to reflect current search results
			$(".index").parent().parent().hide();
			
		} else {
			$("#empty-search-results").hide();
			
			// update index to reflect current search results
			$(".index").parent().parent().show();
		}

		$.each(subcategories, function(key, subcategory) {
			if($(subcategory).children(".place").hasClass("positive-search-result") == false) {
				$(subcategory).hide().removeClass("positive-search-result").addClass("negative-search-result");

				// update index to reflect current search results
				negative_subcategory_index_id = $(subcategory).attr("id").replace("subcategory-", "");
				$(".index .item#"+negative_subcategory_index_id).hide();
			} else {
				$(subcategory).show().removeClass("negative-search-result").addClass("positive-search-result");

				// update index to reflect current search results
				positive_subcategory_index_id = $(subcategory).attr("id").replace("subcategory-", "");
				positive_subcategory_index_count = $(subcategory).children(".positive-search-result").length;
				$(".index .item#"+positive_subcategory_index_id).show();
				$(".index .item#"+positive_subcategory_index_id).children("a").children(".count").html("&nbsp;&nbsp;"+positive_subcategory_index_count);
			}
			$(".subcategory-places").not(":first").css("margin-top", "50px");
			$(".subcategory-places:visible:first").css("margin-top", 0);
		});

	set_subcategory_index_state();
	set_places_margin_top();
}

function is_mobile() {
	window.mobile = 1;
}

function set_places_margin_top() {
	if(window.mobile) {
		$("#places").css("margin-top", $("#navigation").height());
	} else {
		if($(".index-container").length > 0) {
			$("#places").css("margin-top", $("#list-header").height() + 39);
		}
		$("#places").css("opacity", 1);
	}
	
	// update index to reflect current search results
	if($(".index-container").length <= 0 && $(".search-container").length <= 0) {
		$("#places").css("padding-top", "10px");
	}
}

function set_subcategory_index_state() {
	subcategory_index_max = 10;
	
	if(window.mobile) {
		$("a.index-show-more").hide();

		$(".index").each(function() {
			width = 0;
			$(this).children().each(function() {
				width = parseInt(width) + (parseInt($(this).width()) + parseInt($(this).css("margin-right").replace("px", "")));
			});
			$(this).width(width + 10);
		});
		
		search_width = 0;
		$("#search-suggestions").children(":visible").each(function() {
			search_width = parseInt(search_width) + (parseInt($(this).outerWidth()) + parseInt($(this).css("margin-right").replace("px", "")));
		});
		$("#search-suggestions").width(search_width + 10);
	} else {
		$(".index").each(function() {
			if($(this).children(":visible").length > subcategory_index_max) {
				$("a.index-show-more#"+$(this).attr("id")).show();
				$(this).addClass("collapsed");
			} else {
				$("a.index-show-more#"+$(this).attr("id")).hide();
				$(this).removeClass("collapsed");

				if($(this).children(":visible").length == 0) {
					$(this).parent().parent().hide();
				}
			}
		});
	}
}

$(document).ready(function() { 
	window.list_header_sticky = 1;
	
	if(window.location.hash) {
		url_search_query = window.location.hash.replace("#", "").replace("%20", " ");
		$("input#search").val(" ").val(url_search_query);
	}
	
	if($("input#search").val() != "") {
		run_search($("input#search").val());
	}

	$("#empty-search-results a#clear-search").click(function() {
		$("input#search").val("").keyup().focus();
	});
	
	$("#search-suggestions a.suggestion:first").hide();
	
	$("#search-suggestions a.suggestion").click(function() {
		$(this).hide();
		$("#search-suggestions a.suggestion").not($(this)).show();
		suggestion = $(this).attr("data-value");
		$("input#search").focus().val(suggestion).keyup();
	});
	
	set_subcategory_index_state();
	
	set_places_margin_top();
		
	$("a.index-show-more").click(function() {
		index = $(".index#"+$(this).attr("id"));
		index.removeClass("collapsed");
		total_indexes_height = 0;
		search_height = $(".search-container").height() + $(".places-search").height();
		
		$(".index").each(function() {
			total_indexes_height = total_indexes_height + ($(this).children(".item").height() * $(this).children(".item").size());
		});
		
		if((total_indexes_height + search_height + $("#header-image").height()) > $(window).height()) {
			$("#navigation").removeClass("sticky");
			window.list_header_sticky = 0;
		}
		
		$(this).hide();
	
		set_places_margin_top();
	});
	
	$(".place .place-image").hover(function() {
		$(this).parent().parent().parent().children(".place-info").children(".title").children("a").addClass("hover");
	}).mouseleave(function() {
		$(this).parent().parent().parent().children(".place-info").children(".title").children("a").removeClass("hover");
	});
	
	window.list_header_position = $("#navigation").offset().top - (60 + parseInt($("#navigation").css("margin-top").replace("px", "")));
	
	$("input#search").attr("autocomplete", "off").on('input change paste keyup', function(keyboard) {
		input_from_textfield = $(this).val();
		run_search(input_from_textfield);
	});
	
	$(".place a").click(function() {
		$("input#search").attr("autocomplete", "on");
	});

	$("#master").css("opacity", 1);
});

$(window).unload(function() {
	$("input#search").attr("autocomplete", "on");
}).scroll(function (event) {
    var scroll_position = $(window).scrollTop();
    if(scroll_position >= window.list_header_position && window.list_header_sticky == 1) {
		$("#navigation").addClass("sticky");
    } else {
		$("#navigation").removeClass("sticky");
    }
});