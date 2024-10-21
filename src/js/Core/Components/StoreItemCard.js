
const StoreItemControl = {

	initCardVariations(storeItemCard) {
		// Find all price divs.
		const priceDivs = storeItemCard.querySelectorAll('.var-price');
		// Save last displayed price.
		let displayedPrice = priceDivs[0];
		// Display default price.
		displayedPrice.classList.add('active');

		// Find variations controls.
		storeItemCard.querySelectorAll('.single-var').forEach(singleVarControl => {
			// Get variation id.
			const varID = singleVarControl.getAttribute('var');
			// Get correspondent price div.
			const priceDiv = [ ...priceDivs ].filter((el) => el.getAttribute('var') === varID)[0];
			// Display related price while hovering over variation.
			singleVarControl.addEventListener('mouseenter', () => {
				// Bail if hovering over the first price.
				if (displayedPrice.isSameNode(priceDiv)) {
					return;
				}

				// Remove 'active' class from currently displayed variation.
				displayedPrice.classList.remove('active');
				// Add 'active' class to the price div correspondent to this variation.
				priceDiv.classList.add('active');
				// Save currently displayed price div.
				displayedPrice = priceDiv;
			});

			singleVarControl.addEventListener('mouseleave', () => {
				// Remove 'active' class from currently displayed variation.
				displayedPrice.classList.remove('active');
				// Set current price to the first variation.
				displayedPrice = priceDivs[0];
				// Add 'active' class to the price div correspondent to this variation.
				displayedPrice.classList.add('active');
			});
		});
	},

	initStoreItemCard(storeItemCard) {
		// Initialize variation prices updates.
		this.initCardVariations(storeItemCard);
	},
}

export default StoreItemControl;
