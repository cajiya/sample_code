<?php 

namespace Customize\Twig\Extension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WpExtension extends AbstractExtension
{
    // WordPressのインストール先URLを設定してください。
    private $wpInstallUrl = 'https://example.com/media/';

    public function getFunctions()
    {
        return [
            new TwigFunction('wp_posts', [$this, 'getPosts']),
            new TwigFunction('wp_categories', [$this, 'getCategories']),
        ];
    }

    public function getCategories(){
        $response = @file_get_contents( $this->wpInstallUrl . 'wp-json/wp/v2/categories' );
        if( $response != false ){
            $data = json_decode( strip_tags($response) );
        }
        return $data;
    }

    public function getPosts( $category = null, $search = null , $per_page = null ){
        $query = [];
        if( $category !== null ) $query['category'] = $category;
        if( $search !== null ) $query['search'] = $search;
        if( $per_page !== null ) $query['per_page'] = $per_page;
        $response = @file_get_contents( $this->wpInstallUrl . 'wp-json/wp/v2/posts?' . http_build_query($query) );
        if( $response != false ){
            $data = json_decode( strip_tags($response) );
        }
        return $data;
    }
}

