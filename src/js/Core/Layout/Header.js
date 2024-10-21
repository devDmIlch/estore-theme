
document.addEventListener('DOMContentLoaded', () => {
	// Find page header.
	const pageHeader = document.querySelector('.website-header');

	// Bail if the header is not present on the page, as everything below requires header.
	if (pageHeader === null) {
		return;
	}

	// Initialize fancy scrolling header.
	window.addEventListener('scroll', (e) => {
		if (window.scrollY > 20) {
			pageHeader.classList.add('shrink');
		} else {
			pageHeader.classList.remove('shrink');
		}
	});

	// Initialize search field.
	const searchField = pageHeader.querySelector('.search-box');
	if (searchField) {
		// Find search redirect button.
		const searchRedirectButton = pageHeader.querySelector('.search-submit');
		// Save default search link.
		const defaultSearchLink = window.location.origin + '/search/';

		searchField.addEventListener('keyup', () => {
			// Update state of the link button
			if (searchField.value.length === 0) {
				searchRedirectButton.classList.add('inactive');
			} else {
				searchRedirectButton.classList.remove('inactive');
			}
			// Update link for the link button.
			searchRedirectButton.setAttribute('href', defaultSearchLink + searchField.value);
		});

		searchField.addEventListener('keypress', (e) => {
			// Trigger search on clicking 'Enter'.
			if (e.keyCode === 13) {
				searchRedirectButton.click();
			}
		});
	}

	// Initialize header menu.
	const headerMenu = pageHeader.querySelector('.header-menu');
	// Check if the header menu exists (it is embedded on the front page).
	if (headerMenu) {
		// Initialize menu trigger.
		const headerMenuTrigger = pageHeader.querySelector('.menu-trigger');
		headerMenuTrigger.addEventListener('click', () => headerMenu.classList.toggle('inactive'));

		// Initialize dropdowns inside the menus.
		headerMenu.querySelectorAll('.dropdown-trigger').forEach((triggerEl) => {
			// Save trigger relation value
			const triggerRelation = triggerEl.getAttribute('relation');
			// Find target element for the trigger.
			const targetEl = headerMenu.querySelector('.dropdown-target[relation="' + triggerRelation + '"]');

			if (targetEl) {
				// Update classes to show/hide target dropdown on clicking trigger.
				triggerEl.addEventListener('click', () => {
					// Update class of the trigger element to flip the caret.
					const isActive = triggerEl.classList.toggle('active');

					targetEl.parentElement.style.maxHeight = (isActive ? targetEl.offsetHeight : 0) + 'px';
				});
			}
		});
	}
});
