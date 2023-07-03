(() => {
  Drupal.behaviors.presentation_config_form = {
    attach: function (context, settings) {
      /* Turn off front end require validation on settings. */
      const pluginSettings = document.querySelectorAll('.plugin-settings-wrapper');
      pluginSettings.forEach(function(element) {
        const requiredFields = element.querySelectorAll('[required="required"]');

        requiredFields.forEach(function(element) {
          element.removeAttribute('required');
        })
      });

      /* Repair possibility to select plugin checkbox trough his label */
      const pluginsSwitchers = document.querySelectorAll('.plugin-settings-switch + label');

      pluginsSwitchers.forEach(function(element) {
        element.setAttribute('for', element.getAttribute('for').split('--')[0]);
      });
    }
  };
})();
