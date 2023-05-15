<?php

/**
 * Btree Helpers. 
 * 
 * @author Aleksandr Russakov <russakov.aleksandr@gmail.com>
 */


namespace App\Common;

use Doctrine\ORM\EntityManagerInterface;


class Btree
{
    const BTREE_ID = 'BTREEGUID';

    const RTYPE_DEFAULT = 'DEFAULT';
    const RTYPE_FLAT = 'FLAT';


    /**
     * Tree sample data
     *
     * @return array
     */
    public static function getSampleData(): array
    {
        return [
            'Building Complex' =>
            [
                'Building 1' => [
                    'PS11' => 'Parking Space 1',
                    'PS12' => 'Parking Space 2',
                    'shared:SHAREDXXXGUID' => 'Shared Space XXX',
                ],
                'Building 2' => [
                    'PS21' => 'Parking Space 2'
                ],
                'Building 3' => [
                    'PS31' => 'Parking Space 3',
                    'shared:SHAREDXXXGUID' => 'Shared Space XXX',
                ],
            ]
        ];
    }


    /**
     * Returns tree data from database. If doesn't exists - it will populate data first and only then return the value.
     *
     * @param EntityManagerInterface $entityManager
     * @return array
     */
    public static function getData(EntityManagerInterface $entityManager): array
    {
        $data = $entityManager->getRepository(\App\Entity\Btree::class)->find('BTREEGUID');
        if (!$data) {
            $Btree = \App\Model\Factory\BtreeFactory::create(self::BTREE_ID, self::getSampleData());
            $entityManager->persist($Btree);
            $entityManager->flush();
            $data = $entityManager->getRepository(\App\Entity\Btree::class)->find('BTREEGUID');
        }

        return $data->getContent();
    }


    /**
     * Updates tree data in db
     *
     * @param EntityManagerInterface $entityManager
     * @param array $data
     * @return array
     */
    public static function updateTreeData(EntityManagerInterface $entityManager, array $data): array
    {
        $entityManager->find(\App\Entity\Btree::class, self::BTREE_ID)->setContent($data);
        $entityManager->flush();
        $data = $entityManager->getRepository(\App\Entity\Btree::class)->find('BTREEGUID');

        return $data->getContent();
    }


    /**
     * Retrieves tree detailed information for selected node. Universal, does support any level of depths.
     *
     * @param string $nodeID
     * @param array $nodeData
     * @param array $outPut
     * @return array
     */
    public static function retrieveNodeInfo(string $nodeID, array $nodeData = [], &$outPut = [], $type = self::RTYPE_DEFAULT): array
    {
        if ($type === self::RTYPE_DEFAULT) {
            if (key($nodeData) === $nodeID) {
                $outPut[$nodeID][] = [
                    'parents' => [],
                    'siblings' => [],
                    'childrens' => $nodeData[$nodeID]
                ];
                return $outPut;
            }
        }

        $result = ['hasMatch' => false, 'parents' => [], 'childrens' => [], 'siblings' => []];
        foreach ($nodeData as $key => $node) {
            if (strtoupper($key) === strtoupper($nodeID)) {
                $result['hasMatch'] = true;
                $result['siblings'] = [];
                foreach ($nodeData as $key => $value) {
                    if (strtoupper($key) !== strtoupper($nodeID)) $result['siblings'][$key] = $value;
                }
                $result['childrens'] = is_array($node) ? $node : [];
            } else {
                if (is_array($node)) {
                    $matcherResult = self::retrieveNodeInfo($nodeID, $node, $outPut, $type);
                    if ($matcherResult['hasMatch']) {

                        $parents = [];
                        foreach ($nodeData as $ix => $value) {
                            if (strtoupper($ix) === strtoupper($key)) $parents[$ix] = $value;
                        }

                        $outPut[$nodeID][] = [
                            'parents' => $parents,
                            'siblings' => is_array($matcherResult['siblings']) ? $matcherResult['siblings'] : [],
                            'childrens' => $matcherResult['childrens']
                        ];
                    }
                }
            }
        }

        return $result;
    }


    /**
     * Retrieves node detailed information about node relations and returns sorted flat array.
     * 
     *
     * @param string $guid
     * @return array
     */
    public static function retrieveNodeInfoAsFlat(string $guid, array $treeData = []): array
    {
        self::retrieveNodeInfo($guid, $treeData, $nodeData, self::RTYPE_FLAT);

        $result[$guid] = [];
        if (!$nodeData) {
            return $result;
        }

        foreach ($nodeData as $nodes) {
            foreach ($nodes as $key => $props) {
                foreach (array_keys($props['parents']) as $ix) {
                    $result[$guid][$key][$ix] = ['relation' => 'parent'];
                }

                foreach (array_keys($props['siblings']) as $ix) {
                    $type = null;
                    if (strpos($ix, ':'))
                        list($type, $ix) = explode(':', $ix);

                    $result[$guid][$key][$ix] = ['relation' => ($type === 'shared') ? null : 'sibling'];
                }

                if (!empty($result[$guid]))
                    ksort($result[$guid]);

                if (!empty($result[$guid][$key]))
                    ksort($result[$guid][$key]);
            }
        }

        return $result;
    }


    /**
     * Inserts node to any level of three. For properties without childrens transforms an existing property into array.
     *
     * @param string $nodeID
     * @param array $newNodeData
     * @param array $nodeData
     * @return array
     */
    public static function getNewTreeStructure(EntityManagerInterface $entityManager, string $nodeID, array $newNodeData, array &$nodeData = []): array
    {
        if (!$nodeData) {
            $nodeData = self::getData($entityManager);
        }

        foreach ($nodeData as $key => &$node) {
            if (strtoupper($key) === strtoupper($nodeID)) {
                $newNodeKey = array_keys($newNodeData)[0];
                $newNodeValue = array_values($newNodeData)[0];

                if (is_array($node)) {
                    $node[$newNodeKey] =  $newNodeValue;
                } else {
                    $newNodeDef = [$node => $node];
                    $nodeData[$key] = [];
                    $nodeData[$key] = $newNodeDef + [$newNodeKey => $newNodeValue];
                }
            } else {
                if (is_array($node)) {
                    self::getNewTreeStructure($entityManager, $nodeID, $newNodeData, $node);
                }
            }
        }

        return $nodeData;
    }
}
