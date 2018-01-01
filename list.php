<?php
require( 'common.php' );

$q = '';
$limit = 30;
$skip = 0;

if( $_GET['q'] ){ $q = $_GET['q']; }
if( $_GET['limit'] ){ $limit = $_GET['limit']; }
if( $_GET['skip'] ){ $skip = $_GET['skip']; }
if( $q ){
  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME . '/_design/ftsearch/_search/itemsIndex?include_docs=true&q=' . urlencode($q) . '&limit=' . $limit . '&skip=' . $skip; 
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
  $items = $json->{'rows'};

  $body = '{"status":true, "aws_tag":"' . AWS_ASSOC_TAG . '", "cnt": ' . $total_rows . ', "items": ' . json_encode( $items, JSON_UNESCAPED_UNICODE ) . '}';

  header( 'HTTP/1.1 200 OK' );
  header( 'Status: 200' );
  header( 'Content-Type: application/json' );

  echo $body;
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
//var_dump($json);
  $total_rows = $json->{'total_rows'} - 1;

  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME . '/_all_docs?include_docs=true&limit=' . $limit . '&skip=' . $skip; 
  $r = file_get_contents( $request, false, $context );
  $json = json_decode( $r );
  //$rows = json_encode( $json->{'rows'}, JSON_UNESCAPED_UNICODE );
  $items = $json->{'rows'};

  $body = '{"status":true, "aws_tag":"' . AWS_ASSOC_TAG . '", "cnt": ' . $total_rows . ', "items": ' . json_encode( $items, JSON_UNESCAPED_UNICODE ) . '}';

  header( 'HTTP/1.1 200 OK' );
  header( 'Status: 200' );
  header( 'Content-Type: application/json' );

  echo $body;
}
?>
