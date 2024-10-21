/* global wpApiSettings */

import axios from "axios";

const OrderCardControl = {

	initOrderCard(storeItemCard) {
		// Order endpoints path.
		const pathStub = '/wp-json/estore/order/';
		// Request config.
		const requestConfig = {
			headers: {
				'X-WP-Nonce': wpApiSettings.nonce,
			},
		}

		// Find order status to change.
		const orderStatusText = storeItemCard.querySelector('.status-text');
		// Find order cancellation button.
		const cancelOrderButton = storeItemCard.querySelector('.cancel-order');

		// Create a flag to prevent double cancellation requests.
		let isActionInProgress = false;

		// Get the order id.
		const orderID = storeItemCard.getAttribute('order-id');

		// Initialize order cancellation button if it actually exists.
		if (cancelOrderButton) {
			cancelOrderButton.addEventListener('click', () => {
				// Check if action in progress flag is active.
				if (isActionInProgress) {
					return;
				}

				// Make action flag active.
				isActionInProgress = true;

				// Prepare data for request.
				const requestData = {
					order_id: orderID,
				}

				// Make request to cancel the order.
				axios.post(pathStub + 'request-cancellation', requestData, requestConfig).then(response => {
					// Update message.
					if (response.data.status === 200 && response.data.success) {
						// Remove cancel order button.
						cancelOrderButton.remove();
						// Update status message if the element for it exists.
						if (orderStatusText && response.data.order_status) {
							orderStatusText.innerHTML = response.data.order_status;
						}
						// Deactivate the flag.
						isActionInProgress = false;
					} else {
						// Update message of the order button.
						if (response.data.message) {
							cancelOrderButton.innerHTML = response.data.message;
							// Add shaking animation to inform user.
							cancelOrderButton.classList.add('shake');
						}
					}
				});
			});
		}
	},
}

export default OrderCardControl;
