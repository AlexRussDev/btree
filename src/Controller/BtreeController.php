<?php

/**
 * Btree example. 
 * 
 * @author Aleksandr Russakov <russakov.aleksandr@gmail.com>
 */

namespace App\Controller;

use Doctrine\ORM\UnexpectedResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use App\Model\TreeBuilder;
use Symfony\Component\Uid\Ulid;

class BtreeController extends BtreeAbstarctController
{
    private TreeBuilder $_TreeBuilder;

    public function __construct(Connection $connection)
    {
        $this->_TreeBuilder = new TreeBuilder($connection);
    }


    #[Route('/api/btree', name: 'btree_gettree', methods: ['GET'])]
    function getTree(): Response
    {
        return $this->json($this->_TreeBuilder->GetFullTreeData());
    }


    #[Route('/api/btree/getnodeinfo', name: 'btree_getnodeinfo', methods: ['POST'])]
    function getNodeInfo(Request $request): Response
    {
        try {
            $guid = $request->get('guid');
            if (!$guid) {
                throw New \InvalidArgumentException('Please make sure that node guid is present...');
            }
        
            return $this->json($this->_TreeBuilder->RetrieveNodeInfo($guid)->asFlat());
        } catch (\Exception $e){
            return $this->json(['error' => 'Aborting... ' . $e->getMessage()], 400);
        }
    }


    #[Route('/api/btree/addnode', name: 'btree_addnode', methods: ['POST'])]
    function addNode(Request $request): Response
    {
        try {
            $this->validateRequest($request);
            if (!$this->_TreeBuilder->AddTreeNode($request->get('guid'), (new Ulid)->toBase32(), $request->get('title'), false))
                throw new UnexpectedResultException('Unable to create a node for some reason. Possibly duplicate node guid was provided.');
            
            return new Response("OK");
        } catch (\Exception $e){
            return $this->json(['error' => 'Aborting... ' . $e->getMessage()], 400);
        }
    }


    #[Route('/api/btree/addsharednode', name: 'btree_addsharednode', methods: ['POST'])]
    function addSharedNode(Request $request): Response
    {
        try {
            $this->validateRequest($request);
            if (!$this->_TreeBuilder->AddTreeNode($request->get('guid'), $request->get('sharedguid'), $request->get('title'), true))
                throw new UnexpectedResultException('Unable to create a shared node for some reason.');
            
            return new Response("OK");
        } catch (\Exception $e){
            return $this->json(['error' => 'Aborting... ' . $e->getMessage()], 400);
        }
    }


    #[Route('/api/createroutines', name: 'btree_createroutines', methods: ['GET'])]
    function createRoutines(Connection $connection): Response
    {
        $connection->prepare(implode(';', $this->getRoutines()))->executeQuery();
        return new Response("OK");
    }
}
