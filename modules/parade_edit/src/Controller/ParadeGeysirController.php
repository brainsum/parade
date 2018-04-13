<?php

namespace Drupal\parade_edit\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\workbench_moderation\ModerationInformationInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\geysir\Ajax\GeysirOpenModalDialogCommand;
use Drupal\geysir\Controller\GeysirModalController;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Controller for up and down actions.
 */
class ParadeGeysirController extends GeysirModalController {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * The moderation information.
   *
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  private $moderationInformation;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityFieldManager $entityFieldManager, ModerationInformationInterface $moderation_information) {
    parent::__construct($entityFieldManager);
    $this->entityFieldManager = $entityFieldManager;
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('workbench_moderation.moderation_information')
    );
  }

  /**
   * Override default to add empty button array.
   *
   * {@inheritdoc}
   */
  public function add($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $position, $js = 'nojs', $bundle = NULL) {
    if ($js == 'ajax') {
      $response = new AjaxResponse();
      $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);

      if ($bundle) {
        $newParagraph = Paragraph::create(['type' => $bundle]);
        $form = $this->entityFormBuilder()->getForm($newParagraph, 'geysir_modal_add', []);

        $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form), ["buttons" => []]));
      }
      else {
        $entity = $this->entityTypeManager()->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);
        $bundle_fields = $this->entityFieldManager->getFieldDefinitions($parent_entity_type, $entity->bundle());
        $field_definition = $bundle_fields[$field];
        $bundles = $field_definition->getSetting('handler_settings')['target_bundles'];

        $routeParams = [
          'parent_entity_type'   => $parent_entity_type,
          'parent_entity_bundle' => $parent_entity_bundle,
          'parent_entity_revision' => $parent_entity_revision,
          'field'                => $field,
          'field_wrapper_id'     => $field_wrapper_id,
          'delta'                => $delta,
          'paragraph'            => $paragraph->id(),
          'paragraph_revision'   => $paragraph->getRevisionId(),
          'position'             => $position,
          'js'                   => $js,
        ];

        $form = \Drupal::formBuilder()->getForm('\Drupal\geysir\Form\GeysirModalParagraphAddSelectTypeForm', $routeParams, $bundles);
        $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form), ["buttons" => []]));
      }

      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

  /**
   * Preferences modal - node edit without paragraph reference field.
   *
   * {@inheritdoc}
   */
  public function preferences($entity_type, $node, $js = 'nojs') {
    if ($js == 'ajax') {
      $options = [
        'width' => '60%',
        'modal' => TRUE,
      ];
      $response = new AjaxResponse();
      $entity_revision = $this->moderationInformation->getLatestRevision($entity_type, $node->id());
      $form = $this->entityFormBuilder()->getForm($entity_revision, 'preferences', []);
      $form['#attributes']['class'][] = 'node-parade-onepage-edit-form';
      $form['#attributes']['class'][] = 'node-parade-onepage-form';
      $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Edit %label', ['%label' => $node->label()]), render($form), $options));

      return $response;
    }

    return [
      '#markup' => $this->t('Javascript is required for this functionality to work properly.'),
    ];
  }

}
