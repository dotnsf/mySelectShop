<?php
require_once( './credentials.php' );

//. Result filename
if( $argc == 1 ){
  define( 'OUTPUT_FILENAME', 'items.txt' );
}else{
  define( 'OUTPUT_FILENAME', $argv[1] );
}
//echo( 'Target filename : ' . OUTPUT_FILENAME . "\n\n" );


//. USER_AGENT
define( 'CRAWLER_USER_AGENT', 'XXX (Linux)' );


function initData(){
  if( $argc <= 2 ){
    //. initialize output file
    $fno = fopen( OUTPUT_FILENAME, 'w' );
    if( $fno ){
      fwrite( $fno, "" );
      fclose( $fno );
    }else{
      echo( "Faild to open " . OUTPUT_FILENAME ."\n" );
    }
  }
}

function initDB(){
  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME;
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
  if( $r === false ){
    $opts = array(
      'http' => array(
        'method' => 'PUT',
        'header' => array(
          'Authorization: Basic ' . base64_encode( DB_USER . ':' . DB_PASSWORD )
        )
      )
    );
    $context = stream_context_create( $opts );
    $r = file_get_contents( $request, false, $context );
  }
}


function trimText( $html, $startArr, $endText ){
	$txt = "";

	$b = true;
	$n = 0;
	for( $i = 0; $i < count( $startArr ) && $b; $i ++ ){
		$m = mb_strpos( $html, $startArr[$i], $n );
		if( $m ){
			$n = $m + mb_strlen( $startArr[$i] ); //. + 1 ?
		}else{
			$b = false;
		}
	}

	if( $b ){
		$m = mb_strpos( $html, $endText, $n );
		if( $m ){
			$txt = mb_substr( $html, $n, $m - $n );
		}
	}

	return $txt;
}

function trimTextNext( $html, $startArr, $endText ){
	$txt = "";
	$next = "";

	$b = true;
	$n = 0;
	for( $i = 0; $i < count( $startArr ) && $b; $i ++ ){
		$m = mb_strpos( $html, $startArr[$i], $n );
		if( $m ){
			$n = $m + mb_strlen( $startArr[$i] );
		}else{
			$b = false;
		}
	}

	if( $b ){
		$m = mb_strpos( $html, $endText, $n );
		if( $m ){
			$txt = mb_substr( $html, $n, $m - $n );
			$next = mb_substr( $html, $m + mb_strlen( $endText ) );
		}
	}

	return array( $txt, $next );
}

function trimPrice( $p ){
	//. 価格が範囲になっているケース
	if( preg_match( '/ - /', $p ) ){
		list($p1,$p2) = split(' - ',$p);
		$p = $p1; //. 安い方
	}

	$p = preg_replace( "/[^0-9]/", "", $p );

	return $p;
}

function trimNL( $w ){
	$w = trim( $w );
        $w = str_replace( "\n", "", $w );
        $w = str_replace( "\r", "", $w );
        $w = str_replace( "\t", "", $w );
        $w = str_replace( "'", "\'", $w );

	return $w;
}

function addLine( $line ){
	$fno = fopen( OUTPUT_FILENAME, 'a' );
	if( $fno ){
		fwrite( $fno, $line . "\n" );
		fclose( $fno );
	}else{
		echo( "Faild to open " . OUTPUT_FILENAME ."\n" );
	}
}



function update_item_master_text( $itemcode, $itemname, $itemimageurl, $makername, $brandname, $listprice, $asin ){
  if( $asin ){
    //$line = $itemcode . "\t" . $itemname . "\t" . $itemimageurl . "\t" . $makername . "\t" . $brandname . "\t" . $listprice . "\t" . $asin;
    $line = '{"asin":"' . $asin . '"';
    if( $itemcode ){
      $line .= ( ', "code":"' . $itemcode . '"' );
    }
		if( $itemname ){
			$line .= ( ', "name":"' . $itemname . '"' );
		}
		if( $listprice > 0 ){
			$line .= ( ', "price":' . $listprice );
		}
		if( $brandname ){
			$line .= ( ', "brand":"' . $brandname . '"' );
		}
		if( $makername ){
			$line .= ( ', "maker":"' . $makername . '"' );
		}
		if( $itemimageurl ){
			$line .= ( ', "image_url":"' . $itemimageurl . '"' );
		}
		$line .= ( '}' );
    echo( "$line \n" );

	  addLine( $line );
  }
	return 1;
}

/*
function initialize_mysampledata_sql(){
	$filename = "../mysampledata.sql";
	addLine( $filename, "drop table if exists items" );
	addLine( $filename, "create table items( id int primary key auto_increment, code varchar(20), name varchar(1024), price int, brand varchar(1024), maker varchar(1024), image_url varchar(1024), asin varchar(20) )" );

	return 1;
}

function update_mysampledata_sql( $itemcode, $itemname, $itemimageurl, $makername, $brandname, $listprice, $asin ){
	$filename = "../mysampledata.sql";
        $line = "insert into items(code,name,price,brand,maker,image_url,asin) values('".$itemcode."','".$itemname."',".$listprice.",'".$brandname."','".$makername."','".$itemimageurl."','".$asin."');";
echo( "$line \n" );

	addLine( $filename, $line );

	return 1;
}



function update_master_tsv( $filename ){
	$cnt = 0;
	$pdo = new PDO( 'mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset=utf8', DB_USER, DB_PASSWORD );

	if( $pdo != null ){
		$pdo->query( 'SET NAMES utf8' );

		//. Update
		$fno = fopen( OUTPUT_DIR . $filename, 'r' );
		if( $fno ){
			if( flock( $fno, LOCK_SH ) ){
				while( !feof( $fno ) ){
					$line = fgets( $fno );
					$line = removeTail($line);
					if( strpos( $line, "\t" ) ){
						try{
							//list($item_code, $item_name, $item_name2, $item_engname, $item_listprice, $item_brand_id, $item_maker_id, $item_image_url, $item_category_id, $item_desc, $item_component, $item_capacity, $item_point, $item_ranking_point, $item_step ) = split( "\t", $line );
							list($item_code, $item_name, $item_image_url, $item_brand, $item_maker, $item_listprice, $item_asin ) = split( "\t", $line );

							if( $item_code != null && preg_match( "/^[0-9]+$/", $item_code ) ){
								//$cnt += insert_item_master( $pdo, $item_code, $item_name, $item_name2, $item_engname, $item_listprice, $item_brand_id, $item_maker_id, $item_image_url, $item_category_id, $item_desc, $item_component, $item_capacity, $item_point, $item_ranking_point, $item_step );
								$cnt += insert_items( $pdo, $item_code, $item_name, $item_listprice, $item_brand, $item_maker, $item_image_url, $item_asin );
							}
						}catch( Exception $e ){
							echo( "Exception : $e \n" );
						}
					}
				}
				flock( $fno, LOCK_UN );
			}
		}
		fclose( $fno );


		//. Zip & Remove
		//zip_remove( $filename );
	}

	return $cnt;
}


function zip_remove( $filename ){
	$r = 0;

	//. Zip & Remove
	if( file_exists($filename) && !is_dir($filename) ){
		$zipfilename = str_replace( '.tsv', '.zip', $filename );
		$zip = new ZipArchive();
		if( ( $res = $zip->open( $zipfilename, ZipArchive::CREATE ) ) == true ){
			$dt = date('YmdHis');
			$zname = $filename;
			if( ( $n = strrpos( $zname, '/' ) ) != FALSE ){
				$zname = substr( $zname, $n + 1 );
			}
			$zname = str_replace( '.tsv', '_' . $dt . '.tsv', $zname );

			$zip->addFile($filename,$zname);
			$zip->close();

			$r ++;
		}

		unlink( $filename );

		$r ++;
	}

	return $r;
}
*/

function endsWith($haystack, $needle){
	return $needle == "" || substr($haystack, -strlen($needle)) == $needle;
}

function removeTail($line){
	while( endsWith( $line, "\r\n" ) ){
		$line = substr($line,0,strlen($line)-2);
	}
	while( endsWith( $line, "\n" ) ){
		$line = substr($line,0,strlen($line)-1);
	}
	while( endsWith( $line, "\r" ) ){
		$line = substr($line,0,strlen($line)-1);
	}

	return $line;
}


function getCodesFromAmazonAPI( $node ){
	for( $i = 0; $i < 100000; $i += 1000 ){
		getCodesAmazonNodeMinMax( $node, $i, $i + 999 );
	}
}

function getCodesAmazonNodeMinMax( $node, $min, $max ){
	//. Page 1
	usleep( 1400000 );
  echo( "node = $node : min = $min , max = $max , page = 1 \n" );
	$totalpages = getItemSearchAmazonAPI($node,$min,$max);

	if( $totalpages < 11 || $max - $min == 9 ){
		if( $totalpages > 1 ){
			//. Page 2+
			$m = ( $totalpages > 10 ) ? 10 : $totalpages;
			for( $p = 2; $p <= $m ; $p ++ ){
				usleep( 1400000 );
        echo( "node = $node :  min = $min , max = $max , page = $p / $totalpages \n" );
				getItemSearchAmazonAPI($node,$min,$max,$p);
			}
		}
	}else{
		//. Page 1+
		if( $max - $min == 999 ){
			for( $i = $min; $i < $max; $i += 100 ){
				getCodesAmazonNodeMinMax( $node, $i, $i + 99 );
			}
		}else if( $max - $min == 99 ){
			for( $i = $min; $i < $max; $i += 10 ){
				getCodesAmazonNodeMinMax( $node, $i, $i + 9 );
			}
		}else{
			for( $i = $min; $i <= $max; $i ++ ){
				getCodesAmazonNodeMinMax( $node, $i, $i );
			}
		}
	}

	return $totalpages;
}

function getItemSearchAmazonAPI($node,$min,$max,$item_page = 0,$aws_host = 'ecs.amazonaws.jp'){
	$totalpages = 0;
	$request = "http://" . $aws_host . "/onca/xml?";
	$timestamp = gmdate( "Y-m-d\TH:i:s\Z" );
//	echo( "timestamp = $timestamp\n" );

	$params = "AWSAccessKeyId=" . AWS_KEY . "&AssociateTag=" . AWS_ASSOC_TAG . "&BrowseNode=" . $node;

	if( $item_page > 0 ){
		$params .= ( "&ItemPage=" . $item_page );
	}

	$params .= ( "&MaximumPrice=" . $max . "&MinimumPrice=" . $min . "&Operation=ItemSearch&ResponseGroup=ItemAttributes%2CSmall%2CImages&SearchIndex=Beauty&Service=AWSECommerceService&Timestamp=" . urlencode( $timestamp ) . "&Version=2009-01-06" );

	$str = "GET\n" . $aws_host . "\n/onca/xml\n" . $params;

	$hash = hash_hmac( "sha256", $str, AWS_SECRET, true );

	$request .= ( $params . "&Signature=" . urlencode( base64_encode( $hash ) ) );

	$opts = array(
		'https'=>array(
			'method'=>'GET',
			'header'=>"User-Agent: ".CRAWLER_USER_AGENT."\r\n" .
				'Host: ' . $aws_host . "\r\n"
		)
	);
	$context = stream_context_create( $opts );
	$r = file_get_contents( $request, false, $context );
//echo "node = $node -> r = $r \n";

	$exc = false;
	try{
		$xml = new SimpleXMLElement( $r );
	}catch( Exception $e ){
		$exc = true;
	}

	if( !$exc && $xml->Items->Item[0] ){
		$totalpages = $xml->Items->TotalPages;
//echo( "totalpages = $totalpages \n" );
		$idx = 0;
		$item = $xml->Items->Item[$idx];
		while( $item != null && $idx < 10 ){
			$image_url = "";
			$manufacturer = "";
			$brand = "";
			$title = "";
			$listprice = "";
			$ean = "";
			$asin = "";
			try{
				$image_url = trimNL($item->MediumImage->URL);
			}catch( Exception $e ){
			}
			try{
				$manufacturer = trimNL($item->ItemAttributes->Manufacturer);
			}catch( Exception $e ){
			}
			try{
				$brand = trimNL($item->ItemAttributes->Brand);
			}catch( Exception $e ){
			}
			try{
				$title = trimNL($item->ItemAttributes->Title);
			}catch( Exception $e ){
			}
			try{
				$listprice = trimNL($item->ItemAttributes->ListPrice->Amount);
			}catch( Exception $e ){
			}
			try{
				$ean = trimNL($item->ItemAttributes->EAN);
			}catch( Exception $e ){
			}
			try{
				$asin = trimNL($item->ASIN);
			}catch( Exception $e ){
			}

			if( $listprice == '' ){
				$listprice = 0;
			}

			//. output
			update_item_master_text( $ean, $title, $image_url, $manufacturer, $brand, $listprice, $asin );
			//update_mysampledata_sql( $ean, $title, $image_url, $manufacturer, $brand, $listprice, $asin );

			$idx ++;
			try{
				$item = $xml->Items->Item[$idx];
			}catch( Exception $e ){
			}
		}
	}

	return $totalpages;
}


function loadBulk(){
  $fno = fopen( OUTPUT_FILENAME, 'r' );
  if( $fno ){
    $txt = "";
    while( ( $line = fgets( $fno ) ) !== false ){
      $txt .= ( $line . "\n" );
    }
    fclose( $fno );
    $lines = explode( "\n", $txt );

    $cnt = 0;
    $body = "";
    for( $i = 0; $i < count( $lines ); $i ++ ){
      $line = trim($lines[$i]);
      if( $line || $i == count( $lines ) - 1 ){
        if( $line ){
          if( mb_strlen( $body ) > 0 ){
            $body .= ",";
          }
          $body .= ( $line );
          $cnt ++;
        }

        if( $cnt == 1000 || $i == count( $lines ) - 1 ){
          $body = '{ "docs": [ ' . $body . ' ] }';
       	  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME . '/_bulk_docs';
          $opts = array(
		'http' => array(
			'method' => 'POST',
			'header' => array(
				'Content-type: application/json',
				'Content-Length: ' . strlen( $body ),
				'Authorization: Basic ' . base64_encode( DB_USER . ':' . DB_PASSWORD )
			),
			'content' => $body
		)
          );
          $context = stream_context_create( $opts );
	  usleep( 1000000 );
          $r = file_get_contents( $request, false, $context );
//echo( "r = $r\n" );

          $body = "";
          $cnt = 0;
        }
      }
    }
  }else{
    echo( "Faild to open " . OUTPUT_FILENAME ."\n" );
  }

  //. search index
  $body = '{ "_id": "_design/ftsearch", "indexes": { "itemsIndex": { "analyzer": "japanese", "index": "function(doc){ if(\'name\' in doc){ var fields = [doc.name, doc.code, doc.brand, doc.maker, doc.image_url, doc.asin]; index(\'default\', fields.join(\' \')); index(\'name\', doc.name, {store:\'yes\'}); index(\'code\', doc.code, {store:\'yes\'}); index(\'brand\', doc.brand, {store:\'yes\'}); index(\'maker\', doc.maker, {store:\'yes\'}); index(\'image_url\', doc.image_url, {store:\'yes\'}); index(\'asin\', doc.asin, {store:\'yes\'}); } }" } } }';
  $request = 'https://' . DB_USER . '.cloudant.com/' . DB_NAME . '/_design/ftsearch';
  $opts = array(
    'http' => array(
      'method' => 'PUT',
      'header' => array(
        'Content-type: application/json',
        'Content-Length: ' . strlen( $body ),
        'Authorization: Basic ' . base64_encode( DB_USER . ':' . DB_PASSWORD )
      ),
      'content' => $body
    )
  );
  $context = stream_context_create( $opts );
  usleep( 1000000 );
  $r = file_get_contents( $request, false, $context );
}

?>
