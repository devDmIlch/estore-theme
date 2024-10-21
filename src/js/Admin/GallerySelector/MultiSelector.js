
const MultiSelector = {
	initSelectedItemControls(selectedItemDOMEl) {

	},

	initSelector(selectorDOMEl, onChange = null) {
		// Find the input field for the selector.
		const selectedInput = selectorDOMEl.querySelector('.input-selected');
		// Create an array that will hold selected values.
		const selectedArr = selectedInput.value !== '' ? selectedInput.value.split(',') : [];

		// Find the selectable options in the dropdown.
		const multiSelectOptions = selectorDOMEl.querySelectorAll('.option');
		// Find the search bar.
		const searchInput = selectorDOMEl.querySelector('.input-search');

		// Initialize each option.
		multiSelectOptions.forEach((option) => {
			// Find value of the option.
			const optionValue = option.getAttribute('value');

			option.addEventListener('click', () => {
				const isSelected = option.classList.toggle('active');
				// Do additional check to determine whether the value was already added to the array.
				if (isSelected && selectedArr.indexOf(optionValue) < 0) {
					selectedArr.push(optionValue);
				}
				// Do additional check to determine whether the value was already removed from the array.
				if (!isSelected && selectedArr.indexOf(optionValue) !== -1) {
					selectedArr.splice(selectedArr.indexOf(optionValue), 1);
				}
				// Update the selected input.
				selectedInput.value = selectedArr.join(',');
				// Create new event for selector value updating.
				const changeEvent = new CustomEvent('change', {detail: {value: optionValue, isSelected: isSelected}});
				// Trigger 'onChange' event for the selector element.
				selectorDOMEl.dispatchEvent(changeEvent);
			});
		});

		// Initialize search input if it exists.
		if (searchInput) {
			// Searching within large number of options would be demanding, therefore use a delayed action.
			let delayAction = null;

			// Add event on 'keyup' since it's the most reliable one.
			searchInput.addEventListener('keyup', () => {
				// About previous search if it exists.
				if (delayAction) {
					clearTimeout(delayAction);
				}
				// Create new timeout.
				delayAction = setTimeout(() => {
					multiSelectOptions.forEach((option) => {
						if (option.innerHTML.trim().toLowerCase().includes(searchInput.value.trim().toLowerCase())) {
							option.classList.remove('inactive');
						} else {
							option.classList.add('inactive');
						}
					});
				}, 50);
			});
		}

		// Initialize listener for external value update.
		selectorDOMEl.addEventListener('moveValue', (e) => {
			if (typeof e.detail === 'undefined') {
				return;
			}

			// Find position of the value inside of an array.
			const valuePos = selectedArr.indexOf(e.detail.value);
			// Get index of the element to swap with.
			const swapPos = e.detail.next ? valuePos + 1 : valuePos - 1;

			// Update the order in array of selected values.
			[selectedArr[valuePos], selectedArr[swapPos]] = [selectedArr[swapPos], selectedArr[valuePos]];

			// Update input to reflect new order properly.
			selectedInput.value = selectedArr.join(',');
		});
	},
}

export default MultiSelector;
