<?php
// /app/Customize/EventListener/TwigAddParamsEventListener.php

namespace Customize\EventListener;

use Customize\Repository\RankingRepository;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigAddParamsEventListener implements EventSubscriberInterface
{
    /**
     * コンストラクタ
     * 
     * @param RankingRepository $rankingRepository 売上ランキングのリポジトリ
     * @param ProductRepository $productRepository 商品リポジトリ
     */
    public function __construct(
        RankingRepository $rankingRepository,
        ProductRepository $productRepository
    ) {
        $this->rankingRepository = $rankingRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * テンプレートイベントのリスナー
     * 売上ランキングをテンプレートに渡すための処理
     * 
     * @param TemplateEvent $event テンプレートイベント
     */
    public function onRanking(TemplateEvent $event)
    {
        // 現在のテンプレートのパラメータを取得
        $parameters = $event->getParameters();

        // 売上ランキングのデータを取得
        $rankings = $this->rankingRepository->getProductsRanking();

        $result = null;
        if (!empty($rankings)) {
            // ランキングデータから商品IDを抽出
            $product_ids = array_column($rankings, 'product_id');
            // 商品IDに基づいて商品情報を取得
            $Products = $this->productRepository->findBy(['id' => $product_ids]);
        }

        // テンプレートにランキング商品を渡す
        $parameters['rankingProducts'] = $Products;
        $event->setParameters($parameters);
    }

    /**
     * イベントサブスクライバーを定義
     * 
     * @return array イベントとメソッドのマッピング
     */
    public static function getSubscribedEvents()
    {
        return [
            // 'Block/ranking.twig'テンプレートに対するイベントリスナーを登録
            'Block/ranking.twig' => 'onRanking',
        ];
    }
}
