<?php
	function generate_category_urls($this_categories_names) {
		global $db;
		
		$this_categories_urls = Array();
			
		if($this_categories_names[0]) { $this_categories_urls[0] = $this_categories_names[0]; }
		if($this_categories_names[1]) { $this_categories_urls[1] = $this_categories_names[0]."/".$this_categories_names[1]; }
		if($this_categories_names[2]) { $this_categories_urls[2] = $this_categories_names[0]."/".$this_categories_names[1]."/".$this_categories_names[2]; }
		if($this_categories_names[3]) { $this_categories_urls[3] = $this_categories_names[0]."/".$this_categories_names[1]."/".$this_categories_names[2]."/".$this_categories_names[3]; }
		if($this_categories_names[4]) { $this_categories_urls[4] = $this_categories_names[0]."/".$this_categories_names[1]."/".$this_categories_names[2]."/".$this_categories_names[3]."/".$this_categories_names[4]; }
		
		return $this_categories_urls;
	}

	function render_category_breadcrumbs($url_category_names, $this_categories_urls, $number_of_places) {
		global $root;
		global $list_name_url;
		global $url_neighborhood;
		global $url_neighborhood_terms;
		global $list_name_url_without_neighborhood;
		
		echo "<div id='category-breadcrumbs'>";
		
			if($url_neighborhood) {
				$url_neighborhood_term_count = 0;
				foreach($url_neighborhood_terms as $url_neighborhood_term) {
					$url_neighborhood_term_count++;
					if($url_neighborhood_term_count > 1) { echo " / " ;}
				
					$neighborhood_breadcrumb_counter = 0;
					while($neighborhood_breadcrumb_counter-1 < $url_neighborhood_term_count-1) {
						$neighborhood_breadcrumb_counter++;
						$neighborhood_breadcrumb_urls[$url_neighborhood_term_count] .= ":".$url_neighborhood_terms[$neighborhood_breadcrumb_counter-1];
					}
				
					$neighborhood_breadcrumb_url = array_values(array_slice($neighborhood_breadcrumb_urls, -1));
					$neighborhood_breadcrumb_url = $neighborhood_breadcrumb_url[0];
					echo "<a href='".$root.$list_name_url_without_neighborhood.$neighborhood_breadcrumb_url."' id='search-query'>".convert("search-query", "display", $url_neighborhood_term)."</a>";
				}
				if($url_category_names) { echo " / "; }
			}
		
			foreach($url_category_names as $category_key => $category_name) {
				echo "<a href='".$root.$list_name_url."/".$this_categories_urls[$category_key]."'>".convert("category", "display", $category_name)."</a>";
				if(($category_key + 1) != count($url_category_names)) { echo " / "; }
			}
		
		// if list has more than 1 place, render search and search suggestions
		if($number_of_places > 1) {
			render_search($url_neighborhood, $url_category_names);
			if(!is_mobile()) {
				$search_suggestions = generate_search_suggestions($popular);
				render_search_suggestions($search_suggestions);
			}
		}

		echo "</div>";
		
	}
	
	function render_sub_category_index($places_by_sub_category, $url_categories) {
		global $root;
		global $list_name_url;
		global $url_neighborhood;
		
		$subcategories = Array();

		foreach($places_by_sub_category as $sub_category => $places_in_sub_category) {
			if($sub_category != "") {
				$sort = str_pad(count($places_in_sub_category), 20, "0", STR_PAD_LEFT)."-".convert("category", "url", $sub_category);
				$subcategories[$sort] = Array(
					"name" => $sub_category,
					"name_url" => convert("category", "url", $sub_category),
					"name_display" => convert("category", "display", $sub_category),
					"count" => count($places_in_sub_category)
				);
			}
		}

		krsort($subcategories);
	
		if(count($subcategories) > 1) {
			echo "<div class='index-container'>";
			echo "<div class='index-header'>categories</div>";
			echo "<div class='index-wrapper'>";
			echo "<div class='index' id='subcategories' data-count='".count($subcategories)."'>";
			foreach($subcategories as $subcategory => $subcategory_info) {
				echo "<span class='item' id='".$subcategory_info["name_url"]."'>";
				echo "<a href='".$root.$list_name_url."/";
				if($url_categories) { echo $url_categories."/"; }
				echo $subcategory_info["name_url"]."'>";
				echo "<span class='label'>";
				echo $subcategory_info["name_display"];	
				echo "</span>";
				if(!is_mobile()) {
					echo "<span class='count'>&nbsp;&nbsp;";
					echo "".$subcategory_info["count"];
					echo "</span>";
				}
				echo "</a>";
				echo "</span>";
			}
			echo "</div>";
			echo "</div>";
			echo "</div>";
		
			echo "<a class='index-show-more' id='subcategories'>Show more...</a>";
		}
	}
	
	function render_neighborhood_index($neighborhoods, $url_categories) {
		global $root;
		global $list_name_url;
		global $url_neighborhood_terms;
		
		$url_neighborhood_terms_display = Array();
		foreach($url_neighborhood_terms as $url_neighborhood_term) {
			$url_neighborhood_terms_display[] = convert("search-query", "display", $url_neighborhood_term);
		}
		
		if($url_neighborhood_terms && count(array_intersect(array_keys($neighborhoods), $url_neighborhood_terms_display)) > 0) {
			$show_neighborhoods = false;
		} else {
			$show_neighborhoods = true;
		}

		foreach($neighborhoods as $neighborhood_label => $neighborhood_count) {
			$sort = str_pad($neighborhood_count, 20, "0", STR_PAD_LEFT)."%COUNT%";
			$neighborhoods_sorted[$sort.$neighborhood_label] = Array("label" => $neighborhood_label, "count" => $neighborhood_count);
		}
		
		krsort($neighborhoods_sorted);
		
		if(count($neighborhoods_sorted) > 1 && $show_neighborhoods) {
			echo "<div class='index-container'>";
			echo "<div class='index-header'>neighborhoods</div>";
			echo "<div class='index-wrapper'>";
			echo "<div class='index' id='neighborhoods' data-count='".count($neighborhoods_sorted)."'>";
			foreach($neighborhoods_sorted as $neighborhood_label_and_count => $neighborhood_info) {
				if($neighborhood_info["label"] != "") {
					echo "<span class='item' id='".convert("neighborhood", "url", $neighborhood_info["label"])."'>";
					echo "<a href='".$root.$list_name_url.":";
					echo convert("neighborhood", "url", $neighborhood_info["label"]);
					if($url_categories) { echo "/".$url_categories ; }
					echo "'>";
					echo "<span class='label'>";
					echo $neighborhood_info["label"];	
					echo "</span>";
					if(!is_mobile()) {
						echo "<span class='count'>&nbsp;&nbsp;";
						echo "".$neighborhood_info["count"];
						echo "</span>";
					}
					echo "</a>";
					echo "</span>";
				}
			}
			echo "</div>";
			echo "</div>";
			echo "</div>";
		
			echo "<a class='index-show-more' id='neighborhoods'>Show more...</a>";
		}
			
	}
	
	function render_search($url_neighborhood, $url_category_names) {
		if($url_category_names || $url_neighborhood) {
			// if($url_neighborhood) { $saved_search_class = " has-search-query"; }
			echo "<span id='search-separator' class='".$saved_search_class."'> / </span>";
		}
		echo '<input type="text" id="search" class="places-search" placeholder="Search..." autocapitalize="none" autocorrect="off">';		
	}
	
	function render_search_suggestions($suggestions) {
		$suggestions = array_filter($suggestions, function($x) { return !empty($x); });
		if(count($suggestions) > 2) {
			echo "<div class='search-container'>";
			echo "<div class='search-wrapper'>";
			echo "<div id='search-suggestions'>";
			foreach($suggestions as $suggestion) {
				echo "<a class='suggestion' data-value='".$suggestion."'>".convert("search-suggestion", "display", $suggestion)."</a>";
			}
			echo "</div>";
			echo "</div>";
			echo "</div>";
		}
	}
	
	function generate_search_suggestions($popular) {
		// array value for places on street count starts at 0, so add 1 to get correct value
		foreach($popular["streets"] as $street_name => $places_on_street_count) {
			$popular["streets"][$street_name] = $places_on_street_count + 1;
		}
		
		// sort popular data by number of places in each popular type
		foreach($popular as $popular_label => $popular_data) {
			arsort($popular[$popular_label]);
		}
		
		// setup search suggestions
		$search_suggestions = Array();
		
		$top_x = 50;
		$number_of_random_popular_streets = 4;
		$number_of_random_popular_neighborhoods = 5;
		$number_of_random_popular_ratings = 5;
		
		// add popular streets to search suggestions
		$top_x_popular_streets = round((round(count($popular["streets"])) / $top_x) * 10);
		$random_amongst_top_x_percentile_streets = rand(0, $top_x_popular_streets);
		
		while($random_popular_streets_count < $number_of_random_popular_streets) {
			$popular_streets = array_keys($popular["streets"]);
			$search_suggestions[] = $popular_streets[$random_amongst_top_x_percentile_streets+$random_popular_streets_count];
			$random_popular_streets_count++;	
		}
		
		// add popular ratings to search suggestions
		$top_x_popular_ratings = round((round(count($popular["ratings"])) / $top_x) * 10);
		$random_amongst_top_x_percentile_ratings = rand(0, $top_x_popular_ratings);
		
		while($random_popular_ratings_count < $number_of_random_popular_ratings) {
			$popular_ratings = array_keys($popular["ratings"]);
			if($popular_ratings[$random_amongst_top_x_percentile_ratings+$random_popular_ratings_count] != "") {
				$search_suggestions[] = ">".number_format($popular_ratings[$random_amongst_top_x_percentile_ratings+$random_popular_ratings_count], 1);
			}
			$random_popular_ratings_count++;	
		}
	
		/*
		// add random popular neighborhoods to search suggestions
		$top_x_popular_neighborhoods = round((round(count($popular["neighborhoods"])) / $top_x) * 10);
		$random_amongst_top_x_percentile_neighborhoods = rand(0, $top_x_popular_neighborhoods);
		while($random_popular_neighborhoods_count < $number_of_random_popular_neighborhoods) {
			$popular_neighborhoods = array_keys($popular["neighborhoods"]);
			$search_suggestions[] = $popular_neighborhoods[$random_amongst_top_x_percentile_neighborhoods+$random_popular_neighborhoods_count];	
			$random_popular_neighborhoods_count++;
		}
		*/
		
		// randomize order of search suggestions
		$search_suggestions = array_unique($search_suggestions);
		shuffle($search_suggestions);
		
		return $search_suggestions;
	}
	
	function render_subcategory_header($sub_category_label, $url_categories) {
		global $root;
		global $list_name_url;
		
		if($sub_category_label) {
			echo "<span class='subcategory-header'>";
			echo "<a href='".$root.$list_name_url."/";
			if($url_categories) { echo $url_categories . "/"; }
			echo convert("category", "url", $sub_category_label)."'>";
			echo convert("category", "display", $sub_category_label);
			echo "</a>";
			echo "</span>";
		} else {
			echo "<span class='subcategory-header-replacement'></span>";
		}
	}
	
	function render_place_info($place_info, $search_terms_string, $url_categories, $url_neighborhood_terms, $sub_category_label) {
		echo "<div class='place'";
		echo 'data-search-terms="'.strtolower(strip_accents($search_terms_string)).'"';
		echo 'data-subcategory="'.strtolower(strip_accents($sub_category_label)).'"';
		echo 'data-neighborhood="'.strtolower(strip_accents(convert("neighborhood", "url", $place_info["neighborhood"]))).'"';
		echo "data-rating='".number_format($place_info["rating"], 1)."'>";

			$place_url = "https://foursquare.com/v/".$place_info["foursquare_id"];
			echo "<a href='".$place_url."' target='_blank'>";
				echo "<span class='place-image-wrapper'>";
				if($place_info["photo_url_prefix"] != "" && $place_info["photo_url_suffix"] != "") {
					$size = 100;
					echo "<img src='".$place_info["photo_url_prefix"].$size."x".$size.$place_info["photo_url_suffix"]."' class='place-image'>";
				}
				echo "</span>";
			echo "</a>";
	
			echo "<div class='place-info'>";
	
				// render place title
				echo "<span class='title'>";
					echo "<a href='".$place_url."' target='_blank'>";
						echo $place_info["name"];
					echo "</a>";
				echo "</span>";
	
				// render place description items (rating, category, address)
				echo "<span class='description'>";
					render_place_description_items($place_info, $url_categories, $url_neighborhood_terms);
				echo "</span>";
	
			echo "</div>"; // close .place-info
		
		echo "</div>"; // close .place
	}
	
	function generate_search_terms($this_category_key, $next_sub_category, $place_info) {
		global $db;
		
		// define eligible search terms
		$search_terms = Array(
			$next_sub_category,
			convert("category", "display", $place_info["categories"][$this_category_key]["name"]),
			$place_info["name"],
			$place_info["address"],
			convert("neighborhood", "url", $place_info["neighborhood"]),
			number_format($place_info["rating"], 1)
		);
		
		// mysql clean search terms
		foreach($search_terms as $key => $search_term) {
			$search_terms[$key] = mysqli_real_escape_string($db, $search_term);
		}
		
		// combine search terms into string
		$search_terms_string[$place["id"]] = implode(" ", $search_terms);
		
		return $search_terms_string[$place["id"]];
	}
	
	function render_place_description_items($place_info, $url_categories, $url_neighborhood_terms) {
		global $root;
		global $list_name_url;
		
		$description_items = Array();
		
		// generate html for place rating
		if($place_info["rating"] != "") {
			$rating_display = number_format($place_info["rating"], 1);

			if($url_categories) { $url_categories_string = "/".$url_categories; }
	
			if(!in_array($rating_display, $url_neighborhood_terms)) {
				$description_items["rating"] = "
					<span class='rating-circle' style='background-color: #".$place_info["rating_color"].";'></span>
					<span class='rating'>
					<a href='".$root.$list_name_url.$url_categories_string."#".$rating_display."' style='color: #".$place_info["rating_color"].";' data-rating=".$rating_display.">".$rating_display."</a>
					</span>";
			} else {
				$description_items["rating"] = "
					<span class='rating-circle' style='background-color: #".$place_info["rating_color"].";'></span>
					<span class='rating' style='color: #".$place_info["rating_color"].";'>".$rating_display."</span>";
			}
		}

		// generate html for place category
		if($sub_category_label != $this_category["name"] && $this_sub_category != $this_category["name"]) {
			$description_items["category"] = "
				<a class='category' href='".$root.$list_name_url."/".$this_category["url"]."'>".
					convert("category", "display", $this_category["name"]).
				"</a>";
		}
	
		// generate html for place address
		if($place_info["address"] != "") {
			$google_maps_url =
				"//google.com/maps/search/?api=1&query=".
					urlencode($place_info["name"]
					." ".
					$place_info["address"]
					." ".
					$place_info["city"]
					." ".
					$place_info["country_code"]);
			
				if(!is_mobile()) { $target = " target='_blank'"; }
			$description_items["address"] = "
				<a class='address' href='".$google_maps_url."'".$target.">".
					$place_info["address"].
				"</a>";
		}

		// add middot in between description items
		$description_items_count = 0;
		foreach($description_items as $description_item) {
			$description_items_count++;
			echo $description_item;
			if(count($description_items) != $description_items_count) { echo " &middot; "; }
		}
	}
	
	function render_list($query) {
		global $root;
		global $list;
		global $list_name_url;
		global $list_name_url_without_neighborhood;
		global $url_neighborhood;
		global $url_neighborhood_terms;
		global $db;
	
		$category_info = get_all_category_info();
		$url_category_names = Array();
		if(isset($_GET['category1'])) { $url_category_names[0] = urldecode($_GET['category1']); }
		if(isset($_GET['category2'])) { $url_category_names[1] = urldecode($_GET['category2']); }
		if(isset($_GET['category3'])) { $url_category_names[2] = urldecode($_GET['category3']); }
		if(isset($_GET['category4'])) { $url_category_names[3] = urldecode($_GET['category4']); }
		if(isset($_GET['category5'])) { $url_category_names[4] = urldecode($_GET['category5']); }
		$url_categories = implode("/", $url_category_names);
	
		// get premium data for each place
		$premium_places_info_query = mysqli_query($db, "SELECT * FROM places_premium_data ".$query);
		while($premium_place_info = mysqli_fetch_array($premium_places_info_query)) {
			$premium_place_info_set[$premium_place_info["foursquare_id"]] = $premium_place_info;
		}
		
		// get neighborhood data for each place
		$neighborhood_query = mysqli_query($db, "SELECT * FROM neighborhoods ".$query);
		while($neighborhood_info = mysqli_fetch_array($neighborhood_query)) {
			$neighborhood_info_set[$neighborhood_info["foursquare_id"]] = $neighborhood_info;
		}
		
		$places_info_query = mysqli_query($db, "SELECT * FROM places ".$query);
		while($place = mysqli_fetch_array($places_info_query)) {
			$this_categories_names = Array();
			$this_categories_id = Array();
		
			$matching_type = "short_name";
			
			if(isset($category_info[$place["category_id"]][$matching_type])) {
				$this_categories_names[0] = convert("category", "url", $category_info[$place["category_id"]][$matching_type]);
			}
		
			if(isset($category_info[$place["category_id"]]["parent_category_".$matching_type])) {
				$this_categories_names[1] = convert("category", "url", $category_info[$place["category_id"]]["parent_category_".$matching_type]);
			}
		
			if(isset($category_info[$place["category_id"]]["grandparent_category_".$matching_type])) {
				$this_categories_names[2] = convert("category", "url", $category_info[$place["category_id"]]["grandparent_category_".$matching_type]);
			}
		
			if(isset($category_info[$place["category_id"]]["greatgrandparent_category_".$matching_type])) {
				$this_categories_names[3] = convert("category", "url", $category_info[$place["category_id"]]["greatgrandparent_category_".$matching_type]);
			}
		
			if(isset($category_info[$place["category_id"]]["greatgreatgrandparent_category_".$matching_type])) {
				$this_categories_names[4] = convert("category", "url", $category_info[$place["category_id"]]["greatgreatgrandparent_category_".$matching_type]);
			}
		
			$this_categories_names = array_reverse($this_categories_names);
		
			if(count($url_category_names) == 0) {
				$match = 1;
				$match_level = 0;
			}
		
			if(count($url_category_names) == 1
				&& $this_categories_names[0] == $url_category_names[0]) {
				$match = 1;
				$match_level = 1;
			}
		
			if(count($url_category_names) == 2
				&& $this_categories_names[0] == $url_category_names[0]
				&& $this_categories_names[1] == $url_category_names[1]) {
				$match = 1;
				$match_level = 2;
			}
		
			if(count($url_category_names) == 3
				&& $this_categories_names[0] == $url_category_names[0]
				&& $this_categories_names[1] == $url_category_names[1]
				&& $this_categories_names[2] == $url_category_names[2]) {
				$match = 1;
				$match_level = 3;
			}
		
			if(count($url_category_names) == 4
				&& $this_categories_names[0] == $url_category_names[0]
				&& $this_categories_names[1] == $url_category_names[1]
				&& $this_categories_names[2] == $url_category_names[2]
				&& $this_categories_names[3] == $url_category_names[3]) {
				$match = 1;
				$match_level = 4;
			}
		
			if(count($url_category_names) == 5
				&& $this_categories_names[0] == $url_category_names[0]
				&& $this_categories_names[1] == $url_category_names[1]
				&& $this_categories_names[2] == $url_category_names[2]
				&& $this_categories_names[3] == $url_category_names[3]
				&& $this_categories_names[4] == $url_category_names[4]) {
				$match = 1;
				$match_level = 5;
			}
		
			if($match) {
				$places_info[$place["id"]] = $place;
				
				// add premium data to regular places_info array
				foreach(premium_places_data_fields(null) as $premium_data_field) {
					$places_info[$place["id"]][$premium_data_field] = $premium_place_info_set[$place["foursquare_id"]][$premium_data_field];	
				}
				
				// add neighborhood data to regular places_info array
				$places_info[$place["id"]]["neighborhood"] = $neighborhood_info_set[$place["foursquare_id"]]["neighborhood_long_name"];
			
				// form category urls
				$this_categories_urls = generate_category_urls($this_categories_names);
				
				// set category variables
				$direct_parent_category = $this_categories_urls[$match-2];			
				$this_sub_category = $this_categories_names[$match_level-1];
				$next_sub_category = $this_categories_names[$match_level];

				// generate category info for each place
				foreach($this_categories_names as $this_category_key => $this_category_value) {
					$places_info[$place["id"]]["categories"][$this_category_key] = Array(
						"name" => $this_category_value,
						"url" => $this_categories_urls[$this_category_key]
					);
				}
				
				// generate search terms for this place
				$search_terms_string[$place["id"]] = generate_search_terms($this_category_key, $next_sub_category, $places_info[$place["id"]]);
								
				// if search query is set, check if search query words are found in search terms
				if($url_neighborhood) {
					// clear search match array
					$search_match = Array();
					
					// check if any word in search query matches any word in search terms
					foreach($url_neighborhood_terms as $url_neighborhood_term) {
						if(strpos(strtoupper($search_terms_string[$place["id"]]), strtoupper(convert("search-query", "url", $url_neighborhood_term))) > 1) {
							$search_match[$place["id"]][] = 1;
						} else {
							$search_match[$place["id"]][] = 0;
						}
					}
					
					// if all search query words are found in search terms, add place to subcategory array
					if(!in_array("0", $search_match[$place["id"]])) {
						$places_by_sub_category[$next_sub_category][]["id"] = $place["id"];
					}
				} else {
					// otherwise show all places
					$places_by_sub_category[$next_sub_category][]["id"] = $place["id"];
				}
			}
		
			$match = 0;
		}
		
		echo "<div id='places'>";
		
		// sort subcategories by number of places in each subcategory
		foreach($places_by_sub_category as $sub_category => $places_in_sub_category) {
			if($sub_category != "") {
				$sort = str_pad(count($places_in_sub_category), 20, "0", STR_PAD_LEFT)."%COUNT%".convert("category", "url", $sub_category);
			} else {
				$sort = "0"."%COUNT%".convert("category", "url", $sub_category);
			}
			$sub_categories_sorted_by_count[$sort] = $places_in_sub_category;
		}
	
		krsort($sub_categories_sorted_by_count);
		
		foreach($sub_categories_sorted_by_count as $sub_category_label_and_count => $places_in_sub_category) {
			// parse subcategory label and count
			$sub_category_label_and_count = explode("%COUNT%", $sub_category_label_and_count);
			$sub_category_label = $sub_category_label_and_count[1];
			$sub_category_count = trim($sub_category_label_and_count[0], 0);
			
			echo "<div class='subcategory-places'";
				if($sub_category_label) { echo "id='subcategory-".convert("category", "url", $sub_category_label)."'"; }
			echo ">";
			
				// render subcategory header
				render_subcategory_header($sub_category_label, $url_categories);
		
				// sort places in subcategory by rating
				foreach($places_in_sub_category as $place) {
					if($places_info[$place["id"]]["rating"] != "") {
					
						$rating = str_replace(".", "", number_format($places_info[$place["id"]]["rating"], 1));
						$rating_sort = convert_range($rating, 100, 0, 0, 100);
						
						$rating_sort = str_pad($rating_sort, 20, 0, STR_PAD_LEFT);
					} else {
						$rating_sort = str_pad(9, 20, 9, STR_PAD_LEFT);
					}
					
						
					$sort = $rating_sort.$places_info[$place["id"]]["name"].microtime().rand(0, 99999999);
					$places_in_sub_category_sorted[$sort] = $place;
				}
				
				ksort($places_in_sub_category_sorted);
	
				// for every place in subcategory, render place
				foreach($places_in_sub_category_sorted as $sort => $place) {
				
					// get category for this place
					$this_category = $places_info[$place["id"]]["categories"][count($places_info[$place["id"]]["categories"])-1];
				
					// increment number of places in this subcategory
					$number_of_places++;
				
					// increment number of places on this street
					if(preg_match('/[0-9]+/', $places_info[$place["id"]]["address"])) {
						$building_number = explode(" ", $places_info[$place["id"]]["address"]);
						$street = str_replace($building_number[0]." ", "", $places_info[$place["id"]]["address"]);
					} else {
						$street = $places_info[$place["id"]]["address"];
					}
					$popular["streets"][$street]++;
				
					// increment number of places in this neighborhood
					$popular["neighborhoods"][$places_info[$place["id"]]["neighborhood"]]++;
				
					// increment number of places with this rating
					$popular["ratings"][$places_info[$place["id"]]["rating"]]++;
					
					// render place info
					render_place_info($places_info[$place["id"]], $search_terms_string[$place["id"]], $url_categories, $url_neighborhood_terms, $sub_category_label);
				
					// empty subcategory places so next subcategory is filled with correct places
					$places_in_sub_category_sorted = Array();
				}

			echo "</div>"; // close .subcategory-places
		}
				
		echo "</div>"; // close #places
		
		// render list header
		echo "<div id='navigation'>";
		
			echo "<div id='list-header'>";
		
				// render list name
				echo "<h1><a href='".$root.$list_name_url_without_neighborhood."'>".$list["name"]."</a></h1>";
		
				// render category breadcrumbs
				render_category_breadcrumbs($url_category_names, $this_categories_urls, $number_of_places);
				
				echo "<div id='empty-search-results'>Nothing found...<br><a id='clear-search'>clear search</a></div>";
		
			echo "</div>"; // close #list-header
		
			// render sub category index
			render_sub_category_index($places_by_sub_category, $url_categories);
			
			// render neighborhood index
			render_neighborhood_index($popular["neighborhoods"], $url_categories);
		
		echo "</div>"; // close #navigation

	}
?>