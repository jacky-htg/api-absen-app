<?php
    // declare a function to fetch headers. because the native php function is not recognized by nginx server environment
    if (!function_exists('getallheaders'))
    {
        function getallheaders()
        {
           $headers = array ();
           foreach ($_SERVER as $name => $value)
           {
               if (substr($name, 0, 5) == 'HTTP_')
               {
                   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
               }
           }
           return $headers;
        }
    }
    
    $link = new mysqli($host,$login,$pass,$db,$port);

    if ($link->connect_errno) {
        printf("Connect failed: %s\n", $link->connect_error);
        exit();
    }
