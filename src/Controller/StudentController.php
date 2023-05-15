<?php

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

// Use Drupal\Code\Database\Database;.
// Use Drupal\Core\Form\FormBase;.
// Use Drupal\Core\Url;.
// Use Drupal\Core\Routing;.
// Use Drupal\Core\Form\FormStateInterface;.
/**
 * Function to get student details from database to filter the form.
 */
class StudentController extends ControllerBase {
  /**
   * The request stack service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Construct function.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request parameter.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiate this block class.
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * GetStudentDetails.
   *
   * @return string
   *   Return Table format data.
   */
  public function getStudentDetails() {
    $filter_form = $this->formBuilder()->getForm('Drupal\student_registration\Form\FilterForm');
    // Get parameter value while submitting filter form.
    $email = $this->requestStack->getCurrentRequest()->query->get('email');
    $rno = $this->requestStack->getCurrentRequest()->query->get('rno');
    $date_from = $this->requestStack->getCurrentRequest()->query->get('date_from');
    $date_to = $this->requestStack->getCurrentRequest()->query->get('date_to');
    // Create table header.
    $header = ['Email',
      'First Name',
      'Last Name',
      'Gender',
      'Age',
      'Roll Number',
      'Date Of Admission',
      'Interests',
      'Notes',
    ];
    if ($email == "" && $rno == "" && $date_from == "" && $date_to == "") {
      $form['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => student_registration_get_students("All", "", "", "", ""),
        '#empty' => $this->t('No users found'),
      ];
    }
    else {
      $form['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => student_registration_get_students("", $email, $rno, $date_from, $date_to),
        '#empty' => $this->t('No records found'),
      ];
    }
    $form['pager'] = [
      '#type' => 'pager',
    ];
    return [
      $filter_form,
      $form,
    ];
    // $query = \Drupal::database();
    // $result = $query->select('students','s')
    // ->fields('s',['email','fname','lname','gender','age','rno','date','interests','notes'])
    // ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20)
    // ->execute()->fetchAll(\PDO::FETCH_OBJ);
    // $data=[];
    // foreach($result as $row){
    // $data[] =[
    // 'email'=>$row->email,
    // 'fname'=>$row->fname,
    // 'lname'=>$row->lname,
    // 'gender'=>$row->gender,
    // 'age'=>$row->age,
    // 'rno'=>$row->rno,
    // date'=>$row->date,
    // interests'=>$row->interests,
    // notes'=>$row->notes,
    // ];
    // }
    // $build['table']=[
    // '#type'=>'table',
    // '#header'=>$header,
    // '#rows'=>$data,
    // '#empty' => $this->t('No records found'),
    // ];
    // $build['pager']=[
    // '#type'=>'pager',
    // ];
    // return[
    // $form,
    // $build,
    // '#title'=>'Students List'
    // ];
  }

}
