<?php
    require("helpers/base.php");

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
            if(!in_array($list_data["foursquare_id"], $hidden_lists)) {
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
    }
        catch(PDOException $error){
        error_log('PDOException - ' . $error->getMessage(), 0);
        http_response_code(500);
        
        die('Error establishing connection with database');
    }
?>