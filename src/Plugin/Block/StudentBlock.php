<?php

namespace Drupal\student_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Database\Connection;

/**
 * Registration block.
 *
 * @Block(
 *   id = "student_block",
 *   admin_label = @Translation("Student Registration Form"),
 *   category = @Translation("Custom Student Registration Form")
 * )
 */
class StudentBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;
  /**
   * The account service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Construct function.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Database\Connection $database
   *   Connection Database Variable.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, AccountProxyInterface $current_user, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->currentUser = $current_user;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Instantiate this block class.
    return new static($configuration, $plugin_id, $plugin_definition,
      // Load the service required to construct this class.
      $container->get('form_builder'),
      $container->get('current_user'),
       $container->get('database'),

    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = $this->formBuilder->getForm('Drupal\student_registration\Form\StudentForm');
    $email = $this->currentUser->getEmail();
    $roles = $this->currentUser->getRoles();
    $user = $this->database->select('students', 's')
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
      ->condition('s.email', $email, '=')
      ->execute()
      ->fetchAll(\PDO::FETCH_OBJ);
    if (in_array('stude', $roles)) {
      return [
        '#theme' => 'student_update',
        '#title' => 'Student User Details',
        '#data' => $user,
      ];
    }
    else {
      return $form;
    }

  }

}
