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
   * Return a slideatlasItem dao based on an itemId.
   */
  public function getByItemId($itemId)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    $dao = $this->initDao('Item', $row, 'slideatlas');
    return $dao;
    }

  /** get All based on the item type*/
  function getAllByItemType($itemType)
    {
    $sql = $this->database->select()
                  ->where('item_type = ?', $itemType)
                  ->order(array('item_id DESC'));
    $rowset = $this->database->fetchAll($sql);
    $results = array();
    foreach($rowset as $row)
      {
      $results[] = $this->initDao('Item', $row, 'slideatlas');
      }
    return $results;
    }

  /**
   * Update a slideatlasItem type.
   */
  public function updateItemtype($itemId, $itemType)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    if($row)
      {
      $this->database->getDB()->update('slideatlas_item',
                                     array('item_type' => $itemType),
                                     array('item_id = ?' => $itemId));
      $updatedrow = $this->database->fetchRow($sql);
      $dao = $this->initDao('Item', $updatedrow, 'slideatlas');
      }

    return $dao;
    }

  /**
   * Update a slideatlasItem order.
   */
  public function updateItemOrder($itemId, $itemOrder)
    {
    $sql = $this->database->select()->where('item_id = ?', $itemId);
    $row = $this->database->fetchRow($sql);
    if($row)
      {
      $this->database->getDB()->update('slideatlas_item',
                                     array('item_order' => $itemOrder),
                                     array('item_id = ?' => $itemId));
      $updatedrow = $this->database->fetchRow($sql);
      $dao = $this->initDao('Item', $updatedrow, 'slideatlas');
      }

    return $dao;
    }

}
