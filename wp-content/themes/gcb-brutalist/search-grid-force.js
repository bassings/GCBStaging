// Force search grid cards to 400px height via JavaScript
document.addEventListener('DOMContentLoaded', function() {
	const cards = document.querySelectorAll('.search-results-grid .bento-item.gcb-bento-card');
	cards.forEach(card => {
		card.style.setProperty('height', '400px', 'important');
		card.style.setProperty('min-height', '400px', 'important');
		card.style.setProperty('max-height', '400px', 'important');
	});
});
