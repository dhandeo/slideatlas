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
  public $moduleName = 'slideatlas';
  public $_moduleComponents = array('Api');
  public $_models=array();

  /** init notification process*/
  public function init()
    {
    $this->enableWebAPI($this->moduleName);
    $fc = Zend_Controller_Front::getInstance();
    $this->moduleWebroot = $fc->getBaseUrl().'/modules/'.$this->moduleName;
    $this->coreWebroot = $fc->getBaseUrl().'/core';
    
    $this->addCallBack('CALLBACK_CORE_ITEM_DELETED', 'handleItemDeleted');
    $this->addCallBack('CALLBACK_CORE_GET_USER_ACTIONS', 'getUserAction');
    
    }//end init

  /**
   * When an item is being deleted, we should delete corresponding row in slideatlas_item tables.  
   */
  public function handleItemDeleted($params)
    {
    $modelLoader = new MIDAS_ModelLoader();
    $slideatlasItemModel = $modelLoader->loadModel('Item', $this->moduleName);
    $slideatlasItem = $slideatlasItemModel->getByItemId($params['item']);
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
    return array($this->t('View connectome images') => 
                 array("url" => $moduleWebroot.'/user/index', "image" => $moduleFileroot.'/public/images/microscope.png') );
    }      
  
  } //end class
  
?>
