<?php
/*=========================================================================
MIDAS Server
Copyright (c) Kitware SAS. 20 rue de la Villette. All rights reserved.
69328 Lyon, FRANCE.

See Copyright.txt for details.
This software is distributed WITHOUT ANY WARRANTY; without even
the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE.  See the above copyright notices for more information.
=========================================================================*/
require_once BASE_PATH . '/modules/slideatlas/models/base/ItemModelBase.php';


/** Slideatlas_ItemModel */
class Slideatlas_ItemModel extends Slideatlas_ItemModelBase {

  /**
   * Return an slideatlasItem dao based on an itemId.
   */
  public function getByItemId($itemId)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    $dao = $this->initDao('Item', $row, 'slideatals');
    $return = $dao;
    }

  /**
   * lists the set of items for a user, based on which communities
   * they are a member of, if a status is provided, will filter by status.
   * @param UserDao $userDao
   * @param type $status
   * @return type
   */
  function findAvailableItems($userDao, $status = null)
    {
    if(!$userDao)
      {
      throw new Exception('You must be logged in to create a slideatlas');
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Exception("userDao should be a valid instance.");
      }

    $userId = $userDao->getUserId();
    $membersGroupName = "Members";
    $sql = $this->database->select()->setIntegrityCheck(false);
    $sql->from(array('cc' => 'slideatlas_slideatlas'));
    $sql->join(array('g' => 'group'), 'cc.community_id=g.community_id');
    $sql->join(array('u2g' => 'user2group'), 'g.group_id=u2g.group_id');
    $sql->join(array('vd' => 'validation_dashboard'), 'validation_dashboard_id=dashboard_id');
    $sql->where('g.name=?', $membersGroupName);
    $sql->where('u2g.user_id=?', $userId);
    if($status)
      {
      $sql->where('cc.status=?', $status);
      }

    $rowset = $this->database->fetchAll($sql);
    $return = array();
    foreach($rowset as $row)
      {
      $slideatlasId = $row['slideatlas_id'];
      $name = $row['name'];
      $description = $row['description'];
      $return[$slideatlasId] = array('name' => $name, 'description' => $description);
      }
    return $return;
    }

}
