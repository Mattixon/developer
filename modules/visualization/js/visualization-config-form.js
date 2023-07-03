(() => {
  Drupal.behaviors.visualization_config_form = {
    attach: function (context, settings) {
      /* Repair possibility to select checkbox trough his label */
      const checkboxLabel = document.querySelector('#edit-settings-visualization-settings-content-start-from-building + label');
      checkboxLabel.setAttribute('for', checkboxLabel.getAttribute('for').split('--')[0]);
    }
  };
})();
