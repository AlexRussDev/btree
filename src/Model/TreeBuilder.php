<?php

namespace App\Model;
use Doctrine\DBAL\Connection;


class TreeBuilder
{
    private Connection $_connection;
    private Array $_nodeInfo = ['parent'=> [], 'sibling' => [], 'children' => [], 'null'=> []];


    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Returns full tree data
     *
     * @return array
     */
    public function GetFullTreeData(string $guid = "ROOTGUID"): array
    {
        return $this->GetTreeNodeData($guid);
    }

    /**
     * Returns tree node data including childrens
     *
     * @param string $nodeTitle
     * @return array
     */
    public function GetTreeNodeData(string $guid): array
    {
        $sql = 'SELECT node.*, IF(node.l + 1 = node.r, 0, 1) hasChildren FROM tree AS node, tree AS parent WHERE node.l BETWEEN parent.l AND parent.r AND parent.guid = ? ORDER BY node.r DESC';
        $stmt = $this->_connection->prepare($sql);
        $stmt->bindValue(1, $guid);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Returns true if operation succeeded
     *
     * @param string $targetGuid
     * @param string $caption
     * @return boolean
     */
    public function AddTreeNode(string $targetGuid, string $nodeGuid, string $caption, bool $allowDuplicate = false): bool
    {
        $sql = $allowDuplicate ? 'CALL ADDTREENODE(?, ?, ?, @result)' : 'CALL ADDUNIQUETREENODE(?, ?, ?, @result)';
        $stmt = $this->_connection->prepare($sql);
        $stmt->bindValue(1, $targetGuid);
        $stmt->bindValue(2,  $nodeGuid);
        $stmt->bindValue(3, $caption);
        $stmt->executeQuery();
        unset($stmt);

       return (int)$this->_connection->prepare('SELECT @result')->executeQuery()->fetchOne() > 0;
    }

    /**
     * Returns TreeBuilder object
     *
     * @param string $nodeName
     * @return array
     */
    public function RetrieveNodeInfo(string $guid): TreeBuilder
    {
        if ($data = $this->GetTreeNodeData($guid)){
            $this->_nodeInfo['null'] =  array_slice($data, 1, count($data));
            foreach($data as $row){
               
                //fetch parents
                $sql = 'SELECT `guid`, `name` FROM `tree` WHERE `guid` = ? ORDER by `name` ASC';
                $stmt = $this->_connection->prepare($sql);
                $stmt->bindValue(1, $row['parentguid']);
                $parent = $stmt->executeQuery()->fetchAssociative();
                unset($stmt);

                //fetch siblings
                if ($parent){
                    $sql = 'SELECT `guid`, `name` FROM `tree` WHERE `parentguid` = ? AND `guid` <> ? ORDER by `name` ASC';
                    $stmt = $this->_connection->prepare($sql);
                    $stmt->bindValue(1, $parent['guid']);
                    $stmt->bindValue(2, $row['guid']);
                    $this->_nodeInfo['sibling'][] = $stmt->executeQuery()->fetchAssociative();
                    unset($stmt);
                }
                $this->_nodeInfo['parent'][] = $parent;
               
                //fetch childrens
                if ((int)$row['hasChildren'] > 0){
                    $sql = 'SELECT `guid`, `name` FROM `tree` WHERE `l` > ? AND `r` < ? ORDER by `name` ASC';
                    $stmt = $this->_connection->prepare($sql);
                    $stmt->bindValue(1, $row['l']);
                    $stmt->bindValue(2, $row['r']);
                    $this->_nodeInfo['children'][] = $stmt->executeQuery()->fetchAssociative();
                    unset($stmt);
                }

            }
        }
        return $this;
    }

    /**
     * Returns flat node info
     *
     * @return string
     */
    public function asFlat(): string{
        
        $result = [];
        foreach($this->_nodeInfo as $relation => $data){
           foreach($data as $row){
            $result[] = ['property' => $row['name'], 'relation' => $relation === 'null' ? null : $relation]; 
           }
        }

        return json_encode($result);
    }
}
