<?php

namespace Conferences\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mail\Transport;
use Zend\Mail\Message as Message;

use Conferences\Service\ConferenceService;

use Conferences\Form\Conference as ConferenceForm;
use Conferences\Form\ConferenceImage as ConferenceImageForm;

use Conferences\Entity\Conference;

class AdminConferenceController extends AbstractActionController
{
    
    /**
     * Conference creation and edit Form 
     *  
     * @var \Zend\Form\Form
     */
    private $form;
    
    /**
     * Conference image handling Form 
     *  
     * @var \Zend\Form\Form
     */
    private $imageForm;
    
    /**
     * Main service for handling conferences (IE conferences)
     * 
     * @var \Conferences\Service\ConferenceService
     */
    private $conferenceService;
    
    
    /**
     * Class constructor
     * 
     * @param \Conferences\Service\ConferenceService $conferenceService
     * @param \Zend\Form\Form $promoteForm
     */
    public function __construct(ConferenceService $conferenceService, ConferenceForm $form, ConferenceImageForm $imageForm) {
        $this->conferenceService = $conferenceService;
        $this->form = $form;
        $this->imageForm = $imageForm;
    }
    
    /**
     * Returns a list of conferences, as fethched from model
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction() {
        
    	return new ViewModel(array('conferences' => $this->conferenceService->getFullList()));
        
    }
    
    /**
     * Add new conference
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction() {
        $conference = new Conference();
        
        // bind entity values to form
        $this->form->bind($conference);
        
        $view = $this->processForm($conference);
        
        if (!$view instanceof ViewModel) {
        
            $view = new ViewModel(array('form' => $this->form, 
                                        'title' => 'New conference'));
            $view->setTemplate('conferences/admin-conference/conference-form');
            
        }
        
        return $view;
    }
    
    public function editAction() {
        $conference = $this->getConferenceFromQuery();
                
        // bind entity values to form
        $this->form->bind($conference);
        
        $view = $this->processForm($conference);
        
        if (!$view instanceof ViewModel) {
            
            $view = new ViewModel(array('form' => $this->form, 'title' => 'Edit conference ' . $conference->getTitle()));
            $view->setTemplate('conferences/admin-conference/conference-form');
            
        }
        
        return $view;
    }
    

    public function removeAction() {
        $conference = $this->getConferenceFromQuery();
                
        $this->conferenceService->removeConference($conference);
        
        return $this->redirect()->toRoute('zfcadmin/conferences');
    }
    
    public function imageAction() {
        $conference = $this->getConferenceFromQuery();
        
        $form = $this->imageForm;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Merge data received from form
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                
                // Configure adapter to save image
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                $adapter->setDestination('./public/confimages/');

                // retrieve uploaded file info
                $receivedFiles = $adapter->getFileInfo();
                $receivedFile = $receivedFiles[0];  

                // add filter to rename uploaded file
                $adapter->addFilter('File\Rename',
                                    array('target' => $adapter->getDestination() . '/' . $conference->getId() . '.jpg',
                                          'overwrite' => true));

                // receive and store file
                if ($adapter->receive($receivedFile['name'])) {
                    $adapter->getFilter('File\Rename')->getFile();
                    
                    // redirect to confernce list
                    return $this->redirect()->toRoute('zfcadmin/conferences');
                
                } else {
                    // an error has occurred
                    $form->setMessages(array('image' => array('An error has occurred during file upload')));
                }
                
            }
        }

        
        $view = new ViewModel(array('form' => $this->imageForm,
                                    'conference' => $conference));
        
        return $view;
    }
    
    
    
    
    /*
     * Private methods
     */
    
    private function processForm(Conference $conference) {
        
        if ($this->request->isPost()) {
        
            // bind entity values to form
            if ($conference instanceof Conference) {
                $this->form->bind($conference);
                $confirmMessage = 'Conference ' . $conference->getTitle() . ' updated successfully';
            } else {
                $this->form->bind(new Conference());
                $confirmMessage = 'Conference inserted successfully';
            }
            
            // get post data
            $post = $this->request->getPost()->toArray();
            
            // fill form
            $this->form->setData($post);
        
            // check if form is valid
            if(!$this->form->isValid()) {
                
                // prepare view
                $view = new ViewModel(array('form'  => $this->form,
                                            'title' => 'Some errors during conference processing'));
                $view->setTemplate('conferences/admin-conference/conference-form');
                
                return $view;
        
            }
        
            // use service to save data
            $conference = $this->conferenceService->upsertConference($this->form->getData());
        
            $this->flashMessenger()->setNamespace('admin-conference')->addMessage($confirmMessage);
            
            // redirect to conference list
            return $this->redirect()->toRoute('zfcadmin/conferences');
            
        }
        
    }
    
    private function getConferenceFromQuery() {
    
        $id = (int)$this->params('id');
    
        if (empty($id) || $id <= 0){
            $this->getResponse()->setStatusCode(404);    //@todo there is a better way?
            // Probably triggering Not Found Conference SM
            // Zend\Mvc\Application: dispatch.error
            return;
        }
    
        $conference = $this->conferenceService->getConference($id);
    
        if ($conference === null){
            throw new \Exception('Conference not found');    //@todo throw custom exception type
        }
    
        return $conference;
    
    }
    
}
