<?php

namespace App\Model\Factory;

use App\Entity\Btree;

class BtreeFactory
{
    public static function create(string $id, array $content): Btree
    {
        $Btree = new Btree();
        $Btree->setId($id);
        $Btree->setContent($content);
        return $Btree;
    }
}
