<?php
require( 'common.php' );

$q = $_GET['q'];
$limit = $_GET['limit'] ? $_GET['limit'] : 30;
$skip = $_GET['skip'] ? $_GET['skip'] : 0;
if( $q ){
}else{
  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME . '/_all_docs'; 
  $opts = array(
    'http' => array(
      'method' => 'GET',
      'header' => array(
        'Authorization: Basic ' . base64_encode( DB_USER . ':' . DB_PASSWORD )
      )
    )
  );
  $context = stream_context_create( $opts );
  $r = file_get_contents( $request, false, $context );
  $json = json_decode( $r );

  header( 'HTTP/1.1 200 OK' );
  header( 'Status: 200' );
  header( 'Content-Type: application/json' );

  //echo '{"status": true}';
  echo $r;
}
?>
