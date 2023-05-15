<?php

/**
 * Btree example. 
 * 
 * @author Aleksandr Russakov <russakov.aleksandr@gmail.com>
 */

namespace App\Controller;

use \App\Common\Btree as BtreeCommon;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BtreeController extends AbstractController
{
    const BTREE_ID = 'BTREEGUID';


    #[Route('/api/btree', name: 'btree_gettree', methods: ['GET'])]
    function getTree(EntityManagerInterface $entityManager): Response
    {
        return $this->json(BtreeCommon::getData($entityManager));
    }


    #[Route('/api/btree/getpropinfo/{guid}', name: 'btree_getpropinfo', methods: ['GET'])]
    function getPropInfo(EntityManagerInterface $entityManager, Request $request): Response
    {
        $nodeData = BtreeCommon::retrieveNodeInfoAsFlat($request->get('guid'), BtreeCommon::getData($entityManager));
        if (!$nodeData) {
            return $this->json(['error' => 'Aborting... Please make sure that node guid does exists in tree structure...']);
        }

        return $this->json($nodeData);
    }

    #[Route('/api/btree/addnode/{guid}', name: 'btree_addnode', methods: ['POST'])]
    function addNode(EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($newNodeData = json_decode($request->get('nodedata'), true)) {
            $treeData = BtreeCommon::getNewTreeStructure($entityManager, $request->get('guid'), $newNodeData);
            if (!$treeData)
                return $this->json(['error' => 'Aborting... Unable to insert a node']);
            $treeData = BtreeCommon::updateTreeData($entityManager, $treeData);
            return $this->json($treeData);
        }
        return $this->json(['error' => 'Aborting... Something went wrong...']);
    }
}
