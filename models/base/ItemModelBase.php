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
      'item_order' => array('type' => MIDAS_DATA),
      'item' =>  array('type' => MIDAS_ONE_TO_ONE,
                        'model' => 'item',
                        'parent_column' => 'item_id',
                        'child_column' => 'item_id')

       );
    $this->initialize(); // required
    }

  abstract public function getByItemId($itemId);


  /** Create a new slideatals item */
  function createItem($item_id, $item_order)
    {
    if(!is_int($item_order))
      {
      throw new Zend_Exception('order should be an interger.');
      }

    $modelLoader = new MIDAS_ModelLoader();
    $coreItemModel = $modelLoader->loadModel('Item');
    $coreItem = $coreItemModel->load($item_id);
    if($coreItem === false)
      {
      throw new Exception("This item_id doesn't exist.", MIDAS_INVALID_POLICY);
      }

    $this->loadDaoClass('ItemDao', 'slideatlas');
    $slideatlasItemDao = new Slideatlas_ItemDao();
    $slideatlasItemDao->setItemId($item_id);
    $slideatlasItemDao->setItemOrder($item_order);
    $this->save($slideatlasItemDao);
    return $slideatlasItemDao;
    }

}  // end class Slideatlas_SlideatlasModelBase