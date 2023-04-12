<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * The style plugin for custom elements format.
 *
 * @ViewsStyle(
 *   id = "custom_elements",
 *   title = @Translation("Custom Elements"),
 *   help = @Translation("Render the view as JSON."),
 *   theme = "views_view_custom_elements",
 *   display_types = {"normal"}
 * )
 */
class CustomElements extends StylePluginBase {

  /**
   * Pager None class.
   *
   * @var string
   */
  const PAGER_NONE = 'Drupal\views\Plugin\views\pager\None';

  /**
   * Pager Some class.
   *
   * @var string
   */
  const PAGER_SOME = 'Drupal\views\Plugin\views\pager\Some';

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // The Serializer parent offers JSON (default) and XML. We only want to make use of JSON so hide the option.
    unset($form['formats']);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $result = ['rows' => []];

    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row) {
      $build = $this->view->rowPlugin->render($row);
      $custom_element = CustomElement::createFromRenderArray($build);
      $result['rows'][] = $custom_element;
    }

    if ($this->view->pager) {
      $result['pager'] = $this->pagination($result['rows']);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function pagination($rows) {
    $pagination = [];
    $class = NULL;

    $pager = $this->view->pager;

    if ($pager) {
      $class = get_class($pager);
    }

    if ($class === NULL) {
      return NULL;
    }

    if (method_exists($pager, 'getPagerTotal')) {
      $pagination['total_pages'] = $pager->getPagerTotal();
    }
    if (method_exists($pager, 'getCurrentPage')) {
      $pagination['current'] = $pager->getCurrentPage() ?? 0;
    }
    if ($class == static::PAGER_NONE) {
      $pagination['items_per_page'] = $pager->getTotalItems();
    }
    elseif ($class == static::PAGER_SOME) {
      $pagination['total_items'] = count($rows);
    }

    return $pagination;
  }
}
