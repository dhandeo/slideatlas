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
 * Slideatlas_ConfigForm
 */
class Slideatlas_ConfigForm extends AppForm
{

  /** create  form */
  public function createConfigForm()
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/slideatlas/config/index')
          ->setMethod('post');

    $MongoServer = new Zend_Form_Element_Text('MongoServer');
    $dbName = new Zend_Form_Element_Text('dbName');

    $submit = new  Zend_Form_Element_Submit('submitConfig');
    $submit ->setLabel('Save Mongo Database configuration');

    $form->addElements(array($MongoServer, $dbName, $submit));
    return $form;
    }

  /** create imageformat form */
  public function createImageFormatForm()
    {
    $form = new Zend_Form;

    $form->setAction($this->webroot.'/slideatlas/config/index')
          ->setMethod('post');

    $allFormats = new Zend_Form_Element_Radio('supportAll');
    $allFormats->addMultiOptions(array(
                 MIDAS_SLIDEATLAS_ALL_FORMATS => $this->t("Yes, all items need to be processed."),
                 MIDAS_SLIDEATLAS_NOT_ALL_FORMATS => $this->t("No, only process items in supported image formats."),
                  ))
            ->setRequired(true)
            ->setValue(MIDAS_SLIDEATLAS_NOT_ALL_FORMATS);
    $imageFormats = new Zend_Form_Element_Text('imageFormats');
    $submit = new  Zend_Form_Element_Submit('submitImageFormats');
    $submit ->setLabel('Save Image Format Configuration');

    $form->addElements(array($allFormats, $imageFormats, $submit));
    return $form;
    }

} // end class
?>
