<?php
    define('APP', '/home/users/api-absen-tasti/');
    
    include APP."config/config.php";
    include APP."libs/Generic.php";
    include APP."libs/BeforeValidException.php";
    include APP."libs/ExpiredException.php";
    include APP."libs/SignatureInvalidException.php";
    include APP."libs/JWT.php";
    use \Firebase\JWT\JWT;
  
    include APP."route/route.php";
