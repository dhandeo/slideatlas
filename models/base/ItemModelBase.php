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
/** SlideatlasModel Base class */
abstract class Slideatlas_ItemModelBase extends Slideatlas_AppModel {

  /**
   * constructor
   */
  public function __construct()
    {
    parent::__construct();
    $this->_name = 'slideatlas_item';
    $this->_key = 'slideatlas_id';
    $this->_daoName = 'ItemDao';

    $this->_mainData = array(
      'slideatlas_id' => array('type' => MIDAS_DATA),
      'item_id' => array('type' => MIDAS_DATA),
      'item_type' => array('type' => MIDAS_DATA),
      'item_order' => array('type' => MIDAS_DATA),
      'item' =>  array('type' => MIDAS_ONE_TO_ONE,
                        'model' => 'item',
                        'parent_column' => 'item_id',
                        'child_column' => 'item_id')

       );
    $this->initialize(); // required
    }

  abstract public function getByItemId($itemId);
  abstract function getAllByItemType($itemType);
  abstract public function updateItemType($itemId, $itemType);
  abstract public function updateItemOrder($itemId, $itemOrder);


  /** Create a new slideatals item
   * @param string $item_id
   * @param int $item_order
   * @param int $item_Type
   * @return The slideatlas item object that was created
   */
  function createItem($itemId, $itemType, $itemOrder = 0)
    {
    if(($itemType != SLIDEATLAS_RAW_IMAGE) && ($itemType != SLIDEATLAS_DICED_IMAGE) )
      {
      throw new Zend_Exception('Unkown slideatlas item type.');
      }

    $modelLoader = new MIDAS_ModelLoader();
    $coreItemModel = $modelLoader->loadModel('Item');
    $coreItem = $coreItemModel->load($itemId);
    if($coreItem == false)
      {
      throw new Exception("This item_id doesn't exist.", MIDAS_INVALID_POLICY);
      }

    $this->loadDaoClass('ItemDao', 'slideatlas');
    $slideatlasItemDao = new Slideatlas_ItemDao();
    $slideatlasItemDao->setItemId($itemId);
    $slideatlasItemDao->setItemType($itemType);
    $slideatlasItemDao->setItemOrder($itemOrder);
    $this->save($slideatlasItemDao);
    return $slideatlasItemDao;
    }

}  // end class Slideatlas_SlideatlasModelBase