<?php
// /app/Customize/Repository/RankingRepository.php

namespace Customize\Repository;

use Eccube\Entity\OrderItem;
use Eccube\Repository\AbstractRepository;
use Eccube\Entity\Master\OrderItemType;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;

class RankingRepository extends AbstractRepository
{
    /**
     * コンストラクタ
     * 
     * @param RegistryInterface $registry マネージャーレジストリ
     */
    public function __construct(RegistryInterface $registry)
    {
        // OrderItemエンティティに基づいたリポジトリの初期化
        parent::__construct($registry, OrderItem::class);
    }
    
    /**
     * 売上ランキングを取得する
     * 
     * @param int $recentLimit 取得する最新のOrderItemの数
     * @return array 商品の売上ランキングデータ
     */
    public function getProductsRanking( $recentLimit = 7)
    {
        // ProductClass毎の売上高を取得するためのクエリ
        $qb = $this->createQueryBuilder('oi')
            ->select(' IDENTITY(oi.ProductClass) as product_class_id, IDENTITY(oi.Product) as product_id , SUM(oi.quantity) * (oi.price + oi.tax) as total_price')
            ->join('oi.OrderItemType', 'oit')
            ->join('oi.Order', 'o')
            ->where('oit.id = :orderItemTypeProduct')
            ->andWhere('o.order_date > :recent_date')
            ->groupBy('oi.ProductClass')
            ->orderBy('total_price', 'DESC')
            ->setParameter('orderItemTypeProduct', OrderItemType::PRODUCT)
            ->setParameter('recent_date', new \DateTime("-{$recentLimit} days") );

        // クエリの実行と結果の取得
        return $qb->getQuery()->getResult();
    }
}
