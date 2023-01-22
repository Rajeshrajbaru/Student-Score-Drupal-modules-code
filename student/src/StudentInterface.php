<?php

namespace Drupal\student;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a student entity type.
 */
interface StudentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the student title.
   *
   * @return string
   *   Title of the student.
   */
  public function getTitle();

  /**
   * Sets the student title.
   *
   * @param string $title
   *   The student title.
   *
   * @return \Drupal\student\StudentInterface
   *   The called student entity.
   */
  public function setTitle($title);

  /**
   * Gets the student creation timestamp.
   *
   * @return int
   *   Creation timestamp of the student.
   */
  public function getCreatedTime();

  /**
   * Sets the student creation timestamp.
   *
   * @param int $timestamp
   *   The student creation timestamp.
   *
   * @return \Drupal\student\StudentInterface
   *   The called student entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the student status.
   *
   * @return bool
   *   TRUE if the student is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the student status.
   *
   * @param bool $status
   *   TRUE to enable this student, FALSE to disable.
   *
   * @return \Drupal\student\StudentInterface
   *   The called student entity.
   */
  public function setStatus($status);

}
