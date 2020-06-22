function toggle_place(type, place) {
	if(type == "show") {
		place.style.display = "inline-block";
		place.classList.remove("negative-search-result");
		place.classList.add("positive-search-result");
	} else if(type == "hide") {
		place.style.display = "none";
		place.classList.add("negative-search-result");
		place.classList.remove("positive-search-result");
	}
}

function update_links_with_search_query(add_or_remove, input_from_textfield) {
	var links = document.querySelectorAll(".index a, .subcategory-header a");
	for(var i = 0; i < links.length; i++) {
		if(links[i].getAttribute("href")) {
			this_href = links[i].getAttribute("href").split("#")[0];
			if(add_or_remove == "add") {
				links[i].setAttribute("href", this_href + "#" + input_from_textfield + "");
			} else {
				links[i].setAttribute("href", this_href);
			}
		}
	}
}

function search_update_styling(input_from_textfield) {
	if(input_from_textfield) {
		search_query = input_from_textfield;
		if(document.getElementById("search-separator")) {
			document.getElementById("search-separator").classList.add("has-search-query");
		}
		
		if(history.pushState) {
		    history.pushState(null, null, '#'+input_from_textfield);
		} else {
		    location.hash = '#'+input_from_textfield;
		}
		
		update_links_with_search_query("add", input_from_textfield);

		document.title = window.document_title_at_load + " / " + search_query;
	} else {
		search_query = "";
		if(document.getElementById("search-separator")) {
			document.getElementById("search-separator").classList.remove("has-search-query");
		}
		
		if(history.pushState) {
			history.pushState("", document.title, window.location.pathname + window.location.search);
		} else {
		    location.hash = '#';
		}
		
		update_links_with_search_query("remove");

		document.title = window.document_title_at_load;
	}
}

function search_filter_list(input_from_textfield) {
	search_query = search_query.toUpperCase().replace(/'/g, "\\\'");
	
	var places = document.getElementsByClassName("place");
	for(var i = 0; i < places.length; i++) {
		if(search_query.indexOf(">") > -1) {
			rating_comparision_query = search_query.replace(">", "");

			if(places[i].getAttribute("data-rating") != "" && places[i].getAttribute("data-rating") >= rating_comparision_query) {
				toggle_place("show", places[i]);
			} else {
				toggle_place("hide", places[i]);
			}
		} else if(search_query.indexOf("<") > -1) {
			rating_comparision_query = search_query.replace("<", "");

			if(places[i].getAttribute("data-rating") != "" && places[i].getAttribute("data-rating") <= rating_comparision_query) {
				toggle_place("show", places[i]);
			} else {
				toggle_place("hide", places[i]);
			}
		} else {		
			match_set = Array();

			search_query_parts = search_query.split(' ');
			search_query_parts.forEach(function(term) {
				if(places[i].getAttribute("data-search-terms").toUpperCase().indexOf(term) > -1) {
					match_set.push(true);
				} else {
					match_set.push(false);
				}	
			});

			if(match_set.every((val, i, arr) => val == true)) {
				toggle_place("show", places[i]);
			} else {
				toggle_place("hide", places[i]);
			}
		}	
	}

	if(document.getElementById("neighborhoods")) {
		var neighborhoods = document.getElementById("neighborhoods").children;
		for(var i = 0; i < neighborhoods.length; i++) {	
		
			if(neighborhoods[i].getAttribute("id")) {			
				neighborhood_label = neighborhoods[i].getAttribute("id");
				neighborhood_count = document.querySelectorAll(".place.positive-search-result[data-neighborhood='"+neighborhood_label+"']").length;

				if(neighborhood_label) {
					if(neighborhood_count > 0 && document.querySelector(".index#neighborhoods .item#"+neighborhood_label+" .count")) {
						document.querySelector(".index#neighborhoods .item#"+neighborhood_label).style.display = "block";
						document.querySelector(".index#neighborhoods .item#"+neighborhood_label+" .count").innerHTML = "&nbsp;&nbsp;"+neighborhood_count;
					} else {
						document.querySelector(".index#neighborhoods .item#"+neighborhood_label).style.display = "none";
					}
				}
			}
		}
	}
	
	var subcategories = document.getElementsByClassName("subcategory-places");
	for(var i = 0; i < subcategories.length; i++) {
		if(subcategories[i].getAttribute("id")) {
			subcategory_label = subcategories[i].getAttribute("id").replace("subcategory-", "");
			subcategory_count = document.querySelectorAll(".place.positive-search-result[data-subcategory='"+subcategory_label+"']").length;
		
			if(subcategory_count == 0) {
				subcategories[i].style.display = "none";
				subcategories[i].classList.add("negative-search-result");
				subcategories[i].classList.remove("positive-search-result");

				// update index to reflect current search results
				if(subcategories[i].getAttribute("id")) {
					if(document.querySelector(".index .item#"+subcategory_label)) {
						document.querySelector(".index .item#"+subcategory_label).style.display = "none";
					}
				}
			} else {
				subcategories[i].style.display = "block";
				subcategories[i].classList.add("positive-search-result");
				subcategories[i].classList.remove("negative-search-result");
						
				if(subcategories[i].getAttribute("id")) {
					// update index to reflect current search results
					if(document.querySelector(".index .item#"+subcategory_label) && document.querySelector(".index .item#"+subcategory_label+" a .count")) {
						document.querySelector(".index .item#"+subcategory_label).style.display = "block";
						document.querySelector(".index .item#"+subcategory_label+" a .count").innerHTML = "&nbsp;&nbsp;"+subcategory_count;
					}
				}
			}
		}
	}
	
	var indexes = document.getElementsByClassName("index-container");
	for(var i = 0; i < indexes.length; i++) {
		indexes[i].style.display = "none";
	}
		
	if(document.getElementsByClassName("place positive-search-result").length == 0) {
		document.getElementById("empty-search-results").style.display = "inline-block";

		// update index to reflect current search results
		for(var i = 0; i < indexes.length; i++) {
			indexes[i].style.display = "none";
		}
	} else {
		document.getElementById("empty-search-results").style.display = "none";

		// update index to reflect current search results
		for(var i = 0; i < indexes.length; i++) {
			indexes[i].style.display = "block";
		}
	}

	set_subcategory_index_state();
	set_places_margin_top();
}

function is_mobile() {
	window.mobile = 1;
}

function set_places_margin_top() {
	// if(window.mobile) {
	// 	if($(".index-container").length > 0) {
	// 		$("#places").css("margin-top", $("#navigation").height());
	// 	} else {
	// 		$("#places").css("margin-top", $("#navigation").height() + 39);
	// 	}
		
	// } else {
		if($(".index-container").length > 0) {
			$("#places").css("margin-top", $("#list-header").height() + 39);
		}
		$("#places").css("opacity", 1);
	// }
	
	// update index to reflect current search results
	if($(".index-container").length <= 0 && $(".search-container").length <= 0) {
		$("#places").css("padding-top", "10px");
	}
}

function set_subcategory_index_state() {
	subcategory_index_max = 10;
	
	$("a.index-show-more").hide();
	
	// if(window.mobile) {
	// 	$("a.index-show-more").hide();

	// 	$(".index").each(function() {
	// 		width = 0;
	// 		$(this).children().each(function() {
	// 			width = parseInt(width) + (parseInt($(this).width()) + parseInt($(this).css("margin-right").replace("px", "")));
	// 		});
	// 		$(this).width(width + 10);
			
	// 		if($(this).children(":visible").length == 0) {
	// 			$(this).parent().parent().hide();
	// 		}
	// 	});
		
	// 	search_width = 0;
	// 	$("#search-suggestions").children(":visible").each(function() {
	// 		search_width = parseInt(search_width) + (parseInt($(this).outerWidth()) + parseInt($(this).css("margin-right").replace("px", "")));
	// 	});
	// 	$("#search-suggestions").width(search_width + 10);
	// } else {	
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
	// }
	
	if($(".subcategory-places:visible").length == 1) {
		$(".subcategory-header-replacement").parent(".subcategory-places.positive-search-result").children(".place:first").css("margin-top", 0);
		// if(window.mobile) { $(".subcategory-header-replacement").css("margin-top", "-20px"); }
	}

}

$(document).ready(function() {
	window.list_header_sticky = 1;
	window.document_title_at_load = document.title;
	$("input#search").focus();
	
	if(window.location.hash) {
		url_search_query = window.location.hash.replace("#", "").replace("%20", " ");
		$("input#search").val(" ").val(decodeURIComponent(url_search_query));
	}
	
	if($("input#search").val() != "") {
		search_update_styling($("input#search").val());
		search_filter_list($("input#search").val());
	}

	$("#empty-search-results a#clear-search").click(function() {
		if(window.location.hash == "") {
			var list_url = $("#list").data("list-url");
			var root_path = window.location.href.split(list_url);
			window.location.href = root_path["0"] + list_url;
		}
		$("input#search").val("").keyup().focus();
	});
	
	$(".rating a").click(function() {
		window.location.hash = "#"+$(this).data("rating");
		location.reload();
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
		search_update_styling(input_from_textfield);
		setTimeout(function() {
			search_filter_list(input_from_textfield);
		}, 250);
	});
	
	$(".place a").click(function() {
		$("input#search").attr("autocomplete", "on");
	});

	$("input#search").autosizeInput(40);
	
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