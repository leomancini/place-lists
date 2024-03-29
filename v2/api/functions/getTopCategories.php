<?php
    function getCategoriesData() {
        global $db;

        try {
            $categories_query = $db->prepare("SELECT * FROM categories");
            $categories_query->execute();
            $categories_query->setFetchMode(PDO::FETCH_ASSOC);
    
            $categories = [];
    
            $categories_data_output = [];

            while($categories_data = $categories_query->fetch()) {
                $categories_data_output[$categories_data["foursquare_id"]] = $categories_data;
            }
    
            $db = null;

            return $categories_data_output;
        } catch(PDOException $error) {
            error_log('PDOException - ' . $error->getMessage(), 0);
            http_response_code(500);
            
            die('Database error (see logs)');
        }
    }

    function getTopCategories($places_data) {
        $categories_data = getCategoriesData();

        $this_list_categories = [];

        foreach($places_data as $place_data) {
            if($place_data["category_id"] !== "") {
                array_push($this_list_categories, $place_data["category_id"]);
            }
        }

        $this_list_categories_with_counts = array_count_values($this_list_categories);
        
        $this_list_categories_with_counts_and_data = [];

        foreach($this_list_categories_with_counts as $foursquare_id => $count) {
            if($count > 1 && array_key_exists($foursquare_id, $categories_data)) {

                $url_array = [];

                foreach($categories_data[$foursquare_id] as $category_data_field => $category_data_value) {
                    if(
                        ($category_data_field === "foursquare_id" ||
                        $category_data_field === "parent_category_foursquare_id" ||
                        $category_data_field === "grandparent_category_foursquare_id" ||
                        $category_data_field === "greatgrandparent_category_foursquare_id" ||
                        $category_data_field === "greatgreatgrandparent_category_foursquare_id") &&
                        $category_data_value !== ""
                    ) {
                        array_unshift($url_array, convert("category", "url", $categories_data[$category_data_value]["short_name"]));
                    }
                }

                array_push(
                    $this_list_categories_with_counts_and_data,
                    [
                        "count_in_this_list" => $count,
                        "percentage_in_this_list" => round(($count / count($places_data)) * 100, 2),
                        "data" => $categories_data[$foursquare_id],
                        "url" => implode("/", $url_array)
                    ]
                );
            }
        }

        usort($this_list_categories_with_counts_and_data, function($a, $b) {
            return $b["count_in_this_list"] > $a["count_in_this_list"];
        });

        return $this_list_categories_with_counts_and_data;
    }
?>