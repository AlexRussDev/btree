<?php

/**
 * Btree example. 
 * 
 * @author Aleksandr Russakov <russakov.aleksandr@gmail.com>
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


abstract class BtreeAbstarctController extends AbstractController
{
    public function validateRequest(Request $request){
        $guid = $request->get('guid');
        if (!$guid) {
            throw New \InvalidArgumentException('Please make sure that destination node guid is present...');
        }

        $nodeTitle = $request->get('title');
        if (!$nodeTitle) {
            throw New \InvalidArgumentException('Please make sure that node title is present...');
        }
    }

    function getRoutines(): array
    {
        $sql = [];

        $sql[] = "CREATE TABLE IF NOT EXISTS `tree` (
          `_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `guid` varchar(11) NOT NULL,
          `parentguid` varchar(11) DEFAULT NULL,
          `name` varchar(100) NOT NULL DEFAULT '',
          `l` int(11) DEFAULT NULL,
          `r` int(11) DEFAULT NULL,
          PRIMARY KEY (`_id`),
          KEY `guid_ix` (`guid`),
          KEY `l_ix` (`l`),
          KEY `r_ix` (`r`),
          KEY `parentguid_ix` (`parentguid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4";

        $sql[] = "DROP PROCEDURE IF EXISTS `ADDTREENODE`;
        CREATE DEFINER=`root`@`localhost` PROCEDURE `ADDTREENODE`(IN _targetGuid CHAR(11), IN _newGuid CHAR(11), IN _name CHAR(100), INOUT RESULT INT(1))
        proc: BEGIN	  
          SET RESULT := 0;  	
          START TRANSACTION;
            IF _targetGuid <> '' THEN
                SELECT @Count := COUNT(*) FROM `tree` WHERE `guid` = _targetGuid;
                IF @Count = 0 THEN 
                    ROLLBACK;
                    LEAVE proc;
                END IF;  

                SELECT @l := l, @r := r FROM `tree` WHERE `guid` = _targetGuid FOR UPDATE;

                UPDATE `tree` SET r = r + 2 WHERE r > @l;
                UPDATE `tree` SET l = l + 2 WHERE l > @l;

                INSERT INTO `tree` (`guid`, `parentguid`, `name`, l, r) VALUES (_newGuid, _targetGuid, _name, @l + 1, @l + 2);
            ELSE
                INSERT INTO `tree` (`guid`,  `name`, l, r) VALUES (_newGuid, _name, 1, 2);
            END IF; 
          COMMIT;
          SET RESULT := 1;
        END;";

        $sql[] = "DROP PROCEDURE IF EXISTS `ADDUNIQUETREENODE`;
        CREATE DEFINER=`root`@`localhost` PROCEDURE `ADDUNIQUETREENODE`(IN _targetGuid CHAR(11), IN _newGuid CHAR(11), IN _name CHAR(100), INOUT RESULT INT(1))
        proc: BEGIN 
          SET RESULT := 0; 
          SELECT @Count := COUNT(*) FROM `tree` WHERE `guid` = _newGuid;
          IF @Count > 0 THEN 
            ROLLBACK;
            LEAVE proc;
          END IF;   

          CALL ADDTREENODE(_targetGuid, _newGuid,_name, RESULT);
        END;";

        $sql[] = "DROP PROCEDURE IF EXISTS `REMOVETREENODE`;
        CREATE DEFINER=`root`@`localhost` PROCEDURE `REMOVETREENODE`(IN _targetGuid CHAR(11))
        BEGIN
        START TRANSACTION;
            SELECT @l := l, @r := r, @range := r - l + 1 FROM `tree` WHERE `guid` = _targetGuid FOR UPDATE;
            DELETE FROM `tree` WHERE l BETWEEN @l AND @r;
            UPDATE `tree` SET r = r - @range WHERE r > @r;
            UPDATE `tree` SET l = l - @range WHERE l > @r;      
        COMMIT;
        END;";

        return $sql;
    }
  
}
