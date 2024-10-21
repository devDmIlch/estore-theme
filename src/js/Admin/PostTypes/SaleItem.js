/* global wpApiSettings */

import MultiSelector from "../GallerySelector/MultiSelector.js";
import axios from "axios";

document.addEventListener('DOMContentLoaded', () => {
	// Prepare request config for endpoint calls.
	const requestConfig = {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		}
	};
	// Prepare namespace for endpoints related to the 'store-item' type posts.
	const storeItemRestNamespaceStub = '/wp-json/estore/store-item/';

	// Find the settings section.
	const settingsSection = document.querySelector('#sale-item-settings');

	if (settingsSection) {
		const checkboxInputs = settingsSection.querySelectorAll('input[type="checkbox"]');

		checkboxInputs.forEach((input) => {
			input.addEventListener('click', (e) => {
				// Toggle active class.
				input.classList.toggle('active');
			});
		});
	}

	// Find the relations sections.
	const relatedSection = document.querySelector('#sale-item-related');

	if (relatedSection) {
		// Find area with all selected items.
		const selectedArea = relatedSection.querySelector('.selected-values');
		// Create/Remove item control on option selection.
		const initPostControls = (singlePostDOMEl, selector) => {
			// Find id of the post.
			const postId = singlePostDOMEl.getAttribute('value');
			// Find position controls.
			const controlPrev = singlePostDOMEl.querySelector('.controls .prev');
			const controlNext = singlePostDOMEl.querySelector('.controls .next');

			if (controlPrev) {
				// Create an event to move item up.
				const changeEvent = new CustomEvent('moveValue', {detail: {value: postId, next: false}});

				// Dispatch said event to the selector on click.
				controlPrev.addEventListener('click', () => {
					selector.dispatchEvent(changeEvent);

					// Move the post element itself.
					if (singlePostDOMEl.previousElementSibling) {
						singlePostDOMEl.previousElementSibling.before(singlePostDOMEl);
					}
				});
			}

			if (controlNext) {
				// Create an event to move item down.
				const changeEvent = new CustomEvent('moveValue', {detail: {value: postId, next: true}});

				// Dispatch said event to the selector on click.
				controlNext.addEventListener('click', () => {
					selector.dispatchEvent(changeEvent);

					// Move the post element itself.
					if (singlePostDOMEl.nextElementSibling) {
						singlePostDOMEl.nextElementSibling.after(singlePostDOMEl);
					}
				});
			}
		};

		// Find selectors.
		const multiSelectors = relatedSection.querySelectorAll('.multi-selector');
		// Initialize selectors.
		multiSelectors.forEach((selector) => {
			// Initialize multi-selector.
			MultiSelector.initSelector(selector);
			// Add onChange event to the multi-selector.
			selector.addEventListener('change', (e) => {
				if (e.detail.isSelected) {
					axios.post(storeItemRestNamespaceStub + 'get-post-ctrl', {'post_id': e.detail.value}, requestConfig).then((response) => {
						if (response.data.status === 200 && response.data.success) {
							// Insert the card with controls.
							selectedArea.insertAdjacentHTML('beforeend', response.data.html);
							// Initialize card controls.
							initPostControls(selectedArea.lastElementChild, selector);
						}
					});
				} else {
					// Find related card in the selected area.
					const relatedCard = selectedArea.querySelector('.single-control[value="' + e.detail.value + '"]');
					// Remove said card if it exists.
					if (relatedCard) {
						relatedCard.remove();
					}
				}
			});

			// Initialize controls for already selected items.
			relatedSection.querySelectorAll('.single-control').forEach((singlePostControl) => {
				initPostControls(singlePostControl, selector);
			});
		});
	}
});
