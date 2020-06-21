<?php
    require("base.php");

    $foursquare_list_id = $_GET['foursquare_list_id'];

    try {
        // Setup response data model
        $result = [
            "metadata" => [],
            "places" => [
                "metadata" => [],
                "data" => []
            ]
        ];

        // Get metadata of list
        $metadata_query_string = "SELECT * FROM lists WHERE foursquare_id = :foursquare_list_id LIMIT 1";
        $metadata_query = $db->prepare($metadata_query_string);
        $metadata_query->bindParam(':foursquare_list_id', $foursquare_list_id);
        $metadata_query->execute();
        $metadata_query->setFetchMode(PDO::FETCH_ASSOC);
        $metadata = $metadata_query->fetch();

        // Add metadata to response: all fields from database
        $result["metadata"] = $metadata;
        $result["metadata"]["split_list"] = false;

        // Get places data
        $places_query_string = "SELECT * FROM places WHERE foursquare_list_id = :foursquare_list_id";

        foreach($split_list_combos as $split_list_combo) {
            if($foursquare_list_id === $split_list_combo["parent"]) {
                $result["metadata"]["split_list"] = true;

                foreach($split_list_combo["children"] as $split_list_child_id) {
                    $places_query_string .= " OR foursquare_list_id = '".$split_list_child_id."'";
                    $result["metadata"]["children"][] = $split_list_child_id;
                }
            }
        }

        $places_query = $db->prepare($places_query_string);
        $places_query->bindParam(':foursquare_list_id', $foursquare_list_id);
        $places_query->execute();
        $places_query->setFetchMode(PDO::FETCH_ASSOC);

        // Add places data to response
        while($place_data = $places_query->fetch()) {
            array_push($result["places"]["data"], $place_data);
        }

        // Add calculated metadata to response
        $result["places"]["metadata"] = [
            "top_categories" => getTopCategories($result["places"]["data"]),
            "saved_timestamp_counts_with_zeros" => getSavedTimestampStats($result["places"]["data"]),
            "important_timestamps" => getImportantTimestamps($result["places"]["data"]),
        ];
        
        // Add metadata to response: if this list is a split list
        if($result["metadata"]["split_list"] === true) {
            $result["metadata"]["places_count"] = strval(count($result["places"]["data"]));
        }

        // Format and return as JSON
        if(isset($_GET['JSON_PRETTY_PRINT'])) {
            $json = json_encode($result, JSON_PRETTY_PRINT);
            echo "<pre>".$json."</pre>";
        } else {
            $json = json_encode($result);
            echo $json;
        }

        // Close database connection
        $db = null;
    } catch(PDOException $error) {
        error_log('PDOException - ' . $error->getMessage(), 0);
        http_response_code(500);

        die('Database error (see logs)');
    }
?>