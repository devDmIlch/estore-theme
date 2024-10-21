/* global wpApiSettings, postPrefs */

import axios from "axios";

import Splide from "@splidejs/splide";

const StoreItemPostController = {
	defaultSliderPrefs: {
		type: 'slide',
		gap: 24,
		arrows: false,
		pagination: false,
	},

	sliderNavigation: {
		type: 'slide',
		gap: 8,
		focus: 'center',
		trimSpace: true,
		perPage: 7,
		clones: 0,
		pagination: false,
		isNavigation: true,
	},

	addToCartPath: '/wp-json/estore/cart/add-item',

	requestConfig: {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		},
	},

	initImgCarousel() {
		// Create slider with thumbnails.
		const mainSlider = new Splide('.var-slider', this.defaultSliderPrefs).mount();

		// Create slider only if the navigation element was found, otherwise there is only one item.
		if (document.querySelector('.var-slider-nav')) {
			// Create navigation slider.
			const navSlider = new Splide('.var-slider-nav', this.sliderNavigation).mount();
			// Sync two sliders.
			navSlider.sync(mainSlider);
		}
	},

	initPageSwitch() {
		const itemSubMenu = document.querySelector('.store-item-menu');
		if (itemSubMenu) {
			let lastActiveTrigger = document.querySelector('.sub-nav.active');

			let lastActiveSection = document.querySelector('.subpage.active');

			itemSubMenu.querySelectorAll('.sub-nav').forEach(subNav => {
				// Get target subpage.
				const targetName = subNav.getAttribute('option');
				// Target subpage related to the trigger.
				const targetSubPage = document.querySelector('.subpage[subpage="' + targetName + '"]');
				// Add event listener for the trigger.
				subNav.addEventListener('click', () => {
					// Check whether the target was found.
					if (targetSubPage && !targetSubPage.classList.contains('active')) {
						// Change active subpage.
						lastActiveSection.classList.remove('active');
						targetSubPage.classList.add('active');
						lastActiveSection = targetSubPage;
						// Change active subpage navigation trigger.
						lastActiveTrigger.classList.remove('active');
						subNav.classList.add('active');
						lastActiveTrigger = subNav;
					}
				});
			});
		}
	},

	initAddToCard() {
		// Initialize card parameters with data passed through localization lines.
		const productParams = {
			post_id: postPrefs.post_id,
			var_id: postPrefs.var_id,
		};
		// Find add to cart button.
		const addToCartButton = document.querySelector('.add-to-cart');
		// Add actions to the addToCartButton.
		if (addToCartButton) {
			// Create a flag to prevent overlapping calls.
			let isCartButtonSuspended = false;
			// Find the cart pop-up on the page to refresh it's content upon adding new items.
			const cartPopUp = document.querySelector('.cart-pop-up');
			// Find the cart pop-up trigger to update bubble with the newly added items.
			const cartPopUpTrigger = document.querySelector('.cart-trigger');

			addToCartButton.addEventListener('click', () => {
				// Check if the action was already called.
				if (isCartButtonSuspended) {
					return;
				}
				// Set active flag to true.
				isCartButtonSuspended = true;
				// Add class to show that the button is active.
				addToCartButton.classList.add('button-loading');

				axios.post(this.addToCartPath, productParams, this.requestConfig).then((response) => {
					// Update the flag.
					isCartButtonSuspended = false;
					// Update the class.
					addToCartButton.classList.remove('button-loading');
					// Call action to refresh the content of the cart.
					if (cartPopUp) {
						cartPopUp.dispatchEvent(new CustomEvent('refresh'));
					}
					// Update counter of the newly added items.
					if (cartPopUpTrigger) {
						cartPopUpTrigger.setAttribute('new', cartPopUpTrigger.hasAttribute('new') ? Number(cartPopUpTrigger.getAttribute('new')) + 1 : 1);
					}
				});
			});
		}
	},

	initStoreItemPost() {
		// Initialize carousel for product.
		this.initImgCarousel();
		// Initialize switching between different tabs.
		this.initPageSwitch();
		// Initialize adding to cart.
		this.initAddToCard();
	},
}

document.addEventListener('DOMContentLoaded', () => {
	if (document.querySelector('body').classList.contains('single-store-item')) {
		StoreItemPostController.initStoreItemPost();
	}
});
