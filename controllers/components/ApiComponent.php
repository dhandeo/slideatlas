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
   * Helper function for verifying item's existence
   */
  private function _checkItemExistence($itemid, $userDao)
    {
    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $item = $itemModel->load($itemid);
    if($item === false || !$itemModel->policyCheck($item, $userDao, MIDAS_POLICY_READ))
      {
      throw new Exception("This item doesn't exist or you don't have the permissions.", MIDAS_INVALID_POLICY);
      }
    }

  /**
   * Mark the given regular item as a slideatlas item.
   * Or update an existing one if one exists by the order passed.
   * @param token Authentication token
   * @param id The id of the item.
   * @param order(Optional) item order used to sort slideatlas items
   * @return The slideatlas item object that was created
   */
  public function markItem($args)
    {
    $this->_checkKeys(array('id'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('Cannot mark item anonymously', MIDAS_INVALID_POLICY);
      }
    $this->_checkItemExistence($args['id'], $userDao);

    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    if($slideatlasItem == false)
      {
      if(isset($args['order']))
        {
        $order = intval($args['order']);
        }
      else
        {
        $order = 0; // TODO
        }
      $slideatlasItem = $slideatlasItemModel->createItem($args['id'], $order);
      }
    else if(isset($args['order']))
      {
      $slideatlasItem = $slideatlasItemModel->updateItem($args['id'], intval($args['order']));
      }

    return $slideatlasItem->toArray();
    }

  /**
   * Change a slideatlas item to a regular item.
   * @param token Authentication token
   * @param id The id of the item.
   */
  public function unmarkItem($args)
    {
    $this->_checkKeys(array('id'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('Cannot unmark item anonymously', MIDAS_INVALID_POLICY);
      }
    $this->_checkItemExistence($args['id'], $userDao);

    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    if($slideatlasItem)
      {
      $slideatlasItemModel->delete($slideatlasItem);
      }
    }

  /**
   * Get an item's slideatls attributes
   * @param token (Optional) Authentication token
   * @param id The item id
   * @return The slideatlas item object
   */
  function getAttributes($args)
    {
    $this->_checkKeys(array('id'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    $this->_checkItemExistence($args['id'], $userDao);

    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    if($slideatlasItem == false)
      {
      throw new Exception("This item is not a slideatlas item.", MIDAS_INVALID_POLICY);
      }
    $slideatlasItemArray = $slideatlasItem->toArray();

    return $slideatlasItem->toArray();
    }

} // end class
