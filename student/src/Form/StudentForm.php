<?php

namespace Drupal\student\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the student entity edit forms.
 */
class StudentForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New student %label has been created.', $message_arguments));
      $this->logger('student')->notice('Created new student %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The student %label has been updated.', $message_arguments));
      $this->logger('student')->notice('Updated new student %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.student.canonical', ['student' => $entity->id()]);
  }

}
