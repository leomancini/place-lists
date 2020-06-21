<?php
    require("helpers/base.php");

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
        $places_metadata_saved_timestamps = [];

        // Add places data to response
        while($place_data = $places_query->fetch()) {
            array_push($result["places"]["data"], $place_data);

            if($place_data["saved_timestamp"] !== "") {
                array_push($places_metadata_saved_timestamps, date("Y-m", $place_data["saved_timestamp"]));
            }
        }

        // Add metadata to response: number of places in list
        $result["places"]["metadata"]["places_count"] = count($result["places"]["data"]);

        // Add metadata to response: counts of when places were saved
        $places_metadata_saved_timestamp_counts = array_count_values($places_metadata_saved_timestamps);
        ksort($places_metadata_saved_timestamp_counts);
        
        $result["places"]["metadata"]["saved_timestamp_counts"] = [];

        foreach ($places_metadata_saved_timestamp_counts as $date => $count) {
            array_push(
                $result["places"]["metadata"]["saved_timestamp_counts"],
                [
                    "date" => $date,
                    "count" => $count
                ]
            );
        }

        $result["places"]["metadata"]["saved_timestamp_median_count"] = getMedian($places_metadata_saved_timestamp_counts);
        
        $places_metadata_saved_timestamps_greater_than_median = [];
        foreach ($result["places"]["metadata"]["saved_timestamp_counts"] as $saved_timestamp_date_count_pair) {
            if ($saved_timestamp_date_count_pair["count"] > $result["places"]["metadata"]["saved_timestamp_median_count"]) {
                array_push($places_metadata_saved_timestamps_greater_than_median, $saved_timestamp_date_count_pair);
            }
        }

        $result["places"]["metadata"]["greater_than_saved_timestamp_median_count"] = $places_metadata_saved_timestamps_greater_than_median;

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
    }
        catch(PDOException $error){
        error_log('PDOException - ' . $error->getMessage(), 0);
        http_response_code(500);

        die($error);
    }
?>