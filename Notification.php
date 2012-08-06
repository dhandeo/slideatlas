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

require_once BASE_PATH . '/modules/api/library/APIEnabledNotification.php';

class Slideatlas_Notification extends ApiEnabled_Notification
  {
  public $_models = array('Item', 'Community', 'Folder');
  public $moduleName = 'slideatlas';
  public $_moduleComponents = array('Api');
  public $_moduleModels = array('Community', 'Item');
  public $_moduleDaos = array('Community');

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $fc = Zend_Controller_Front::getInstance();
    $this->moduleWebroot = $fc->getBaseUrl().'/modules/'.$this->moduleName;
    $this->coreWebroot = $fc->getBaseUrl().'/core';
    
    $this->addCallBack('CALLBACK_CORE_ITEM_DELETED', 'handleItemDeleted');
    $this->addCallBack('CALLBACK_CORE_GET_USER_ACTIONS', 'getUserAction');
    $this->addCallBack('CALLBACK_CORE_ITEM_VIEW_JS', 'getJs');
    $this->addCallBack('CALLBACK_CORE_GET_FOOTER_HEADER', 'getHeader');
    $this->addCallBack('CALLBACK_CORE_LAYOUT_TOPBUTTONS', 'getButton');
    
    $this->addTask("TASK_MARK_SLIDEATLAS_RAW_ITEM", 'markRawItem', "Mark an item as to-be-processed slide atlas item. Parameters: Item, Revision");
    $this->addEvent('EVENT_CORE_UPLOAD_FILE', 'TASK_MARK_SLIDEATLAS_RAW_ITEM');
    }//end init

  /**
   * When an item is being deleted, we should delete corresponding row in slideatlas_item tables.  
   */
  public function handleItemDeleted($params)
    {
    $slideatlasItem = $this->Slideatlas_Item->getByItemId($params['item']->getKey());
    if($slideatlasItem)
      {
      $slideatlasItemModel->delete($slideatlasItem);
      }
    }

  /** Add a tab to the user's main page to view slideatlas images  */
  public function getUserAction($args)
    {
    $apiargs['useSession'] = true;
    $apiargs['type'] = 'diced';
    $slideatlasItems = $this->ModuleComponent->Api->userGetItems($apiargs);

    $fc = Zend_Controller_Front::getInstance();
    $moduleWebroot = $fc->getBaseUrl().'/'.$this->moduleName;
    $moduleFileroot =  $fc->getBaseUrl().'/modules/'.$this->moduleName;
    return array($this->t('Slide Atlas') => 
                 array("url" => $moduleWebroot.'/user/list', "image" => $moduleFileroot.'/public/images/microscope.png') );
    }      

  /** Get javascript for the item view */
  public function getJs($params)
    {
    return array($this->moduleWebroot.'/public/js/user/user.item.view.js');
    }

  /** get layout header */
  public function getHeader()
    {
    return '<link type="text/css" rel="stylesheet" href="'.Zend_Registry::get('webroot').'/modules/slideatlas/public/css/layout/slideatlas.css" />';
    }  
    
  /** add a view image button  */ 
  public function getButton($params)
    {
    if(!isset($this->userSession->Dao))
      {
      return array();
      }
    else
      {
      $fc = Zend_Controller_Front::getInstance();
      $baseURL = $fc->getBaseUrl();
      $moduleWebroot = $baseURL . '/' . $this->moduleName;
      $moduleFileroot =  $fc->getBaseUrl().'/modules/'.$this->moduleName;
      $html =  "<li class='listButton' style='margin-left:5px;' title='Slide Atlas' rel='".$moduleWebroot."/user/list'>
              <a href='".$moduleWebroot."/user/list'><img id='vieImageButtonImg' src= '".$moduleFileroot."/public/images/microscope.png' alt='Slide Atlas'/>
              <img id='listButtonLoading' style='margin-top:5px;display:none;' src='".$baseURL."/core/public/images/icons/loading.gif' alt=''/>
              Slide Atlas
              </a>
              </li> ";
      return $html;
      }
    }
    
  /** Mark an item as to-be-processed slide atlas item*/
  public function markRawItem($params)
    {
    $itemParam = $params[0];
    $item = $this->Item->load($itemParam['item_id']);
   
    $revisionCount = count($item->getRevisions());
    // only process the initial revision
    if(($revisionCount == 0) || ($revisionCount > 1))
      {
      return;
      }
    
    //check if the item is in the slide atlas communities
    $slideatlasCommunityRootFolders = array();
    $itemCommunityRootFolders = array();
    foreach($this->Slideatlas_Community->getAll() as $slideatlasCommunity)
      {
      $community = $this->Community->load($slideatlasCommunity->getCommunityId());
      array_push($slideatlasCommunityRootFolders, $community->getFolderId());
      }
    foreach($item->getFolders() as $parentFolder)
      {
      $folderId = $parentFolder->getKey();
      $folder = $this->Folder->load($folderId);
      $rootFolder = $this->Folder->getRoot($folder);
      array_push($itemCommunityRootFolders, $rootFolder->getKey());
      }
    $inSlideatlasCommunity = array_intersect($slideatlasCommunityRootFolders, $itemCommunityRootFolders);
    if(empty($inSlideatlasCommunity))
      {
      return;
      }

    // check if the item format is not supported by image dicer  
    if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini"))
      {
      $applicationConfig = parse_ini_file(BASE_PATH."/core/configs/".$this->moduleName.".local.ini", true);
      }
    else
      {
      $applicationConfig = parse_ini_file(BASE_PATH.'/modules/'.$this->moduleName.'/configs/module.ini', true);
      }
    $supportAll = $applicationConfig['global']['supportAll'];
    $imageFormats = explode(',', $applicationConfig['global']['imageFormats']);
    array_walk($imageFormats, create_function('&$val', '$val = trim($val);'));
    $itemFormat = end(explode('.', $item->getName()));
    if((intval($supportAll) == MIDAS_SLIDEATLAS_NOT_ALL_FORMATS) && (!in_array($itemFormat, $imageFormats)) )
      {
      return;
      }
    // mark item as to-be-processed
    $args['useSession'] = true;
    $args['id'] = $item->getKey();
    $args['type'] = 'raw';
    $this->ModuleComponent->Api->markItem($args);  
 
    return;
    }

} //end class
  
?>
