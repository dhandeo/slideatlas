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

/** Component for api methods */
class Slideatlas_ApiComponent extends AppComponent
{

  /**
   * Helper function for verifying keys in an input array
   */
  private function _checkKeys($keys, $values)
    {
    foreach($keys as $key)
      {
      if(!array_key_exists($key, $values))
        {
        throw new Exception('Parameter '.$key.' must be set.', -1);
        }
      }
    }

  /**
   * Create a slideatlas item or update an existing one. it calls the itemCreate api
   * to create a regular item and then add slideatals related attributes on top of it.
   * @param token Authentication token
   * @param parentid The id of the parent folder. Only required for creating a new item.
   * @param name The name of the item to create
   * @param itemOrder(Optional) item order used to sort slideatlas items
   * @param description (Optional) The description of the item
   * @param uuid (Optional) Uuid of the item. If none is passed, will generate one.
   * @param privacy (Optional) Default 'Public', possible values [Public|Private].
   * @return The item object that was created
   */
  public function createItem($args)
    {
    $this->_checkKeys(array('parentId', 'name'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('Cannot create item anonymously', MIDAS_INVALID_POLICY);
      }

    $coreApiComponent = $componentLoader->loadComponent('Api', 'api');
    $coreItemArray = $coreApiComponent->itemCreate($args);

    $modelLoad = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoad->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->createItem($coreItem['item_id'], $args['itemOrder']);
    $slideatlasItemArray = $slideatlasItem->toArray();

    return ($slideatlasItemArray + $coreItemArray);
    }


  /**
   * Get an item's information
   * @param token (Optional) Authentication token
   * @param id The item id
   * @param head (Optional) only list the most recent revision
   * @return The item object
   */
  function getItem($args)
    {
    $this->_validateParams($args, array('id'));

    $coreApiComponent = $componentLoader->loadComponent('Api', 'api');
    $coreItemArray = $coreApiComponent->itemGet($args);

    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    $slideatlasItemArray = $slideatlasItem->toArray();

    return ($slideatlasItemArray + $coreItemArray);
    }

} // end class
