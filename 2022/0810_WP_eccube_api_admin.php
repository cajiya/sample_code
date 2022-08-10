<?php
if( !function_exists('add_eccube_api_menu_page_function') ) :
function add_eccube_api_menu_page_function( $name ){

    $the_page_url  = admin_url( "admin.php?page=".$_GET["page"] );
    $state = wp_create_nonce( 'eccube-api-nonce' );
    $client_id = get_eccube_api_config( 'client_id' );
    $client_secret = get_eccube_api_config( 'client_secret' );
    $token_endpoint = get_eccube_api_config( 'token_endpoint' );
    $authorization_endpoint = get_eccube_api_config( 'authorization_endpoint' );
    $accesstoken = get_option( 'eccube_accesstoken' );
    $refreshtoken = get_option( 'eccube_refreshtoken' );
    $verify_nonce = false;
    $notice = "";

    if( isset($_REQUEST['state']) && isset($_REQUEST['code']) ){
        $state = htmlspecialchars( $_REQUEST['state'] );
        $code = htmlspecialchars( $_REQUEST['code'] );
        $verify_nonce = wp_verify_nonce( $state, 'eccube-api-nonce' );
    }
    if( $verify_nonce == 1 ){

        $data = http_build_query( array(
            "grant_type" => "authorization_code",
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri" => $the_page_url,
            "code" => $code
        ) );
        $header = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: ".strlen($data)
        );
        $response = json_decode( file_get_contents_to_eccube( $token_endpoint, $data , $header ) );
        if( $response == false || isset($response->error) ){
            $notice .= '<div class="notice notice-error"><p>トークンの取得時にエラーが発生</p></div>';
        }else{
            $notice .= '<div class="notice notice-success"><p>トークンの取得成功</p></div>';
            update_option( 'eccube_accesstoken', $response->access_token );
            update_option( 'eccube_refreshtoken', $response->refresh_token );
            $accesstoken = $response->access_token;
            $refreshtoken = $response->refresh_token;
        }
    }
    if( !empty($_REQUEST['mode']) && $_REQUEST['mode'] == "refreshtoken" ){
        $data = http_build_query( array(
            "client_id" => $client_id,
            "client_secret" => $client_secret,
            "grant_type" => "refresh_token",
            "refresh_token" => $refreshtoken
        ) );
        $header = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: ".strlen($data)
        );
        $response = json_decode( file_get_contents_to_eccube($token_endpoint ,$data, $header ) );
        if( $response == false || isset($response->error) ){
            $notice .= '<div class="notice notice-error"><p>トークンの更新時にエラーが発生</p></div>';
        }else{
            $notice .= '<div class="notice notice-success"><p>トークンの更新成功</p></div>';
            update_option( 'eccube_accesstoken', $response->access_token );
            update_option( 'eccube_refreshtoken', $response->refresh_token );
            $accesstoken = $response->access_token;
            $refreshtoken = $response->refresh_token;
        }
    }
    if( !empty($_REQUEST['mode']) && $_REQUEST['mode'] == "deletetoken" ){
        $notice .= '<div class="notice notice-success"><p>トークンを削除しました</p></div>';
        delete_option( 'eccube_accesstoken' );
        delete_option( 'eccube_refreshtoken' );
        $accesstoken = '';
        $refreshtoken = '';
    }
    ?>
    <div class="wrap">
        <h1>EC-CUBE トークン設定</h1>
        <?php echo $notice; ?>
        <form action="<?php echo $authorization_endpoint; ?>" method="GET">
            <input type="hidden" name="response_type" value="code">
            <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
            <input type="hidden" name="redirect_uri" value="<?php echo $the_page_url; ?>">
            <input type="hidden" name="scope" value="read">
            <input type="hidden" name="state" value="<?php echo $state; ?>">
            <table class="form-table">
                <tr>
                    <th>アクセストークン</th>
                    <td>
                        <?php if( !empty($accesstoken) ): ?>
                            <textarea name="" id="" cols="100" rows="15"><?php echo $accesstoken; ?></textarea>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>シークレットトークン</th>
                    <td>
                        <?php if( !empty($refreshtoken) ): ?>
                            <textarea name="" id="" cols="100" rows="15"><?php echo $refreshtoken; ?></textarea>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <p><input type="submit" class="button button-primary" value="EC-CUBEとのAPI連携初期設定を始める"></p>
            <?php if( !empty($refreshtoken) ): ?>
                <p><a href="<?php echo $the_page_url; ?>&mode=refreshtoken" class="button">トークンを更新する</a></p>
            <?php endif; ?>
            <?php if( !empty($accesstoken) || !empty($refreshtoken) ): ?>
                <p><a href="<?php echo $the_page_url; ?>&mode=deletetoken" class="button">トークンを削除する</a></p>
            <?php endif; ?>

        </form>

    </div>
<?php
}
endif;

if( !function_exists('add_eccube_api_menu_page') ) :
function add_eccube_api_menu_page() {
  add_menu_page( 'EC-CUBE トークン設定', 'EC-CUBE Token', 'manage_options', 'ec-cube-token.php', 'add_eccube_api_menu_page_function', 'dashicons-cart', 90 );
}
endif;
add_action( 'admin_menu', 'add_eccube_api_menu_page' );

