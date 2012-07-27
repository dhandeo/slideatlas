<?php
/*=========================================================================
 MIDAS Server
 Copyright (c) Kitware SAS. 26 rue Louis Guérin. 69100 Villeurbanne, FRANCE
 All rights reserved.
 More information http://www.kitware.com

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0.txt

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
=========================================================================*/

/**
 * Slideatlas module configuration
 */
class Slideatlas_ConfigController extends Slideatlas_AppController
{ 
  public $_models = array('Community');
  public $_moduleModels = array('Community');
  public $_daos = array('Community');
  public $_moduleDaos = array('Community');
  public $_moduleForms = array('Config');
  public $_components = array('Utility', 'Date');

  /** index action*/
  function indexAction()
    {
    $this->requireAdminPrivileges();

    if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini"))
      {
      $applicationConfig = parse_ini_file(BASE_PATH."/core/configs/".$this->moduleName.".local.ini", true);
      }
    else
      {
      $applicationConfig = parse_ini_file(BASE_PATH.'/modules/'.$this->moduleName.'/configs/module.ini', true);
      }
    $configForm = $this->ModuleForm->Config->createConfigForm();
    $formArray = $this->getFormAsArray($configForm);
    $formArray['MongoServer']->setValue($applicationConfig['global']['mongoserver']);
    $formArray['dbName']->setValue($applicationConfig['global']['dbname']);
    $this->view->configForm = $formArray;

    $imageFormatForm = $this->ModuleForm->Config->createImageFormatForm();
    $imageFormatFormArray = $this->getFormAsArray($imageFormatForm);
    $imageFormatFormArray['supportAll']->setValue($applicationConfig['global']['supportAll']);
    $imageFormatFormArray['imageFormats']->setValue($applicationConfig['global']['imageFormats']);
    $this->view->imageFormatForm = $imageFormatFormArray;
    
    if($this->_request->isPost())
      {
      $this->_helper->layout->disableLayout();
      $this->_helper->viewRenderer->setNoRender();

      $deleteCommunity = $this->_getParam('deleteCommunity');
      if(isset($deleteCommunity) && !empty($deleteCommunity)) //delete a community from list
        {
        $communityId = $this->_getParam('element');
        
        $SlideatalsCommunityDao = $this->Slideatlas_Community->getByCommunityId($communityId);
        if($SlideatalsCommunityDao === false)
        {
        throw new Zend_Exception("This community is not in the slideatls community list.");
        }
        $this->Slideatlas_Community->delete($SlideatalsCommunityDao);
        echo JsonComponent::encode(array(true, 'Changes saved'));
        }

      $submitConfig = $this->_getParam('submitConfig');
      $submitImageFormats = $this->_getParam('submitImageFormats');
      if(isset($submitConfig) || isset($submitImageFormats))
        {
        if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old"))
          {
          unlink(BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old");
          }
        if(file_exists(BASE_PATH."/core/configs/".$this->moduleName.".local.ini"))
          {
          rename(BASE_PATH."/core/configs/".$this->moduleName.".local.ini", BASE_PATH."/core/configs/".$this->moduleName.".local.ini.old");
          }
        if(isset($submitConfig))
          {
          $applicationConfig['global']['mongoserver'] = $this->_getParam('MongoServer');
          $applicationConfig['global']['dbname'] = $this->_getParam('dbName');
          }
        if(isset($submitImageFormats))
          {
          $applicationConfig['global']['supportAll'] = $this->_getParam('allFormats');
          $applicationConfig['global']['imageFormats'] = $this->_getParam('imageFormats');
          }
        $this->Component->Utility->createInitFile(BASE_PATH."/core/configs/".$this->moduleName.".local.ini", $applicationConfig);
        echo JsonComponent::encode(array(true, 'Changes saved'));
        }
      }
    
    $communities = array();
    $slideatlasCommunityDaos = $this->Slideatlas_Community->getAll();
    foreach($slideatlasCommunityDaos as $slideatlasCommunity)
      {
      array_push($communities, $this->Community->load($slideatlasCommunity->getCommunityId() ) );   
      }
    $this->view->communities = $communities;
 
    $this->view->json['message']['delete'] = $this->t('Delete');
    $this->view->json['message']['deleteCommunityMessage'] = $this->t('Do you really want to delete this community from the slide atlas community list?');
    }
    
  /** Ajax element used to select a community*/
  public function selectcommunityAction()
    {
    $this->requireAjaxRequest();
    $this->disableLayout();
    $allCommunities = $this->Community->getAll();
    $slideatlasCommunityDaos = $this->Slideatlas_Community->getAll();
    $slideatlasCommunityIDs = array();
    foreach($slideatlasCommunityDaos as $slideatlasCommunity)
      {
      array_push($slideatlasCommunityIDs, $slideatlasCommunity->getCommunityId()); 
      }
    $communities = array();
    foreach($allCommunities as $community)
      {
      if(!in_array($community->getKey(), $slideatlasCommunityIDs))
        {
        array_push($communities, $community);
        }
      }
    $this->view->communities = $communities;
    $this->view->selectEnabled = true;
    }
    

}//end class