<?php

function show_viewport_size(){
    echo "
    <script src=\"https://code.jquery.com/jquery-latest.js\"></script>
    <script>
        $(document).ready(function(e) {
            showViewportSize(); 
        });
        $(window).resize(function(e) {
            showViewportSize();
        });
        function showViewportSize() {
            var the_width = $(window).width();
            var the_height = $(window).height();                   
            $('#width').text(the_width);
            $('#height').text(the_height);
        }
    </script>
    <div style=\"margin-left: 30px;\">
        <h2>Viewport size:</h2>
        <div style=\"margin-left: 10px; font-size: 18px;\">Width: <label id=\"width\">...</label></div>
        <div style=\"margin-left: 10px; font-size: 18px;\">Height: <label id=\"height\">...</label></div>
    </div>
    ";

    // insert: <?php include '../actions/utility/helpers.php'; show_viewport_size(); ? >
}

function getIPAddress() {  
    // Whether ip is from a shared connection  
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
        $ip = $_SERVER['HTTP_CLIENT_IP'];  
    }  
    // Whether ip is from a proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    }  
    // Whether ip is from a remote address  
    else{  
        $ip = $_SERVER['REMOTE_ADDR'];  
    }  
    return $ip;  
}


?>