<?php
/**
* EC-CUBE Token OPTION / 定数の設定
*
* @param string $name 取得したい定数のKey
* @return string 定数の値
*/
if( !function_exists('get_eccube_api_config') ) :
function get_eccube_api_config( $name ){
    $configs = array(
        /* == 設定 == */
        'eccube_url' => 'https://example.com',
        'authorization_endpoint' => 'https://example.com/ec-cube-admin/authorize',
        'token_endpoint' => 'https://example.com/token',
        'api_endpoint' => 'https://example.com/api',
        'client_id' => 'This0is1a2lie3masquerading4as5ClientId',
        'client_secret' => '0clientsecret1consists2of3a4long5random6string7of8characters9This10should11be12kept13secret'
    );
    foreach( $configs as $key => $val ):
        if( $key === $name ) return $val;
    endforeach;
}
endif;

/**
* file_get_contents 
*
* @param string $request_url リクエストを投げるAPIのエンドポイント
* @param string $data リクエスト情報
* @param string $header リクエストヘッダー
* @return string Http Response
*/
if( !function_exists('file_get_contents_to_eccube') ) :
function file_get_contents_to_eccube( $request_url, $data , $header ){
    $context = array(
        "http" => array(
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
            'ignore_errors' => true,
            "content" => $data
        )
    );
    return @file_get_contents($request_url, false, stream_context_create($context));
}
endif;

/**
* GraphQL Api Request 
*
* @param string $query GraphQLのクエリ
* @return string Http Response
*/
if( !function_exists('request_query_to_eccube') ) :
function request_query_to_eccube( $query ){
    $data = json_encode( array('query' => $query) );
    $header = array(
        "Content-Type: application/json",
        "Authorization: Bearer " . get_option( 'eccube_accesstoken' )
    );
    return file_get_contents_to_eccube( get_eccube_api_config( 'api_endpoint' ) , $data , $header );
}
endif;

/**
* アクセストークンの更新
*
* @return string|false 更新できた場合はアクセストークンを返す、それ以外はfalse
*/
if( !function_exists('refresh_eccube_access_token') ) :
function refresh_eccube_access_token(){
    $data = http_build_query( array(
        "client_id" => get_eccube_api_config( 'client_id' ),
        "client_secret" => get_eccube_api_config( 'client_secret' ),
        "grant_type" => "refresh_token",
        "refresh_token" => get_option( 'eccube_refreshtoken' )
    ) );
    $header = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Content-Length: ".strlen($data)
    );
    $response = json_decode( file_get_contents_to_eccube( get_eccube_api_config( 'token_endpoint' ) , $data , $header ) );

    if( $response == false || isset($response->error) ){
        return false;
    }else{
        update_option( 'eccube_accesstoken', $response->access_token );
        update_option( 'eccube_refreshtoken', $response->refresh_token );
        return $response->access_token;
    }
}
endif;

/**
* 生の$productを使いやすいように整形
*
* @param array $product GraphQlで取得した$product
* @return object 整形した$product
*/
if( !function_exists('convert_raw_product_data') ) :
function convert_raw_product_data( $product ){
    if( !$product ) return false;
    $price = [];
    if( is_array($product->ProductClasses) && !empty($product->ProductClasses) ){
        foreach( $product->ProductClasses as $sku ):
            if( $sku->stock || is_null($sku->stock) ){
                $price[] = $sku->price02;
            }
        endforeach;
    }
    // number_format( min( array_filter($price) ), 0, '.', ',');
    // number_format( max( array_filter($price) ), 0, '.', ',');
    $price_min = number_format( min( array_filter($price) ) * 1.1 );
    $price_max = number_format( max( array_filter($price) ) * 1.1 );
    if( $price_min === $price_max ){
        $price = "{$price_min}";
    }else{
        $price = "{$price_min} 〜 {$price_max}";
    }
    $cats = [];
    if( is_array($product->ProductCategories) && !empty($product->ProductCategories) ){
        foreach( $product->ProductCategories as $cat ):
            $cats[] = $cat->Category->name;
        endforeach;
        $category = implode( ',', array_filter($cats) );
    }
    return (object)array(
        'id' => $product->id,
        'name' => $product->name,
        'price' => $price,
        'url' => get_eccube_api_config('eccube_url') . '/products/detail/' . $product->id,
        'thumbnail' => $product->ProductImage[0]->file_name,
        'category' => $category
    );
}
endif;

/**
* IDから商品を取得＆表示するショートコード
*
* @param int $atts[id] EC-CUBEの商品ID
* @return string|false HTML
*/
if( !function_exists('shortcode__eccube_product') ) :
function shortcode__eccube_product( $atts ) {
    $id = false;
    if( isset($atts['id']) ) $id = $atts['id'];
    if( !$id ) return false;

    $query = <<< EOD
    query {
        product(id: {$id}) {
            id
            name
            ProductClasses{
              stock
              price01
              price02
            }
            ProductCategories{
              Category{
                name
              }
            }
            ProductImage{
              file_name
            }
        }
    }
EOD;

    $response = json_decode( request_query_to_eccube($query) );
    if( $response == false || isset($response->error) ){
        return false;
        $accesstoken = refresh_eccube_access_token();
        if( isset( $accesstoken ) ){
            $response = json_decode( request_query_to_eccube($query) );
        }
    }
    if( $response != false && !isset($response->error) ){
        $product = convert_raw_product_data( $response->data->product );
        if( !$product ) return false;
        // return json_encode($product);
        return <<< EOD
        <div class="itemlist" id="item-{$product->id}">
            <div class="thumb"><img src="{$product->thumbnail}" alt="">
            <div class="text">
                <p>{$product->category}</p>
                <h3><a href="{$product->url}">{$product->name}</a></h3>
                <p>{$product->price}円(税込)</p>
            </div>
        </div>
EOD;
    }

}
endif;
add_shortcode( 'eccube_product', 'shortcode__eccube_product' );
