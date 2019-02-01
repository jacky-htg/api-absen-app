<?php
    include "../config/db_config.php";    // files needed to connect to database
    // generate json web token
    include "../config/api_config.php";   // key bearer & jwt setting location
    include "../libs/Generic.php";
    include "../libs/BeforeValidException.php";
    include "../libs/ExpiredException.php";
    include "../libs/SignatureInvalidException.php";
    include "../libs/JWT.php";
    use \Firebase\JWT\JWT;

    $header = getallheaders();

    if (!empty($header["Authorization"])) {
        $auth = $header["Authorization"];

        if ($auth === $key) {
            api_login();
        } else {
            echo "Invalid Bearer key.";
        }
    } else {
        echo "Invalid Service Request. Bearer token required";
    }

    function api_login() {
        // http://localhost/api/api_login.php // in local
        // http://45.77.47.52/api/api_login.php //in server
        global $link;

        // get posted data
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        // set property values
        $email = isset($data->email) ? $data->email : '';
        $password = isset($data->password) ? $data->password : '';
        $password = sha1(SALT.$password);
        
        // query sql to get data
        $sql = "SELECT * FROM users WHERE email = '".$email."' AND password = '".$password."'";
        
        // result of query sql
        $result = $link->query($sql);
        // result array values of query sql
        $row = $result->fetch_assoc();

        // check user email & password login
        if ($row['email'] == $email && $row['password'] == $password) {
            // if exist in db, then return given jwt and display message success

            $key = "Bearer 65B6778032156";

            $token = array(
               "iss" => $iss,
               "aud" => $aud,
               "iat" => $iat,
               "nbf" => $nbf,
               "data" => array(
                   "email" => $data->email,
                   "password" => $data->password
                )
            );

            // generate jwt
            $jwt = JWT::encode($token, $key);
            $decoded = JWT::decode($jwt, $key, array('HS256'));

            $response = array(
                'message' => 'Login Success!',
                'Token' => $jwt
            );   
        } else {
            // if doesn't exist in db, then return display message failed
            $response = array(
                'message' => 'Login Failed!'
            );
        }

        // return display json message
        echo json_encode($response);
    
    }

    $link->close();
