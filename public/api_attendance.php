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
            api_attendance();
        } else {
            echo "Invalid Bearer key.";
        }
    } else {
        echo "Invalid Service Request. Bearer token required";
    }

    function api_attendance() {
        // http://localhost/api/api_attedance.php in local
        // http://45.77.171.151/api/api_attedance.php in server
        global $link;

        // get posted data
        $json = file_get_contents('php://input');
        $data = json_decode($json);

        $key = 'Bearer 65B6778032156'; //api key
        
        // set property values
        $jwt = isset($data->jwt) ? $data->jwt : '';
        $lat = isset($data->lat) ? $data->lat : '';
        $long = isset($data->long) ? $data->long : '';

        if ($jwt) {
            
            try {
                // decode jwt
                $decoded = JWT::decode($jwt, $key, array('HS256'));

                // result array values of decode data
                $email = $decoded->data->email;

                // query sql to get data email
                $sql = "SELECT code FROM users WHERE email = '".$email."'";
                
                // result of query sql
                $result = $link->query($sql);

                // result num rows of query sql
                $result_num_row = $result->num_rows;

                // check if num rows result of query sql equals 1
                if ($result_num_row == 1) {
                    // result array values of query sql
                    $row = $result->fetch_assoc();
                    // get nik value
                    $nik = $row['code'];

                    //echo $nik;
                }

                /* query sql to check distance in km
                    3959 - Miles
                    6371 - Kilometers
                */
                $sql2 = "SELECT 
                        *,
                        (6371 * ACOS(COS(RADIANS('".$lat."')) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS('".$long."')) + SIN(RADIANS('".$lat."')) * SIN(RADIANS(lat)))) AS distance
                    FROM
                        location_gps
                    ORDER BY distance ASC
                    LIMIT 1;";

                // result of query sql
                $result2 = $link->query($sql2);
                // result array values of query sql
                $row2 = $result2->fetch_assoc();
                // get distance
                $distance = $row2['distance'];
                // get radius
                $radius = $row2['radius'];

                
                // check if distance closer than radius
                if ($distance < $radius) {
                    // get location_id is an id from table locations 
                    $location_id = $row2['id'];
                    // get location name is an name from table locations 
                    $location_name = $row2['name'];
                    // insert data to table attendance
                    $sql3 = "INSERT INTO absens (absen_id, absen_date, location_id) VALUES('".$nik."',now(),'".$location_id."');";
                    // result of query sql
                    $result3 = $link->query($sql3);

                    echo json_encode(array(
                        "status" => "Success",
                        "message" => "Anda sudah absen di {$location_name}",
                        "data" => $decoded->data->email
                    ));
                    
                }
                // if distance further than radius 
                else {
                    // insert data to table attendance and set location_id to NULL
                    //$sql3 = "INSERT INTO attendances (nik_user, absen_date, location_id) VALUES('".$nik."',now(),null);";
                    // result of query sql
                    //$result3 = $link->query($sql3);
                    echo json_encode(array(
                        "status" => "Failed",
                        "message" => "Tidak bisa absen karena berada di luar lokasi"
                    ));
                }
            }

            catch(Exception $e) {
                echo json_encode(array(
                    "status" => "Failed",
                    "message" => "Access Denied",
                    "error" => $e->getMessage()
                ));
            }
        } else {
            echo json_encode(array("message" => "Token is empty"));
        }
    
    }

    $link->close();

?>
