(() => {
	
	const blocks = document.querySelectorAll('.developer-presentation');

	blocks.forEach(block => {
		const tabs = block.querySelectorAll('.developer-tab-btn');
    
		tabs.forEach(tab => {
			tab.addEventListener('click', event => {
				tabs.forEach(tab => {
					tab.classList.remove('active-tab');
				});
				event.target.classList.add('active-tab');
			});
		});
	});

})();