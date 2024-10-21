/* global wpApiSettings */

import axios from "axios";

const CartItem = {

	initCartItem(cartDomEl) {
		const pathStub = '/wp-json/estore/cart/';

		const requestConfig = {
			headers: {
				'X-WP-Nonce': wpApiSettings.nonce,
			},
		};

		// Get variation id from the HTML element.
		const varID = cartDomEl.getAttribute('var-id');

		// Find error message.
		const errorMessageEl = cartDomEl.querySelector('.item-error');

		// Find the bottom-left counter.
		const itemCounter = cartDomEl.querySelector('.number-in-cart');
		// Find Increase/Decrease buttons.
		const decreaseNumButton = cartDomEl.querySelector('.number-decrease');
		const increaseNumButton = cartDomEl.querySelector('.number-increase');
		// Find 'Remove item' button.
		const removeItemButton = cartDomEl.querySelector('.remove-item');

		// Initialize buttons.
		if (increaseNumButton) {
			increaseNumButton.addEventListener('click', () => {
				axios.post(pathStub + 'add-single', {var_id: varID}, requestConfig).then((response) => {
					const updatedNumber = Number(itemCounter.innerHTML) + 1;
					// Check whether response was sent without any errors.
					if (response.data.status !== 200) {
						return;
					}

					if (!response.data.success) {
						// Add error message.
						if (errorMessageEl) {
							errorMessageEl.innerHTML = response.data.message;
						}
						// Add 'error' class to display error message.
						cartDomEl.classList.add('error');
						return;
					}

					// Remove error message (if it's displayed).
					cartDomEl.classList.remove('error');

					// Update the counter.
					itemCounter.innerHTML = updatedNumber;


					// Dispatch 'change' event.
					cartDomEl.dispatchEvent(new Event('change'));
				});
			});
		}

		if (decreaseNumButton) {
			decreaseNumButton.addEventListener('click', () => {
				axios.post(pathStub + 'remove-single', {var_id: varID}, requestConfig).then((response) => {
					const updatedNumber = Number(itemCounter.innerHTML) - 1;

					if (response.data.status !== 200) {
						return;
					}

					if (!response.data.success) {
						// Add error message.
						if (errorMessageEl) {
							errorMessageEl.innerHTML = response.data.message;
						}
						// Add 'error' class to display error message.
						cartDomEl.classList.add('error');
						return;
					}

					// Remove error message (if it's displayed).
					cartDomEl.classList.remove('error');

					// Update the counter.
					itemCounter.innerHTML = updatedNumber;

					// If the number of items is < 1, disable button to decrease items.
					if (updatedNumber < 2) {
						decreaseNumButton.classList.add('disabled');
					}

					// Dispatch 'change' event.
					cartDomEl.dispatchEvent(new Event('change'));
				});
			});
		}

		if (removeItemButton) {
			removeItemButton.addEventListener('click', () => {
				axios.post(pathStub + 'remove-item', {var_id: varID}, requestConfig).then((response) => {
					if (response.data.status === 200 && response.data.success) {
						cartDomEl.remove();
						// Dispatch 'change' event.
						cartDomEl.dispatchEvent(new Event('change'));
					}
				});
			});
		}
	},
}

export default CartItem;
