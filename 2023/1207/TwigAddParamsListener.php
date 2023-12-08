<?php
// /app/Customize/EventListener/TwigAddParamsListener.php

namespace Customize\EventListener;

use Customize\Repository\RankingRepository;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigAddParamsListener implements EventSubscriberInterface
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
        $ProductClasses = $this->rankingRepository->getProductsRanking();
        $event->setParameters($parameters);

        $sums = [];
        $Products = [];
        if (!empty($ProductClasses)) {

            foreach ($ProductClasses as $item) {
                if (!isset($sums[$item['product_id']])) {
                    $sums[$item['product_id']] = 0;
                }
                $sums[$item['product_id']] += $item['total_price'];
            }
            // 合計をtotal_priceで並び替え
            arsort($sums);

            // 結果を表示（必要に応じて）
            foreach ($sums as $productId => $totalPrice) {
                // 商品IDに基づいて商品情報を取得
                $Products[] = current($this->productRepository->findBy(['id' => $productId]));
            }
        }

        // // テンプレートにランキング商品を渡す
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
