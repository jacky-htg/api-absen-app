<?php
    $function = "api_".substr($_SERVER['PATH_INFO'], 1);
    if (!file_exists (APP."app/{$function}.php")) {
        http_response_code(404);
        die();
    }        
    $header = getallheaders();

    if (!empty($header["Authorization"])) {
        $auth = $header["Authorization"];

        if ($auth === $key) {
            include APP."app/{$function}.php";
            $function();
        } else {
            echo "Invalid Bearer key.";
        }
    } else {
        echo "Invalid Service Request. Bearer token required";
    }
