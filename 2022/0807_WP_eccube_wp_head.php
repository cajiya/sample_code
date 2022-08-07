if( !function_exists('eccube_wp_head') ) :
function eccube_wp_head( $content ){
?>
<script>
window.addEventListener('DOMContentLoaded',()=>{
    var CartElm = document.querySelector('.js-ec-cart');
    if( CartElm ){
        var CartRequest = new XMLHttpRequest();
        CartRequest.open("GET", "/$SHOP/block/cart"); // $SHOP は EC-CUBEのインストールPATHに変更してください
        CartRequest.addEventListener("load", function(){
            CartElm.innerHTML = this.responseText;
        });
        CartRequest.send();
    }
})
</script>
<?php
}
endif;
add_action( 'wp_head', 'eccube_wp_head' , 10 , 1 );
