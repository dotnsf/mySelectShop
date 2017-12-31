<?php
require( 'common.php' );

$nodes = array(
  //. https://affiliate.amazon.co.jp/gp/associates/help/t100
/*
  "52905051", //. ビューティー/スキンケア
  "52908051", //. ビューティー/ヘアケア
  "52907051", //. ビューティー/ボディケア
*/
  "52912051"  //. ビューティー/男性化粧品
);

//initialize_mysampledata_sql();
foreach( $nodes as $node ){
  getCodesFromAmazonAPI( $node );
}
exit;
?>
