<?php

namespace Drupal\student_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\user\Entity\User;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Creating a student form.
 */
class StudentForm extends FormBase {
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
  protected $languagemanager;

  /**
   * The messenger, factory, database, languageManager.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Connection Database Variable.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger variable.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $factory
   *   Factory Variable.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   languageManager variable.
   */
  public function __construct(Connection $database, MessengerInterface $messenger, LoggerChannelFactoryInterface $factory, LanguageManagerInterface $language_manager) {
    $this->messenger = $messenger;
    $this->loggerFactory = $factory;
    $this->database = $database;
    $this->languageManager = $language_manager;
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_student';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
      '#title' => $this->t('Email Id:'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Email Id....',
      ],
    ];
    $form['fname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name:'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your First Name....',
      ],
    ];
    $form['lname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name:'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Last Name....',
      ],
    ];
    $form['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender:'),
      '#required' => TRUE,
      '#options' => $genderOptions,
    ];
    $form['age'] = [
      '#type' => 'number',
      '#title' => $this->t('Age:'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Age....',
      ],
    ];
    $form['rno'] = [
      '#type' => 'number',
      '#title' => $this->t('Roll Number :'),
      '#default_value' => '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Please Enter Your Roll Number....',
      ],
    ];
    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date Of Admission :'),
      '#default_value' => '',
      '#required' => TRUE,
    ];
    $form['interests'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Interests:'),
      '#required' => TRUE,
      '#options' => $interestsOptions,
    ];
    $form['notes'] = [
      '#type' => 'textarea',
      '#title' => 'Additional Notes',
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => 'About Employees',
      ],
    ];
    $form['save'] = [
      '#type' => 'submit',
      '#value' => 'Save Student Details',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $fname = $form_state->getValue('fname');
    $rno = $form_state->getValue('rno');
    $lname = $form_state->getValue('lname');
    $age = $form_state->getValue('age');
    // $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
    $regex1 = '/^[a-z]{3,20}$/i';
    $sql_email = $this->database->select('students', 's')
      ->fields('s', ['email'])->condition('s.email', $email, '=')
      ->execute()->fetchField();
    // dpm($sql_email);
    if ($sql_email != FALSE) {
      $form_state->setErrorByName('email', $this->t('This email address already exist, Please enter another email address'));
    }
    $sql_rno = $this->database->select('students', 's')
      ->fields('s', ['rno'])->condition('s.rno', $rno, '=')
      ->execute()->fetchField();
    // dpm($sql);
    if ($sql_rno != FALSE) {
      $form_state->setErrorByName('rno', $this->t('This Roll Number already exist, Please enter another Roll Number'));
    }
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
      $conn->insert('students')
        ->fields($fields)->execute();
      $this->messenger->addMessage(("Thank you " . $form_state->getValue('fname') . ' ' . $form_state->getValue('lname') . ", your details are registered..!"));
      // Create a user Programmaticially.
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $user = User::create();
      $user->setPassword(user_password());
      $user->enforceIsNew();
      $user->setEmail($fields["email"]);
      $user->setUsername($fields["email"]);
      $user->addRole('stude');

      // Optional.
      $user->set("init", $fields["email"]);
      $user->set("langcode", $language);
      $user->set("preferred_langcode", $language);
      $user->set("preferred_admin_langcode", $language);
      // Activate the user account.
      $user->activate();
      // Set user fields.
      $user->set('field_firstname', $fields["fname"]);
      $user->set('field_lastname', $fields["lname"]);
      $user->set('field_gender', $fields["gender"]);
      $user->set('field_age', $fields["age"]);
      $user->set('field_rno', $fields["rno"]);
      $user->set('field_date', $form_state->getValue('date'));
      $user->set('field_interests', $fields["interests"]);
      $user->set('field_notes', $fields["notes"]);
      $user->save();
      user_login_finalize($user);
    }
    catch (Exception $ex) {
      $this->loggerFactory('student_registration')->error($ex->getMessage());
    }

  }

}
