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
    $this->_checkKeys(array('parentid', 'name'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);
    if(!$userDao)
      {
      throw new Exception('Cannot create item anonymously', MIDAS_INVALID_POLICY);
      }

    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $name = $args['name'];
    $description = isset($args['description']) ? $args['description'] : '';

    $uuid = isset($args['uuid']) ? $args['uuid'] : '';
    $record = false;
    $itemArray = array();
    if(!empty($uuid))
      {
      $componentLoader = new MIDAS_ComponentLoader();
      $uuidComponent = $componentLoader->loadComponent('Uuid');
      $record = $uuidComponent->getByUid($uuid);
      }
    if($record != false && $record instanceof ItemDao)
      {
      if(!$itemModel->policyCheck($record, $userDao, MIDAS_POLICY_WRITE))
        {
        throw new Exception('Invalid policy', MIDAS_INVALID_POLICY);
        }
      $record->setName($name);
      if(isset($args['description']))
        {
        $record->setDescription($args['description']);
        }
      if(isset($args['privacy']))
        {
        $privacy = $args['privacy'];
        if($privacy !== 'Public' && $privacy !== 'Private')
          {
          throw new Exception('privacy should be one of [Public|Private]');
          }
        if($privacy === 'Public')
          {
          $privacy_status = MIDAS_PRIVACY_PUBLIC;
          }
        else
          {
          $privacy_status = MIDAS_PRIVACY_PRIVATE;
          }
        $record->setPrivacyStatus($privacy_status);
        }
      foreach($args as $key => $value)
        {
        // Params beginning with underscore are assumed to be metadata fields
        if(substr($key, 0, 1) == '_')
          {
          $this->_setMetadata($record, MIDAS_METADATA_TEXT, substr($key, 1), '', $value);
          }
        }
      $itemModel->save($record);
      $itemArray = $record->toArray();
      }
    else
      {
      if(!array_key_exists('parentid', $args))
        {
        throw new Exception('Parameter parentid is not defined', MIDAS_INVALID_PARAMETER);
        }
      $folderModel = $modelLoader->loadModel('Folder');
      $folder = $folderModel->load($args['parentid']);
      if($folder == false)
        {
        throw new Exception('Parent folder doesn\'t exist', MIDAS_INVALID_PARAMETER);
        }
      if(!$folderModel->policyCheck($folder, $userDao, MIDAS_POLICY_WRITE))
        {
        throw new Exception('Invalid permissions on parent folder', MIDAS_INVALID_POLICY);
        }
      $item = $itemModel->createItem($name, $description, $folder, $uuid);
      if($item === false)
        {
        throw new Exception('Create new item failed', MIDAS_INTERNAL_ERROR);
        }
      $itempolicyuserModel = $modelLoader->loadModel('Itempolicyuser');
      $itempolicyuserModel->createPolicy($userDao, $item, MIDAS_POLICY_ADMIN);
      $itemArray = $item->toArray();
      }

    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->createItem($itemArray['item_id'], intval($args['itemOrder']) );
    $slideatlasItemArray = $slideatlasItem->toArray();

    return ($slideatlasItemArray + $itemArray);
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
    $this->_checkKeys(array('id'), $args);

    $componentLoader = new MIDAS_ComponentLoader();
    $authComponent = $componentLoader->loadComponent('Authentication', 'api');
    $userDao = $authComponent->getUser($args,
                                       Zend_Registry::get('userSession')->Dao);

    $itemid = $args['id'];
    $modelLoader = new MIDAS_ModelLoader();
    $itemModel = $modelLoader->loadModel('Item');
    $item = $itemModel->load($itemid);

    if($item === false || !$itemModel->policyCheck($item, $userDao, MIDAS_POLICY_READ))
      {
      throw new Exception("This item doesn't exist or you don't have the permissions.", MIDAS_INVALID_POLICY);
      }

    $itemArray = $item->toArray();

    $owningFolders = $item->getFolders();
    if(count($owningFolders) > 0)
      {
      $itemArray['folder_id'] = $owningFolders[0]->getKey();
      }

    $revisionsArray = array();
    if(array_key_exists('head', $args))
      {
      $revisions = array($itemModel->getLastRevision($item));
      }
    else //get all revisions
      {
      $revisions = $item->getRevisions();
      }

    foreach($revisions as $revision)
      {
      if(!$revision)
        {
        continue;
        }
      $bitstreamArray = array();
      $bitstreams = $revision->getBitstreams();
      foreach($bitstreams as $b)
        {
        $bitstreamArray[] = $b->toArray();
        }
      $tmp = $revision->toArray();
      $tmp['bitstreams'] = $bitstreamArray;
      $revisionsArray[] = $tmp;
      }
    $itemArray['revisions'] = $revisionsArray;

    $extraFields = array();
    $modules = Zend_Registry::get('notifier')->callback('CALLBACK_API_EXTRA_ITEM_FIELDS',
                                                       array('item' => $item));
    foreach($modules as $module => $fields)
      {
      foreach($fields as $name => $value)
        {
        $extraFields[$module.'_'.$name] = $value;
        }
      }
    $itemArray['extraFields'] = $extraFields;

    $slideatlasItemModel = $modelLoader->loadModel('Item', 'slideatlas');
    $slideatlasItem = $slideatlasItemModel->getByItemId($args['id']);
    $slideatlasItemArray = $slideatlasItem->toArray();

    return ($itemArray + $slideatlasItemArray);
    }

} // end class
