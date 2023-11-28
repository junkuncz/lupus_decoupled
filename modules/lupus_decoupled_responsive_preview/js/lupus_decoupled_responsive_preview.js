/**
 * @file
 * Overwrites behaviour of responsive_preview/js/responsive-preview.js:
 * Adds the possibility to define an absolute responsive_preview.url.
 *
 * @see responsive-preview.js
 */
(function ($, Drupal, drupalSettings) {
  // Submit preview node form on responsive Preview link.
  const $previewSubmit = $('input[data-drupal-selector*="edit-preview"]');
  if ($previewSubmit.length) {
    $('.responsive-preview-preview-link').click(function (e) {
      e.preventDefault();
      $previewSubmit.click();
    });
  }

  /**
   * Registers behaviours related to view widget.
   */
  Drupal.responsivePreview.PreviewView =
    Drupal.responsivePreview.PreviewView.extend({
      render() {
        // Refresh the preview.
        this._refresh();
        Drupal.displace();

        // Render the state of the preview.
        const that = this;
        // Wrap the call in a setTimeout so that it invokes in the next compute
        // cycle, causing the CSS animations to render in the first pass.
        window.setTimeout(function () {
          that.$el.toggleClass('active', that.model.get('isActive'));
        }, 0);

        const $container = this.$el.find('#responsive-preview-frame-container');
        const $frame = $container.find('#responsive-preview-frame');
        const $url = drupalSettings.responsive_preview.url;
        const $frontendBaseUrl = drupalSettings.lupus_decoupled_frontend_url;
        if ($frontendBaseUrl && $url.indexOf('://') < 0) {
          $frame.get(0).contentWindow.location = `${$frontendBaseUrl}/${$url}`;
        } else {
          $frame.get(0).contentWindow.location = $url;
        }

        return this;
      },
    });
})(jQuery, Drupal, drupalSettings);
