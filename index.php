<html>
<head>
<meta charset="utf8"/>
<title>My Shop</title>
<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css"/>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.5/cerulean/bootstrap.min.css"/>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<script src="./cvi_busy_lib.js"></script>
<link href="./colorbox.css" rel="stylesheet"/>
<script type="text/javascript" src="./jquery.colorbox-min.js"></script>
<script type="text/javascript">
var limit = 30;
var skip = 0;
$(function(){
  getItems( limit, skip );

  $('form').submit( function(){
    var q = $('#q').val();
    if( q ){
      searchItems( q, limit, skip );
    }else{
      getItems( limit, 0 );
    }
    return false;
  });
});

function getItems( limit, skip ){
  $('#itemslistbody').html( '' );
  var obj = getBusyOverlay( 'viewport', {color:'black', opacity:0.5, text:'取得中', style:'text-decoration:blink; font-weight:bold; font-size:12px; color:white;' } );
  $.ajax({
    url: './list.php?limit=' + limit + '&skip=' + skip,
    type: 'GET',
    success: function( result ){
      obj.remove();
      //console.log( result );
      result = JSON.parse( result );
      if( result.status && result.items ){
        var items = result.items;
        var aws_tag = result.aws_tag;
        var cnt = result.cnt;
        for( var i = 0; i < items.length; i ++ ){
          var item = items[i];
          var id = item.id;
          var code = ( item.doc.code ? item.doc.code : '' );
          var name = ( item.doc.name ? item.doc.name : '' );
          var brand = ( item.doc.brand ? item.doc.brand : '' );
          var maker = ( item.doc.maker ? item.doc.maker : '' );
          //var price = ( item.doc.price ? item.doc.price : 0 );
          var asin = ( item.doc.asin ? item.doc.asin : '' );
          var image_url = item.doc.image_url;
          var image_url2 = ( image_url ? image_url.split( 'SL160' ).join( 'SL300' ) : '' );
          var link = asin ? 'http://www.amazon.co.jp/dp/' + asin + '?tag=' + aws_tag + '&linkCode=as1&creative=6339' : '';
          $('#itemslistbody').append( '<tr><td>' + code + '<br/><a class="single iframe" rel="external" href="' + image_url2 + '" title="' + name + '"><img src="' + image_url + '" width="30" height="30"/></a></td><td>' + ( link ? '<a target="_blank" href="' + link + '">' + name + '</a>' : name ) + '</td><td>' + brand + '</td><td>' + maker + '</td></tr>' );
        }

        var tr = '<tr><td align="left">';
        if( skip > 0 ){
          var newskip = ( skip < limit ? 0 : skip - limit );
          tr += '<a href="#" onClick="getItems( ' + limit + ', ' + newskip + ' );">&lt;&lt;</a>';
        }else{
          tr += '&nbsp;';
        }
        tr += '</td><td colspan="2" align="middle">' + ( skip + 1 ) + ' - ' + ( skip + limit ) + ' / ' + cnt + '</td>';

        tr += '<td align="right">';
        if( skip + limit < cnt ){
          tr += '<a href="#" onClick="getItems( ' + limit + ', ' + ( skip + limit ) + ' );">&gt;&gt;</a>';
        }else{
          tr += '&nbsp;';
        }
        tr += '</td></tr>';
        $('#itemslistbody').append( tr );

        $('.iframe').colorbox({
          iframe: true,
          width: "90%",
          height: "90%"
        });
      }
    },
    error: function(){
      obj.remove();
      console.log( "error" );
    }
  });
}

function searchItems( q, limit, skip ){
  $('#itemslistbody').html( '' );
  var obj = getBusyOverlay( 'viewport', {color:'black', opacity:0.5, text:'取得中', style:'text-decoration:blink; font-weight:bold; font-size:12px; color:white;' } );
  $.ajax({
    url: './list.php?q=' + q + '&limit=' + limit + '&skip=' + skip,
    type: 'GET',
    success: function( result ){
      obj.remove();
      result = JSON.parse( result );
      //console.log( result );
      if( result.status && result.items ){
        var items = result.items;
        var aws_tag = result.aws_tag;
        var cnt = result.cnt;
        for( var i = 0; i < items.length; i ++ ){
          var item = items[i];
          var id = item.id;
          var code = ( item.fields.code ? item.fields.code : '' );
          var name = ( item.fields.name ? item.fields.name : '' );
          var brand = ( item.fields.brand ? item.fields.brand : '' );
          var maker = ( item.fields.maker ? item.fields.maker : '' );
//          var price = ( item.fields.price ? item.fields.price : 0 );
          var asin = ( item.fields.asin ? item.fields.asin : '' );
          var image_url = item.fields.image_url;
          var image_url2 = ( image_url ? image_url.split( 'SL160' ).join( 'SL300' ) : '' );
          var link = asin ? 'http://www.amazon.co.jp/dp/' + asin + '?tag=' + aws_tag + '&linkCode=as1&creative=6339' : '';
          $('#itemslistbody').append( '<tr><td>' + code + '<br/><a class="single iframe" rel="external" href="' + image_url2 + '" title="' + name + '"><img src="' + image_url + '" width="30" height="30"/></a></td><td>' + ( link ? '<a target="_blank" href="' + link + '">' + name + '</a>' : name ) + '</td><td>' + brand + '</td><td>' + maker + '</td></tr>' );
        }

        var tr = '<tr><td align="left">';
        if( skip > 0 ){
          var newskip = ( skip < limit ? 0 : skip - limit );
          tr += '<a href="#" onClick="searchItems( \'' + q + '\', ' + limit + ', ' + newskip + ' );">&lt;&lt;</a>';
        }else{
          tr += '&nbsp;';
        }
        tr += '</td><td colspan="2" align="middle">' + ( skip + 1 ) + ' - ' + ( skip + items.length ) + ' / ' + cnt + '</td>';

        tr += '<td align="right">';
        if( skip + limit < cnt ){
        //if( items.length == limit ){
          tr += '<a href="#" onClick="searchItems( \'' + q + '\', ' + limit + ', ' + ( skip + limit ) + ' );">&gt;&gt;</a>';
        }else{
          tr += '&nbsp;';
        }
        tr += '</td></tr>';
        $('#itemslistbody').append( tr );

        $('.iframe').colorbox({
          iframe: true,
          width: "90%",
          height: "90%"
        });
      }
    },
    error: function( e ){
      obj.remove();
      console.log( e );
    }
  });
}
</script>
<style>
html, body, {
  background-color: #ddddff;
  height: 100%;
  margin: 0px;
  padding: 0px
}
#cboxOverlay {
    background: #000;
}
html, body, {
  background-color: #ddddff;
  height: 100%;
  margin: 0px;
  padding: 0px
}
#cboxOverlay {
    background: #000;
}
#cboxLoadedContent {
    background: #fff;
}
#cboxLoadedContent {
  padding: 0;
  overflow: auto;
    -moz-box-shadow: 0px 1px 10px #000000;
    -webkit-box-shadow: 0px 1px 10px #000000;
    box-shadow: 0px 1px 10px #000000;
}
#cboxPrevious, #cboxNext, #cboxSlideshow, #cboxClose , #cboxTitle {
  top: -30px;
}
#colorbox, #cboxOverlay, #cboxWrapper {
  overflow: visible ;
}
#cboxTitle {
  color: #fff;
}
#inline-content {/* インラインを使用する時のみ */
    margin: 20px;
}
#ajax-wrap {/* ajaxを使用する時のみ */
  margin: 20px;
}
</style>
</head>
<body>

<div class="container" style="padding:20px 0; font-size:8px;">
  <div class="jumbotron">
    <h1>My Select Shop</h1>
    <p>My favorites items.</p>
  </div>
</div>

<div class="container" style="padding:20px 0; font-size:8px;">
  <form name="frm">
  <input type="text" name="q" id="q" value="" placeholder="search word"/>
  </form>
</div>

<div class="container" style="padding:20px 0; font-size:8px;">
<table class="table table-bordered table-hobor table-condensed">
  <thead>
    <tr><th>#</th><th>NAME</th><th>BRAND</th><th>MAKER</th></tr>
  </thead>
  <tbody id='itemslistbody'>
  </tbody>
</table>
</div>
</body>
</html>
