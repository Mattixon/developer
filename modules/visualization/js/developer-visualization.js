((Drupal) => {
	Drupal.behaviors.developer_visualization = {
		attach: function (context, settings) {
      const blocks = document.querySelectorAll('.developer-presentation');

      blocks.forEach(block => {
        const svgContainer = block.querySelector('.visualization-svg-container');

        if (svgContainer) {
          const svg = svgContainer.querySelector('svg');
          const entityName = svg.dataset.entityName;
          const svgPaths = svg.querySelectorAll('path');
          const backBtn = svgContainer.querySelector('.back-btn');
          const sellEntity = svg.dataset.sellEntityName;
          const navigationContainer = svgContainer.querySelector('.navigation-container');

          if (entityName === 'flat') {
            svgContainer.style.paddingBottom = '80px';
          }
    
          svgPaths.forEach(function(element) {

            /* Hover & move event on stage with status. */
            if (
              element.classList[0] !== 'sold' &&
              (sellEntity === 'building' && entityName === 'estate') ||
              (sellEntity === 'flat' && entityName === 'floor')
            ) {
              
              /* Mouseover event */
              element.addEventListener('mouseover', event => {
                
                const targetOpacity = event.target.parentNode.dataset.pathTargetOpacity;
                event.target.setAttribute('fill-opacity', targetOpacity);
                const flatId = event.target.dataset.subentityId;
                const relatedTooltip = svgContainer.querySelector('#entity-id-' + flatId);
    
                if (relatedTooltip) {
                  relatedTooltip.style.display = "inline-block";
                }
              });
    
              element.addEventListener('mousemove', event => {
                const flatId = event.target.dataset.subentityId;
                const relatedTooltip = svgContainer.querySelector('#entity-id-' + flatId);
                const clientY = event.clientY;
                const clientX = event.clientX;
                
                if (relatedTooltip) {
                  relatedTooltip.style.top = clientY + 20 + 'px';
                  relatedTooltip.style.left = clientX + 20 + 'px';
                }
              });
          
              /* Mouseout event */
              element.addEventListener('mouseout', event => {
                const targetOpacity = event.target.parentNode.dataset.pathTargetOpacity;
                event.target.setAttribute('fill-opacity', targetOpacity);
                const flatId = event.target.dataset.subentityId;
                const relatedTooltip = svgContainer.querySelector('#entity-id-' + flatId);
    
                if (relatedTooltip) {
                  relatedTooltip.style.display = "none";
                }
              });
            }
            /* Hover event standard stage. */
            else if (
              (sellEntity === 'building' && entityName !== 'estate') ||
              (sellEntity === 'flat' && entityName !== 'floor')
            ) {
              element.addEventListener('mouseover', event => {
                const targetOpacity = event.target.parentNode.dataset.pathTargetOpacity;
                event.target.setAttribute('fill-opacity', targetOpacity);
              });
          
              element.addEventListener('mouseout', event => {
                event.target.setAttribute('fill-opacity', 0);
              });
            }
    
            /* Click event */
            if (element.classList[0] !== 'sold') {
              element.addEventListener('click', event => {
                const path = event.target;
                const svg = path.parentNode;
                const frontUrl = svgContainer.dataset.frontUrl;
                const blockId = svgContainer.dataset.blockId;
                const entityName = svg.dataset.entityName;
                const subentityId = path.dataset.subentityId;
                const pathFill = svg.dataset.pathFill;
                const pathTargetOpacity = svg.dataset.pathTargetOpacity;
                const startingEntityName = svg.dataset.startingEntityName;
                const sellEntityName = svg.dataset.sellEntityName;
                const webformId = svg.dataset.webformId;
                const imageStyle = svg.dataset.imageStyle;
                let url = '/';

                if (frontUrl !== '') {
                  url = `${frontUrl}`;
                }
                
                url += `developer-visualization/next/${blockId}/${entityName}/${subentityId}/${pathFill}/${pathTargetOpacity}/${startingEntityName}/${sellEntityName}/${webformId}`;
    
                if (imageStyle) {
                  url += `/${imageStyle}`;
                }
    
                Drupal.ajax({url: url}).execute();
              });
            }
          });
    
          /* Go back event */
          if (backBtn) {
            backBtn.addEventListener('click', event => {
              const svg = event.target.parentNode.querySelector('svg');
              const frontUrl = svgContainer.dataset.frontUrl;
              const blockId = svgContainer.dataset.blockId;
              const entityName = svg.dataset.entityName;
              const entityId = svg.dataset.entityId;
              const pathFill = svg.dataset.pathFill;
              const pathTargetOpacity = svg.dataset.pathTargetOpacity;
              const startingEntityName = svg.dataset.startingEntityName;
              const sellEntityName = svg.dataset.sellEntityName;
              const webformId = svg.dataset.webformId;
              const imageStyle = svg.dataset.imageStyle;
              let url = '/';

              if (frontUrl !== '') {
                url = `${frontUrl}`;
              }

              url += `developer-visualization/prev/${blockId}/${entityName}/${entityId}/${pathFill}/${pathTargetOpacity}/${startingEntityName}/${sellEntityName}/${webformId}`;
      
              if (imageStyle) {
                url += `/${imageStyle}`;
              }
      
              Drupal.ajax({url: url}).execute();
            });
          }

          /* Navigation handler */
          if (navigationContainer) {
            const selects = navigationContainer.querySelectorAll('select');

            selects.forEach(function(element) {
              element.addEventListener('change', function(event) {
                const svg = svgContainer.querySelector('svg');
                const frontUrl = svgContainer.dataset.frontUrl;
                const blockId = svgContainer.dataset.blockId;
                const entityName = event.target.classList[0];
                const entityId = this.value;
                const pathFill = svg.dataset.pathFill;
                const pathTargetOpacity = svg.dataset.pathTargetOpacity;
                const startingEntityName = svg.dataset.startingEntityName;
                const sellEntityName = svg.dataset.sellEntityName;
                const webformId = svg.dataset.webformId;
                const imageStyle = svg.dataset.imageStyle;

                let url = '/';

                if (frontUrl !== '') {
                  url = `${frontUrl}`;
                }

                url += `developer-visualization/change/${blockId}/${entityName}/${entityId}/${pathFill}/${pathTargetOpacity}/${startingEntityName}/${sellEntityName}/${webformId}`;
      
                if (imageStyle) {
                  url += `/${imageStyle}`;
                }

                Drupal.ajax({url: url}).execute();
              });
            });
          }
        }
      });
		}
	};
})(Drupal);
