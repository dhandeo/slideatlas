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
/** competitor controller*/
class Slideatlas_UserController extends Slideatlas_AppController
{
  public $_models = array('User', 'Item');
  public $_daos = array('User');
  public $_moduleModels = array('Item');
  public $_moduleDaos = array('Item');
  public $_moduleComponents = array('Api');
  public $_components = array('Date');

  /**
   * @method init()
   * Index Action (first action when we access the application)
   */
  function init()
    {

    } // end method init

  /**
   * Get Item Information(Ajax)
   */
  function getiteminfoAction()
    {
    $this->requireAjaxRequest();
    $itemId = $this->_getParam('itemId');
    if(!isset($itemId))
      {
      throw new Zend_Exception('itemId parameter required');
      }
    $this->disableView();
    $this->disableLayout();

    $item = $this->Item->load($itemId);
    $itemname = $item->getName();

    $itemSlideatlas = $this->Slideatlas_Item->getByItemId($itemId);
    if(is_object($itemSlideatlas) && $itemSlideatlas->getItemType() == SLIDEATLAS_DICED_IMAGE)
      {
      $args['useSession'] = true;
      $args['id'] = $itemId;
      $attributes = $this->ModuleComponent->Api->getAttributes($args);
      echo JsonComponent::encode(array('status' => 'ok', 'itemslideatlas' => $itemSlideatlas,
        'itemname' => $itemname, 'levels' => $attributes['levels'], 'tilesize' => $attributes['tilesize']));
      }
    else
      {
      echo JsonComponent::encode(array('status' => 'na'));
      }
    }

  /** list action*/
  function listAction()
    {
    $this->view->Date = $this->Component->Date;
    $user_id = $this->_getParam("user_id");

    if(!isset($user_id) && !$this->logged)
      {
      $this->view->header = $this->t(MIDAS_LOGIN_REQUIRED);
      $this->_helper->viewRenderer->setNoRender();
      return false;
      }
    elseif(!isset($user_id))
      {
      $userDao = $this->userSession->Dao;
      $this->view->activemenu = 'myprofile'; // set the active menu
      }
    else
      {
      $userDao = $this->User->load($user_id);
      if($userDao->getPrivacy() == MIDAS_USER_PRIVATE &&
        (!$this->logged || $this->userSession->Dao->getKey() != $userDao->getKey()) &&
        (!isset($this->userSession->Dao) || !$this->userSession->Dao->isAdmin()))
        {
        throw new Zend_Exception("Permission error");
        }
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Controller_Action_Exception("Unable to find user", 404);
      }

    $this->view->user = $userDao;
    if(!empty($this->userSession->Dao) && ($userDao->getKey() == $this->userSession->Dao->getKey() || $this->userSession->Dao->isAdmin()))
      {
      $args['useSession'] = true;
      $args['type'] = 'diced';
      $listImages = $this->ModuleComponent->Api->userGetItems($args);
      $this->view->listImages = $listImages;
      }

    $this->view->isViewAction = ($this->logged && ($this->userSession->Dao->getKey() == $userDao->getKey() || $this->userSession->Dao->isAdmin()));
    }

  /** fullscreen action*/
  function fullscreenAction()
    {
    $this->_helper->layout->disableLayout();

    $user_id = $this->_getParam("user_id");
    if(!isset($user_id) && !$this->logged)
      {
      $this->view->header = $this->t(MIDAS_LOGIN_REQUIRED);
      $this->_helper->viewRenderer->setNoRender();
      return false;
      }
    elseif(!isset($user_id))
      {
      $userDao = $this->userSession->Dao;
      $this->view->activemenu = 'myprofile'; // set the active menu
      }
    else
      {
      $userDao = $this->User->load($user_id);
      if($userDao->getPrivacy() == MIDAS_USER_PRIVATE &&
        (!$this->logged || $this->userSession->Dao->getKey() != $userDao->getKey()) &&
        (!isset($this->userSession->Dao) || !$this->userSession->Dao->isAdmin()))
        {
        throw new Zend_Exception("Permission error");
        }
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Controller_Action_Exception("Unable to find user", 404);
      }

    $this->view->user = $userDao;
    $this->view->title = $this->_getParam("image");
    $this->view->json['slideatlas']['imageName'] = $this->_getParam("image");
    $this->view->json['slideatlas']['zoomLevels'] = $this->_getParam("levels");
    $this->view->json['slideatlas']['tilesize'] = $this->_getParam("tilesize");
    }

  /** chunk action*/
  function chunkAction()
    {
    $this->_helper->layout->disableLayout();

    $user_id = $this->_getParam("user_id");
    if(!isset($user_id) && !$this->logged)
      {
      $this->view->header = $this->t(MIDAS_LOGIN_REQUIRED);
      $this->_helper->viewRenderer->setNoRender();
      return false;
      }
    elseif(!isset($user_id))
      {
      $userDao = $this->userSession->Dao;
      $this->view->activemenu = 'myprofile'; // set the active menu
      }
    else
      {
      $userDao = $this->User->load($user_id);
      if($userDao->getPrivacy() == MIDAS_USER_PRIVATE &&
        (!$this->logged || $this->userSession->Dao->getKey() != $userDao->getKey()) &&
        (!isset($this->userSession->Dao) || !$this->userSession->Dao->isAdmin()))
        {
        throw new Zend_Exception("Permission error");
        }
      }
    if(!$userDao instanceof UserDao)
      {
      throw new Zend_Controller_Action_Exception("Unable to find user", 404);
      }

    $this->view->image = $this->_getParam("image");
    $this->view->name = $this->_getParam("name");
    $this->view->tilesize = $this->_getParam("tilesize");
    //session_write_close();
    }
}//end class
