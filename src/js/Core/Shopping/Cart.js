/* global wpApiSettings */

// Non-local dependencies.
import axios from "axios";

// Local dependencies.
import CartItem from "./CartItem";

const CartController = {
	pathStub: '/wp-json/estore/cart/',
	orderPathStub: '/wp-json/estore/order/',
	userPathStub: '/wp-json/estore/user/',

	requestConfig: {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		},
	},

	// Initializes cart in the header
	initDynamicCart() {
		// Find header trigger.
		const popUpTrigger = document.querySelector('.cart-trigger');
		// Find pop-up target for trigger above.
		const popUpTarget = document.querySelector('.cart-pop-up');

		// Create a function to load cart items.
		const loadCartItems = () => {
			axios.post(this.pathStub + 'render-cart', {}, this.requestConfig).then(response => {
				if (response.data.status === 200) {
					// Remove old cart content if it exists.
					popUpTarget.querySelectorAll('.shopping-cart').forEach((cartItemsNode) => {
						cartItemsNode.remove();
					});
					// Insert new card items.
					popUpTarget.insertAdjacentHTML('beforeend', response.data.html);
					// Initialize all cart items' functionality.
					popUpTarget.querySelectorAll('.single-cart-item').forEach(CartItem.initCartItem);
				}
			});
		};

		if (popUpTrigger && popUpTarget) {
			// Since initially content of the cart doesn't load, user this flag to load it only once.
			let isCartContentLoaded = false;

			popUpTrigger.addEventListener('click', () => {
				popUpTarget.classList.toggle('active');
				// Remove the attribute to remove bubble.
				popUpTrigger.removeAttribute('new');

				if (!isCartContentLoaded) {
					loadCartItems();

					isCartContentLoaded = true;
				}
			});

			// Close cart on clicking outside.
			document.addEventListener('click', (e) => {
				if (!popUpTarget.contains(e.target) && !popUpTrigger.contains(e.target)) {
					popUpTarget.classList.remove('active');
				}
			});

			// Add event to refresh cart on demand.
			popUpTarget.addEventListener('refresh', () => {
				// Load cart items.
				loadCartItems();
			});
		}
	},

	initCartPage() {
		// Check if the user is on the cart page.
		const cartPage = document.querySelector('.cart-page');
		// Bail if not on hte cart page.
		if (!cartPage) {
			return;
		}

		// Find the 'Total Price' element.
		const totalPriceEl = cartPage.querySelector('.total-price');

		// Initialize content for all cart items on the page.
		cartPage.querySelectorAll('.single-cart-item').forEach((item) => {
			// Initialize button actions for this item.
			CartItem.initCartItem(item);
			// Load updated price after clicking any cart control button.
			item.addEventListener('change', () => {
				axios.post(this.pathStub + 'get-cart-price', {}, this.requestConfig).then((response) => {
					if (response.data.status === 200) {
						totalPriceEl.innerHTML = response.data.price;
					}
				});
			});
		});

		// Init confirm checkout button.
		const confirmPurchaseButton = cartPage.querySelector('.order-confirm');
		if (confirmPurchaseButton) {
			// Find input fields.
			const middleNameField = cartPage.querySelector('input[name="middle-name"]');
			const firstNameField = cartPage.querySelector('input[name="first-name"]');
			const lastNameField = cartPage.querySelector('input[name="last-name"]');
			const phoneField = cartPage.querySelector('input[name="phone"]');
			const emailField = cartPage.querySelector('input[name="email"]');

			// Find save data button.
			const saveDataButton = cartPage.querySelector('input[name="save-customer"]');

			confirmPurchaseButton.addEventListener('click', () => {
				const requestData = {
					first_name: firstNameField.value,
					last_name: lastNameField.value,
					middle_name: middleNameField.value,
					phone: phoneField.value,
					email: emailField.value,
				}

				if (saveDataButton.checked) {
					axios.post(this.userPathStub + 'save-data', requestData, this.requestConfig).then((response) => {
						console.log('test');
					});
				}

				axios.post(this.orderPathStub + 'create-order', requestData, this.requestConfig).then((response) => {
					if (response.data.status === 200 && response.data.success) {
						window.location.href = window.location.origin + '/checkout';
					}
				});
			});
		}

	},
}

document.addEventListener('DOMContentLoaded', () => {
	// Initialize cart pop-up in the header.
	CartController.initDynamicCart();
	// Initialize cart page content.
	CartController.initCartPage();
});