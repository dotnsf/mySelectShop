<?php

//. Amazon Associate TAG
define( 'AWS_ASSOC_TAG', '' );
define( 'AWS_KEY', '' );
define( 'AWS_SECRET', '' );

//. Cloudant
define( 'DB_NAME', 'items' );
$def_db_user = 'cloudant_username';
$def_db_password = 'cloudant_password';


//. For IBM Cloud
$db_user = null;
$db_password = null;
if( getenv( 'VCAP_SERVICES' ) ){
  $vcap = json_decode( getenv( 'VCAP_SERVICES' ), true );
  
  $credentials = NULL;
  try{
    if( isset( $vcap['cloudantNoSQLDB'] ) ){
      $credentials = $vcap['cloudantNoSQLDB'][0]['credentials'];
    }
  }catch( Exception $e ){
  }
  if( $credentials == NULL ){
    try{
      $db_user = $credentials['username'];
      $db_password = $credentials['password'];
    }catch( Exception $e ){
    }
  }
}

if( $db_user == null ){
  define( 'DB_USER', $def_db_user );
}else{
  define( 'DB_USER', $db_user );
}
if( $db_password == null ){
  define( 'DB_PASSWORD', $def_db_password );
}else{
  define( 'DB_PASSWORD', $db_password );
}


?>
