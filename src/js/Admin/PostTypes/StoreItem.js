/* global wpApiSettings */

// External imports.
import axios from "axios";

// Internal imports.
import GallerySelector from '../GallerySelector/GalleryFileSelector';

// WordPress functions.
const { __ } = wp.i18n;

const StoreItemController = {
	// Set url path stub for the request
	path: '/wp-json/estore/store-item/',
	// Special flag that is utilized to not stack the actions preventing unexpected behaviour.
	suppressActions: false,

	// Initialize controls for a single variation.
	initSingleVarControls(variationDOM) {
		// Initialize gallery control.
		GallerySelector.initControls(variationDOM.querySelector('.estore-gallery-selector'));
		// Find the item removal button.
		const removeVar = variationDOM.querySelector('.var-del');
		// Initialize removal of the variation upon clicking the deletion button.
		removeVar.addEventListener('click', () => {
			variationDOM.remove();
		});
		// Find the controls buttons.
		const moveUpVar = variationDOM.querySelector('.var-move-up');
		const moveDownVar = variationDOM.querySelector('.var-move-down');
		// Initialize controls buttons.
		moveUpVar.addEventListener('click', () => {
			// Check if previous element sibling even exist.
			if (variationDOM.previousElementSibling === null) {
				return;
			}
			// Swap places with previous sibling.
			variationDOM.previousElementSibling.before(variationDOM);
		});
		moveDownVar.addEventListener('click', () => {
			// Check if previous element sibling even exist and if the next sibling is a variation.
			if (variationDOM.nextElementSibling === null || !variationDOM.nextElementSibling.classList.contains('variation-item')) {
				return;
			}
			// Swap places with previous sibling.
			variationDOM.nextElementSibling.after(variationDOM);
		});
	},

	// Initializes controls in the variation section of the editor.
	initVarSection(sectionDOM) {
		// Find all variations in the section.
		const variations = sectionDOM.querySelectorAll('.variation-item');
		// Find the 'Add New' button in the section.
		const addNewButton = sectionDOM.querySelector('.var-add');

		// Initialize variation controls.
		variations.forEach((variation) => {
			this.initSingleVarControls(variation);
		});

		// Skip the following code related to the addition of the new variations if the button is missing.
		if (addNewButton === null) {
			return;
		}

		addNewButton.addEventListener('click', () => {
			// Check if the previous action is complete.
			if (this.suppressActions) {
				return;
			}
			// Set flag to true, to prevent action stacking.
			this.suppressActions = true;
			// Request a template for a variation from the server.
			axios.post(this.path + 'get-var-ctrl', {}, {
				headers: {
					'X-WP-Nonce': wpApiSettings.nonce,
				}
			}).then((response) => {
				// Insert new variation control.
				addNewButton.insertAdjacentHTML('beforebegin', response.data.html);
				// Initialize variation control.
				this.initSingleVarControls(addNewButton.previousElementSibling);
				// Return the flag to it's original state.
				this.suppressActions = false;
			});
		});
	},
};

document.addEventListener('DOMContentLoaded', () => {
	const storeItemVariationSection = document.querySelector('.variations-section');
	if (storeItemVariationSection !== null) {
		// Initialize the variation section for the editor.
		StoreItemController.initVarSection(storeItemVariationSection);
	}
});
