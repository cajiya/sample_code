if( !function_exists('eccube_wp_head_style') ) :
function eccube_wp_head_style( $content ){
?>
<link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
.ec-cartNaviWrap {
    position: relative;
}

.ec-cartNavi {
    display: flex;
    background: darkgray;
    padding: 10px 15px;
}

.ec-cartNaviNull,
.ec-cartNaviIsset {
    position: absolute;
    word-break: keep-all;
    right: 0;
    top: 100%;
    padding: 15px;
    font-size: 0.85rem;
    background: white;
    color: #000;
    pointer-events: none;
    transition: all 0.5s;
    transform: translate(0px, 15px);
    opacity: 0;
}

.ec-cartNavi__icon {
    font-size: 1rem;
    line-height: 1rem;
    border-right: 1px solid;
    padding-right: 1em;
    margin-right: 1em;
}

.ec-cartNavi__label {
    line-height: 1rem;
}

.ec-cartNavi__badge {
    font-weight: 400;
}

.ec-cartNaviNull p {
    margin: 0;
}

.ec-cartNaviWrap:hover .ec-cartNaviNull,
.ec-cartNaviWrap:hover .ec-cartNaviIsset {
    transform: translate(0px, 0px);
    opacity: 1;
}
	
.ec-cartNaviIsset__cart {
    display: flex;
}

.ec-cartNaviIsset__cartImage {
    width: 100px;
    margin-right: 15px;
}

.ec-cartNaviIsset__cartImage img {
    max-width: 100%;
}

.ec-cartNaviIsset__action {
    display: none;
}

.ec-cartNaviIsset__cartContentPrice {
    display: flex;
    justify-content: flex-end;
}

.ec-cartNaviIsset__cartContentNumber {
    text-align: right;
}
</style>
<?php
}
endif;
add_action( 'wp_head', 'eccube_wp_head_style' , 10 , 1 );
