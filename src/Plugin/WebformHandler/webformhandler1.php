<?php

namespace Drupal\lalg_webform_handlers\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\Component\Utility\Html;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "lalg_webform_handlers_custom_validator",
 *   label = @Translation("Validate emails are different"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Validate emails are different"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
 
  class webformhandler1 extends WebformHandlerBase {
   use StringTranslationTrait;
    
   /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateEmails($form_state);
  }
  
  
   /**
   * Validate that the 6 possible email addresses are different.   * 
   */
    private function validateEmails(FormStateInterface $formState) {      
      
      /**
      *  NOTE: Hard Coded variables relevant to Administer Contact Details only
      *  If the page setup or page names changes, then below might need to be also changed.
      *  If the email fields names change, then brlow might need to be also changed.
      */
      
      $pageToCheckErrors = "additional_members";    # to stop the error message being displayed on the first page
    
      $fieldsToCheck = array();
      $fieldsToCheck[] = 'civicrm_1_contact_1_email_email';
      $fieldsToCheck[] = 'civicrm_3_contact_1_email_email';
      $fieldsToCheck[] = 'civicrm_4_contact_1_email_email';
      $fieldsToCheck[] = 'civicrm_5_contact_1_email_email';
      $fieldsToCheck[] = 'civicrm_6_contact_1_email_email';
      $fieldsToCheck[] = 'civicrm_7_contact_1_email_email';
      
      $keyOnFirstPage = 0;
    
      // end hard coding

  

      $values = $formState->cleanValues()->getValues();        


      // The next few lines 
      
      $emailArray = array();
      foreach ($fieldsToCheck as $fieldsToCheckKey => $fieldsToCheckValue) {
        $emailArray[] = $values[$fieldsToCheckValue];
      }             
      $emailArrayNoBlanks = array_filter($emailArray);   # ignore empty email address fields
      $allEmailsEntered = array_map( 'strtolower', $emailArrayNoBlanks );  # comparison should be case insensitive
      $allEmailsEnteredCopy = $allEmailsEntered;
      $uniqueEmailsEntered = array_unique($allEmailsEntered);    # this array has no duplicates   
      
      if (count($allEmailsEnteredCopy) <> count($uniqueEmailsEntered)) {  # then there must be at least 1 dupliacte

        foreach ($uniqueEmailsEntered as $emailEntry) {
          $key = array_search($emailEntry, $allEmailsEnteredCopy);
          unset($allEmailsEnteredCopy[$key]);
        }
  
        $duplicateEmails = array_unique($allEmailsEnteredCopy);    # this is an array of the duplicates
        $textListOfDuplicateEmails = "";
        $textIsOrAre = "is used more than once";
        foreach ($duplicateEmails as $duplicateEmailKey => $duplicateEmailValue) {  
            if (strlen($textListOfDuplicateEmails) > 0) {
              $textListOfDuplicateEmails .= ", ";
              $textIsOrAre = "are used more than once";
            }
            $textListOfDuplicateEmails .= $duplicateEmailValue;   # building a string of duplicates, but only showing each duplicate once
        }  
        
        // go through each email entered and set error if it matches one of the duplicate emails
        if ($formState->get('current_page') == $pageToCheckErrors) {     
          $firstPageMessage = "";
          foreach ($allEmailsEntered as $allEmailsEnteredKey => $allEmailsEnteredValue) {    
            if (in_array($allEmailsEnteredValue, $duplicateEmails) === true) {    
              if ($allEmailsEnteredKey == $keyOnFirstPage) {
                $firstPageMessage = " - including on the previous page.";
              }
              $formState->setErrorByName($fieldsToCheck[$allEmailsEnteredKey], "All emails must be unique - $textListOfDuplicateEmails $textIsOrAre $firstPageMessage");
            }
          }
        }
       
      } 

    }
  }
  
  