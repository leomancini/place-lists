<?php
    require("base.php");

    $hidden_lists = Array();

    foreach($split_list_combos as $split_list_combo) {
        foreach($split_list_combo["children"] as $split_list_child_key => $split_list_child_value) {
            array_push($hidden_lists, $split_list_child_value);
        }
    }

    try {
        $lists_query = $db->prepare("SELECT * FROM lists WHERE private <> 1");
        $lists_query->execute();
        $lists_query->setFetchMode(PDO::FETCH_ASSOC);

        $lists = [];

        while($list_data = $lists_query->fetch()) {
            $this_split_list = false;

            foreach($split_list_combos as $split_list_combo) {
                if($list_data["foursquare_id"] === $split_list_combo["parent"]) {
                    $this_split_list = true;
                    $list_data["places_count"] = places_count($list_data);
                }
            }

            if(!in_array($list_data["foursquare_id"], $hidden_lists)) {
                $list_data["url"] = convert("url", "url", $list_data["name"]);
                $list_data["split_list"] = $this_split_list;

                array_push($lists, $list_data);
            }
        }
    
        usort($lists, function($a, $b) {
            return $b['places_count'] > $a['places_count'];
        });
    
        if(isset($_GET['JSON_PRETTY_PRINT'])) {
            $json = json_encode($lists, JSON_PRETTY_PRINT);
            echo "<pre>".$json."</pre>";
        } else {
            $json = json_encode($lists);
            echo $json;
        }

        $db = null;
    } catch(PDOException $error) {
        error_log('PDOException - ' . $error->getMessage(), 0);
        http_response_code(500);
        
        die('Database error (see logs)');
    }
?>