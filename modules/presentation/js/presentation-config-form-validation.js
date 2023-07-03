(() => {
  Drupal.behaviors.presentation_config_form_validation = {
    attach: function (context, settings) {
      /* Turn off front end require validation on settings. */
      const pluginSettings = document.querySelectorAll('.plugin-settings-wrapper');
      pluginSettings.forEach(function(element) {
        const requiredFields = element.querySelectorAll('[required="required"]');

        requiredFields.forEach(function(element) {
          element.removeAttribute('required');
        })
      });
    }
  };
})();