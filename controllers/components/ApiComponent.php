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

  private function _getSlideatlasMetaData($itemid)
    {
    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $item = $itemModel->load($itemid);
    $revisionDao = $itemModel->getLastRevision($item);
    if(!$revisionDao)
      {
      throw new Exception("The item must have at least one revision to have metadata.", MIDAS_INVALID_POLICY);
      }

    $itemRevisionModel = $modelLoader->loadModel('ItemRevision');
    $metadata = $itemRevisionModel->getMetadata($revisionDao);
    $metadataArray = array();
    foreach($metadata as $m)
      {
      $mArray = $m->toArray();
      if((!strnatcasecmp($mArray['element'], 'tilesize')) || (!strnatcasecmp($mArray['element'],'levels')) || (!strnatcasecmp($mArray['element'],'collection')))
        {
        if(array_key_exists(strtolower($mArray['element']), array_change_key_case($metadataArray)))
          {
          throw new Exception("This item has duplicated metadata.", MIDAS_INVALID_POLICY);
          }
        $metadataArray[$mArray['element']] = $mArray['value'];
        }
      }
    if((!array_key_exists('tilesize', array_change_key_case($metadataArray))) || (!array_key_exists('levels', array_change_key_case($metadataArray))) )
      {
      throw new Exception("This item's metada doesn't include levels and/or tilesize.", MIDAS_INVALID_POLICY);
      }

    return $metadataArray;
    }

  /**
   * Mark a regular item as a slideatlas item and set its item type.
   * Or update the item type for an existing slideatlas item.
   * @param token Authentication token
   * @param id The id of the item.
   * @param type The type of the item. currently supported types: 1) raw ; 2) diced
   * @return The slideatlas item object that was created or updated
   */
  public function markItem($args)
    {
    $this->_checkKeys(array('id', 'type'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('Cannot set item type anonymously', MIDAS_INVALID_POLICY);
      }
    $this->_checkItemExistence($args['id'], $userDao);

    if((strcasecmp($args['type'], 'raw') != 0)  && (strcasecmp($args['type'], 'diced') != 0) )
      {
      throw new Exception('Unknown type. Currenlty supported types: 1) raw ; 2) diced ', MIDAS_INVALID_POLICY);
      }
    $itemType = SLIDEATLAS_RAW_IMAGE;
    if(strcasecmp($args['type'], 'diced') == 0)
      {
      $itemType = SLIDEATLAS_DICED_IMAGE;
      }
    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    if($slideatlasItem == false)
      {
      $slideatlasItem = $slideatlasItemModel->createItem($args['id'], $itemType);
      }
    else
      {
      $slideatlasItem = $slideatlasItemModel->updateItemType($args['id'], $itemType);
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
    $itemId = $args['id'];

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to get item attributes');
      }
    $this->_checkItemExistence($itemId, $userDao);

    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($itemId);
    if($slideatlasItem == false)
      {
      throw new Exception("This item is not a slideatlas item.", MIDAS_INVALID_POLICY);
      }
    $slideatlasItemArray = $slideatlasItem->toArray();
    if($slideatlasItemArray['item_type'] == SLIDEATLAS_DICED_IMAGE)
      {
      $metaDataArray = $this->_getSlideatlasMetaData($itemId);
      $lowercase_metaDataArray = array_change_key_case($metaDataArray);
      $slideatlasItemArray['levels'] = $lowercase_metaDataArray['levels'];
      $slideatlasItemArray['tilesize'] = $lowercase_metaDataArray['tilesize'];
      $slideatlasItemArray['collection'] = $lowercase_metaDataArray['collection'];
      }

    return $slideatlasItemArray;
    }

  /**
   * Get all the slideatlas items accessable for current user.
   * @param token Authentication token
   * @param type The type of the item. currently supported types: 1) raw ; 2) diced
   */
  public function userGetItems($args)
    {
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);

    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to get slidealas items');
      }
    if((strcasecmp($args['type'], 'raw') != 0)  && (strcasecmp($args['type'], 'diced') != 0) )
      {
      throw new Exception('Unknown type. Currenlty supported types: 1) raw ; 2) diced ', MIDAS_INVALID_POLICY);
      }
    $itemType = SLIDEATLAS_RAW_IMAGE;
    if(strtolower($args['type']) == 'diced')
      {
      $itemType = SLIDEATLAS_DICED_IMAGE;
      }

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $userModel = $modelLoader->loadModel('User');
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');

    $ownedItems = $itemModel->getOwnedByUser($userDao);
    $sharedItems = $itemModel->getSharedToUser($userDao);
    $allItems = array_merge($ownedItems, $sharedItems);
    $allItemIds = array();
    foreach($allItems as $item)
      {
      array_push($allItemIds, $item->getKey());
      }
    $communities = $userModel->getUserCommunities($userDao);
    foreach($communities as $community)
      {
      $communityItems = $itemModel->getSharedToCommunity($community);
      foreach($communityItems as $communityItem)
        {
        if(!in_array($communityItem->getKey(), $allItemIds))
          {
          array_push($allItems, $communityItem);
          array_push($allItemIds, $communityItem->getKey());
          }
        }
      }
    $returnSlideatlasItems = array();
    foreach($allItems as $item)
      {
      $slideaslasItem = $slideatlasItemModel->getByItemId($item->getKey());
      if($slideaslasItem && $slideaslasItem->getItemType() == $itemType)
        {
        $accessibleItemArray = $item->toArray();
        $accessibleItemArray['slideatlas_id'] = $slideaslasItem->getKey();
        $accessibleItemArray['item_type'] = $itemType;
        $accessibleItemArray['item_order'] = $slideaslasItem->getItemOrder();
        if($itemType == SLIDEATLAS_DICED_IMAGE)
          {
          $metaDataArray = $this->_getSlideatlasMetaData($item->getKey());
          $lowercase_metaDataArray = array_change_key_case($metaDataArray);
          $accessibleItemArray['levels'] = $lowercase_metaDataArray['levels'];
          $accessibleItemArray['tilesize'] = $lowercase_metaDataArray['tilesize'];
      $accessibleItemArray['collection'] = $lowercase_metaDataArray['collection'];
          }
        array_push($returnSlideatlasItems, $accessibleItemArray);
        }
      }
    return $returnSlideatlasItems;
    }

  /**
   * Get all the slideatlas items in the database.
   * @param token Authentication token
   * @param type The type of the item. currently supported types: 1) raw ; 2) diced
   */
  public function adminGetItems($args)
    {
    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);

    if(!$userDao)
      {
      throw new Zend_Exception('You must be logged in to get all slidealas items');
      }
    if(!$userDao->getAdmin())
      {
      throw new Zend_Exception('You must be an admin to get all slidealas items');
      }
    if((strcasecmp($args['type'], 'raw') != 0)  && (strcasecmp($args['type'], 'diced') != 0) )
      {
      throw new Exception('Unknown type. Currenlty supported types: 1) raw ; 2) diced ', MIDAS_INVALID_POLICY);
      }

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $allslideatlasItems = array();
    if(strtolower($args['type']) == 'diced')
      {
      $allslideatlasItems = $slideatlasItemModel->getAllByItemType(SLIDEATLAS_DICED_IMAGE);
      }
    else
      {
      $allslideatlasItems = $slideatlasItemModel->getAllByItemType(SLIDEATLAS_RAW_IMAGE);
      }
    $returnSlideatlasItems = array();
    foreach($allslideatlasItems as $slideatlasItem)
      {
      $regularItem = $itemModel->load($slideatlasItem->getItemId());
      $accessibleItemArray = $regularItem->toArray();
      $accessibleItemArray['slideatlas_id'] = $slideatlasItem->getKey();
      $accessibleItemArray['item_type'] = $slideatlasItem->getItemType();
      $accessibleItemArray['item_order'] = $slideatlasItem->getItemOrder();
      if($itemType == SLIDEATLAS_DICED_IMAGE)
        {
        $metaDataArray = _getSlideatlasMetaData($regularItem->getKey());
        $lowercase_metaDataArray = array_change_key_case($metaDataArray);
        $accessibleItemArray['levels'] = $lowercase_metaDataArray['levels'];
        $accessibleItemArray['tilesize'] = $lowercase_metaDataArray['tilesize'];
        $accessibleItemArray['collection'] = $lowercase_metaDataArray['collection'];
        }
      array_push($returnSlideatlasItems, $accessibleItemArray);
      }

    return $returnSlideatlasItems;
    }


} // end class
