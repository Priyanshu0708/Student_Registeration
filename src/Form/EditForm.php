<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * StudentForm to save the student details.
 */
class EditForm extends FormBase {
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $factory;
  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The messenger, factory, database, language_manager.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Connection Database Variable.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger variable.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $factory
   *   Factory Variable.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager Variable.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route match.
   */
  public function __construct(Connection $database, MessengerInterface $messenger, LoggerChannelFactoryInterface $factory, LanguageManagerInterface $language_manager, CurrentRouteMatch $currentRouteMatch) {
    $this->messenger = $messenger;
    $this->loggerFactory = $factory;
    $this->database = $database;
    $this->languageManager = $language_manager;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('database'),
      $container->get('messenger'),
      $container->get('logger.factory'),
       $container->get('language_manager'),
       $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_student_details';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $id = $this->currentRouteMatch->getParameter('id');
    $data = $this->database->select('students', 's')
      ->fields('s', [
        'email',
        'fname',
        'lname',
        'gender',
        'age',
        'rno',
        'date',
        'interests',
        'notes',
      ])
      ->condition('s.rno', $id, '=')
      ->execute()->fetchAll(\PDO::FETCH_OBJ);
    $genderOptions = [
      'Male' => 'Male',
      'Female' => 'Female',
    ];
    $interestsOptions = [
      'Cricket' => 'Cricket',
      'Football' => 'Football',
      'Dancing' => 'Dancing',
      'Singing' => 'Singing',
      'Painting' => 'Painting',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => ('Email Id:'),
      '#default_value' => $data[0]->email,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Email Id....',
      ],
    ];
    $form['fname'] = [
      '#type' => 'textfield',
      '#title' => ('First Name:'),
      '#default_value' => $data[0]->fname,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your First Name....',
      ],
    ];
    $form['lname'] = [
      '#type' => 'textfield',
      '#title' => ('Last Name:'),
      '#default_value' => $data[0]->lname,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Last Name....',
      ],
    ];
    $form['gender'] = [
      '#type' => 'select',
      '#title' => ('Gender:'),
      '#required' => TRUE,
      '#options' => $genderOptions,
      '#default_value' => $data[0]->gender,
    ];
    $form['age'] = [
      '#type' => 'number',
      '#title' => ('Age:'),
      '#default_value' => $data[0]->age,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Age....',
      ],
    ];
    $form['rno'] = [
      '#type' => 'number',
      '#title' => ('Roll Number :'),
      '#default_value' => $data[0]->rno,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Roll Number....',
      ],
    ];
    $form['date'] = [
      '#type' => 'date',
      '#title' => ('Date Of Admission :'),
      '#default_value' => date("d/m/Y", $data[0]->date),
      '#required' => TRUE,
    ];
    $form['interests'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => ('Interests:'),
      '#required' => TRUE,
      '#options' => $interestsOptions,
      '#default_value' => $data[0]->interests,
    ];
    $form['notes'] = [
      '#type' => 'textarea',
      '#title' => 'Additional Notes',
      '#default_value' => $data[0]->notes,
      '#attributes' => [
        'placeholder' => 'About Employees',
      ],
    ];
    $form['update'] = [
      '#type' => 'submit',
      '#value' => 'Save Student User Details',
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $fname = $form_state->getValue('fname');
    $lname = $form_state->getValue('lname');
    $age = $form_state->getValue('age');
    $regex1 = '/^[a-z]{3,20}$/i';
    if ($age < 0 || $age > 100) {
      $form_state->setErrorByName('age', $this->t('Please Enter a valid age'));
    }
    if ($rno < 0 || $rno > 60) {
      $form_state->setErrorByName('rno', $this->t('Please Enter a valid Roll Number'));
    }
    if (!preg_match($regex1, $fname)) {
      $form_state->setErrorByName('fname', $this->t('Please enter a valid First name'));
    }
    if (!preg_match($regex1, $lname)) {
      $form_state->setErrorByName('lname', $this->t('Please enter a valid Last name'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $this->currentRouteMatch->getParameter('id');
    try {
      $conn = Database::getConnection();
      $field = $form_state->getValues();
      $fields["email"] = $field['email'];
      $fields["fname"] = $field['fname'];
      $fields["lname"] = $field['lname'];
      $fields["gender"] = $field['gender'];
      $fields["age"] = $field['age'];
      $fields["rno"] = $field['rno'];
      $fields["date"] = strtotime($field['date']);
      $fields["interests"] = implode(",", $field['interests']);
      $fields["notes"] = $field['notes'];
      $conn->update('students')
        ->fields($fields)
        ->condition("rno", $id)
        ->execute();
        $form_state->setRedirect('<front>');
      $this->messenger->addMessage(("Thank you " . $form_state->getValue('fname') . ' ' . $form_state->getValue('lname') . ", your details updated successfully..!"));
    }
    catch (Exception $ex) {
      $this->loggerFactory('student_registration')->error($ex->getMessage());
    }
  }

}
