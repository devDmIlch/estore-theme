import Splide from "@splidejs/splide";

document.addEventListener('DOMContentLoaded', () => {
	if (!document.body.classList.contains('home')) {
		return;
	}

	// Create object with preferences for sale slider.
	const saleSliderPrefs = {
		type: 'loop',
		gap: 24,
		arrows: false,
		pagination: false,
		autoplay: true,
		interval: 10000,
	};

	// Create slider with thumbnails.
	const salesSlider = document.querySelector('.sales-slider');
	// Check whether the slider exists.
	if (salesSlider) {
		// Mount slider.
		new Splide(salesSlider, saleSliderPrefs).mount();
		// Initialize links on each of the slide.
		salesSlider.querySelectorAll('.single-slide[href]').forEach((slide) => {
			// Initialize link to the item.
			slide.addEventListener('click', (e) => {
				e.preventDefault();
				// Redirect to the link in the slide's attributes.
				window.location.href = slide.getAttribute('href');
			});
		});
	}

	// Initialize home page menu.
	const homeMenu = document.querySelector('.home-menu');
	// Check if the menu exists on the page.
	if (homeMenu) {
		// Initialize dropdowns inside the menus.
		homeMenu.querySelectorAll('.dropdown-trigger').forEach((triggerEl) => {
			// Save trigger relation value
			const triggerRelation = triggerEl.getAttribute('relation');
			// Find target element for the trigger.
			const targetEl = homeMenu.querySelector('.dropdown-target[relation="' + triggerRelation + '"]');

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
