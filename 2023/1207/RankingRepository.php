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
     * @param int $limit 取得する商品の最大数
     * @param int $recentLimit 取得する最新のOrderItemの数
     * @return array 商品の売上ランキングデータ
     */
    public function getProductsRanking($limit = 10, $recentLimit = 100)
    {
        // 最新のOrderItemのIDを取得するためのサブクエリ
        $subQb = $this->createQueryBuilder('oi2')
            ->select('oi2.id')
            ->orderBy('oi2.id', 'DESC')
            ->setMaxResults($recentLimit);

        // メインクエリの作成
        $qb = $this->createQueryBuilder('oi');
        $qb->select('IDENTITY(oi.Product) as product_id, SUM(oi.quantity * oi.price) as total_quantity')
            ->join('oi.Product', 'p')
            ->join('oi.OrderItemType', 'oit')
            ->where('oit.id = :orderItemTypeProduct')
            ->andWhere($qb->expr()->in('oi.id', $subQb->getDQL())) // サブクエリの結果に含まれるIDのみを対象とする
            ->groupBy('oi.Product')
            ->orderBy('total_quantity', 'DESC') // 合計売上高で降順に並べ替え
            ->setMaxResults($limit) // 結果の上限を設定
            ->setParameter('orderItemTypeProduct', OrderItemType::PRODUCT);

        // クエリの実行と結果の取得
        return $qb->getQuery()->getResult();
    }
}
