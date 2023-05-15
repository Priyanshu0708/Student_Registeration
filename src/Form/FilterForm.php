<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
// Use Drupal\Core\Database\Database;.
use Drupal\Core\Url;

// Use Drupal\Core\Routing;.
/**
 * Creating a filter form.
 */
class FilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'filter_student';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = [
      '#type'  => 'fieldset',
      '#title' => $this->t('Filter'),
      '#open'  => TRUE,
    ];
    $form['filters']['email'] = [
      '#title'         => 'Please Enter Email Id....',
      '#type'          => 'textfield',
    ];
    $form['filters']['rno'] = [
      '#title'         => 'Please Enter Roll Number....',
      '#type'          => 'number',
    ];

    $form['filters']['date_from'] = [
      '#title'         => 'from date',
      '#type'          => 'date',
    ];
    $form['filters']['date_to'] = [
      '#title'         => 'to date',
      '#type'          => 'date',
    ];
    $form['filters']['actions'] = [
      '#type'       => 'actions',
    ];
    $form['filters']['save'] = [
      '#type' => 'submit',
      '#value' => 'Filter',
      '#button_type' => 'primary',
    ];
    $form['filters']['reset'] = [
      '#type' => 'submit',
      '#value' => 'Reset',
      '#button_type' => 'secondary',
    ];

    // $form['filters']['actions']['reset'] = [
    // '#type'  => 'submit',
    // '#value' => $this->t('reset')
    // ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $button_clicked = (string) $form_state->getTriggeringElement()['#value'];
    $postData = $form_state->getValues();
    $email = $postData["email"];
    $rno = $postData["rno"];
    $date_from = strtotime($postData["date_from"]);
    $date_to = strtotime($postData["date_to"]);
    if ($button_clicked == "Filter") {
      $url = Url::fromRoute('student_registration.getStudentDetails')
        ->setRouteParameters([
          'email' => $email,
          'rno' => $rno,
          'date_from' => $date_from,
          'date_to' => $date_to,
        ]);
      $form_state->setRedirectUrl($url);
    }
    elseif ($button_clicked == "Reset") {
      $form_state->setRedirect('student_registration.getStudentDetails');
    }

  }

}
