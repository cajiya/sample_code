<?php
// /app/Customize/EventListener/ProductDetailListener.php

namespace Customize\EventListener;

use Eccube\Repository\ProductRepository;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProductDetailListener implements EventSubscriberInterface
{
    private $session;
    protected $productRepository;

    public function __construct(
        SessionInterface $session,
        ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->get('_route') === 'product_detail') {
            $productId = $request->attributes->get('id');
            $Product = $this->productRepository->findOneBy(['id' => $productId ]);

            // セッションから閲覧履歴を取得
            $history = $this->session->get('product_history', []);

            // 閲覧履歴に同じ商品が存在するか確認
            foreach ($history as $key => $storedProduct) {
                if ($storedProduct->getId() === $Product->getId()) {
                    // 同じ商品を見つけたら削除
                    unset($history[$key]);
                    break;
                }
            }

            // 商品を履歴の先頭に追加
            array_unshift($history, $Product);

            // 履歴を最大10件に制限
            $history = array_slice($history, 0, 10);

            // セッションに履歴を保存
            $this->session->set('product_history', $history);
        }
    }
}
