<?php

namespace Drupal\developer\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The DeveloperData service.
 */
class DeveloperDataService {

  /**
   * Constructs a DeveloperDataService object.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {}

  /**
   * Get all estates.
   */
  public function getEstates(): array {
    return $this->entityTypeManager->getStorage('developer_estate')->loadMultiple();
  }

  /**
   * Get all buildings.
   */
  public function getBuildings(): array {
    return $this->entityTypeManager->getStorage('developer_building')->loadMultiple();
  }

  /**
   * Get all floors.
   */
  public function getFloors(): array {
    return $this->entityTypeManager->getStorage('developer_floor')->loadMultiple();
  }

  /**
   * Get all flats.
   */
  public function getFlats(): array {
    return $this->entityTypeManager->getStorage('developer_flat')->loadMultiple();
  }

}
