<?php

namespace Drupal\Student\Form;
use Drupal\student\Entity;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * StudentImport form for uploading archived csv and icons.
 */
class StudentImport extends ConfigFormBase {
  use StringTranslationTrait;
  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The file storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $langManager;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $file_storage
   *   The file storage manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $lang_manager
   *   The language manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user manager.
   */
  public function __construct(EntityFieldManager $entity_field_manager,
    EntityStorageInterface $file_storage,
    LanguageManagerInterface $lang_manager,
    AccountProxyInterface $current_user) {
    $this->entityFieldManager = $entity_field_manager;
    $this->fileStorage = $file_storage;
    $this->langManager = $lang_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')->getStorage('file'),
      $container->get('language_manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'student.studentimport',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['import_csv_file'] = [
      '#type'                 => 'managed_file',
      '#upload_location'      => 'public://store_locator/csv/',
      '#multiple'             => FALSE,
      '#description'          => $this->t(
        " <b>Allowed extensions: csv</b>",
        ),
      '#required' => TRUE,
      '#upload_validators'    => [
        'file_validate_extensions'    => ['csv'],
        'file_validate_size'          => [25600000],
      ],
      '#title'                => $this->t('Stores list CSV File'),
    ];

   
    $markup = "<div id='your_identifier'>
      <li>Upload both CSV <b>Import Students and marks</b></li></div>";
    $form['details'] = [
      '#markup' => $markup,
      '#allowed_tags' => ['div', 'li', 'ul', 'span', 'a', 'b'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $csv_file_uri = $this->getFileUrl($form_state->getValue('import_csv_file'));
  
    // Call node creation function.
    if (!empty($csv_file_uri)) {
      $student_data=[];
      $csvData = file_get_contents($csv_file_uri);
      $lines = explode(PHP_EOL, $csvData);
      foreach ($lines as $line) {
        $csv_row = str_getcsv($line);
        if (count($csv_row) > 1 && !empty($csv_row[0])) {
          $student_data[] = str_getcsv($line);
        }
      }
    }


    $this->importCsvData($student_data);
  }


  /**
   * Import CSV data and create stores.
   *
   * @param mixed $student_data
   *   CSV node data.
   */
  public function importCsvData($student_data) {
    
    array_shift($student_data);
    foreach($student_data as $student)
    {

     $roll_number = $student['0'];
     $student_name = $student['1'];
     $class = $student['2'];
     $subject = $student['3'];
     $score = $student['3'];

     $student_query = "SELECT student__field_roll_number.entity_id
FROM student__field_roll_number
INNER JOIN student__field_class ON student__field_roll_number.entity_id = student__field_class.entity_id
WHERE  student__field_class.field_class_value =  
'$class'  AND student__field_roll_number.field_roll_number_value='$roll_number'";


      $database = \Drupal::database();
      $query = $database->query($student_query);
      $student_result = $query->fetchAll();
      if(!empty($student_result))
      {
       
      }
      else
      {

         $student_save = \Drupal::entityTypeManager()->getStorage('student')->create([ 'title' => $student_name ,'field_class' => $class, 'field_roll_number' => $roll_number]);
         $student_save->save();
      }

  
     $query = "SELECT score__field_subject.entity_id
FROM score__field_subject
INNER JOIN score__field_class ON score__field_subject.entity_id = score__field_class.entity_id
INNER JOIN score__field_roll_number ON score__field_subject.entity_id = score__field_roll_number.entity_id 
WHERE score__field_subject.field_subject_value = '$subject'  AND score__field_class.field_class_value=  
'$class'  AND score__field_roll_number.field_roll_number_value='$roll_number'";

    $database = \Drupal::database();
    $query = $database->query($query);
    $result = $query->fetchAll();

    if(empty($result))
    {
         $score_entity = \Drupal::entityTypeManager()->getStorage('score')->create([ 'field_roll_number' => $roll_number, 'field_class' => $class , 'field_score'=>$student['4'] , 'field_subject' => $student['3']]);
          $score_entity->save();   
    }
    else
    {
      $score_entity = \Drupal::entityTypeManager()->getStorage('score')->load($result['0']->entity_id);
      $score_entity->field_score = $score;
      $score_entity->save();
     }
   }
   \Drupal::messenger()->addMessage('All data is imported');
  }


/**
   * Get File URI.
   *
   * @param mixed $form_field
   *   Form field.
   *
   * @return string
   *   File URI.
   */
  public function getFileUrl($form_field) {
    $file_uri = '';
    if (!empty($form_field)) {
      $file = $this->fileStorage->load($form_field[0]);
      $file_uri = $file->getFileUri();
    }
    return $file_uri;
  }
}



