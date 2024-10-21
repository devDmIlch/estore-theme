const { __ } = wp.i18n;

const GallerySelector = {
	windowProperties: {
		title: __( 'Виберіть Зображення', 'estore-theme' ),
		multiple: true,
		library: {
			type: 'image',
		}
	},

	initControls(selector, props = {}) {
		// Creates preview for a selected attachment.
		const createPreview = (attachment) => {
			// Add item to the visual list.
			if (selectedZone) {
				// Create a DOM element with the image.
				const imageWrapper = document.createElement('div');
				imageWrapper.classList.add('var-image-icon');

				// Create DOM element of the image itself.
				const imageDOMEl = document.createElement('img');
				imageDOMEl.setAttribute('src', attachment.sizes.thumbnail.url);

				// Create removal icon.
				const imageRemoveEl = document.createElement('div');
				imageRemoveEl.classList.add('var-image-remove');
				// Add removal functionality to the button.
				imageRemoveEl.addEventListener('click', () => {
					// Remove wrapper element.
					imageWrapper.remove();
					// Remove element from the list of selected elements.
					selectedIDs = selectedIDs.filter(el => el !== attachment.id);
					// Assign new value to the input field.
					inputField.value = selectedIDs.join(',');
				});

				// Create control icons.
				const imageMoveUp = document.createElement('div');
				imageMoveUp.classList.add('var-image-move-up');
				// Add functionality for first control button.
				imageMoveUp.addEventListener('click', () => {
					// Check whether the element is the first in the list.
					if (imageWrapper.previousElementSibling === null) {
						return;
					}
					// Get index of the element in the array.
					const itemIndex = [...selectedZone.children].indexOf(imageWrapper);
					// Swap this element with a sibling.
					imageWrapper.previousElementSibling.before(imageWrapper);
					// Swap selected ids values.
					[selectedIDs[itemIndex], selectedIDs[itemIndex - 1]] = [selectedIDs[itemIndex - 1], selectedIDs[itemIndex]];
					// Assign new value to the input field.
					inputField.value = selectedIDs.join(',');
				});
				// Add second control button.
				const imageMoveDown = document.createElement('div');
				imageMoveDown.classList.add('var-image-move-down');
				// Add functionality for second control button.
				imageMoveDown.addEventListener('click', () => {
					// Check whether the element is the first in the list.
					if (imageWrapper.nextElementSibling === null) {
						return;
					}
					// Get index of the element in the array.
					const itemIndex = [...selectedZone.children].indexOf(imageWrapper);
					// Swap this element with a sibling.
					imageWrapper.nextElementSibling.after(imageWrapper);
					// Swap selected ids values.
					[selectedIDs[itemIndex], selectedIDs[itemIndex + 1]] = [selectedIDs[itemIndex + 1], selectedIDs[itemIndex]];
					// Assign new value to the input field.
					inputField.value = selectedIDs.join(',');
				});


				// Insert image in the wrapper element.
				imageWrapper.insertAdjacentElement('beforeend', imageDOMEl);
				// Insert 'remove' icon.
				imageWrapper.insertAdjacentElement('beforeend', imageRemoveEl);
				// Insert 'remove' icon.
				imageWrapper.insertAdjacentElement('beforeend', imageMoveUp);
				// Insert 'remove' icon.
				imageWrapper.insertAdjacentElement('beforeend', imageMoveDown);

				// Remove existing elements if only single image is allowed.
				if (props.multiple === false) {
					selectedZone.innerHTML = '';
				}

				// Insert whole element into element for selected items.
				selectedZone.insertAdjacentElement('beforeend', imageWrapper);
			}
		}

		const trigger = selector.querySelector('.gallery-selector-trigger')
		// Get relation name to set values of the input.
		const relationName = trigger.getAttribute('relation');
		// Find the input on the page.
		const inputField = selector.querySelector('input[name="' + relationName + '[]"], input[name="' + relationName + '"]');
		// Abort if the input field was not found.
		if (inputField === null) {
			return;
		}

		// Create an array of values in the input.
		let selectedIDs = inputField.value.split(',').filter(Number).map(Number);
		// Search for the area with selected items.
		const selectedZone = selector.querySelector('.media-selected[relation="' + relationName + '"]');
		// Create media selection window.
		const galleryFrame = wp.media(Object.assign(props ?? this.windowProperties, {title: trigger.getAttribute('window-name') ?? this.windowProperties.title}));

		// Add previews for selected attachment.
		if (selectedIDs.length > 0) {
			// Create an array for already selected attachments.
			const previewAttachments = [];
			// Go though each id to fetch each attachment individually.
			selectedIDs.forEach((id) => {
				// Fetch attachment data.
				wp.media.attachment(id).fetch().then((data) => {
					// Push data into array with the attachment data. This is needed to sort attachments and show them in the right order.
					previewAttachments.push(data);
					// Check whether all of the attachments were loaded.
					if (previewAttachments.length === selectedIDs.length) {
						// Sort attachments.
						previewAttachments.sort((a, b) => selectedIDs.indexOf(a.id) - selectedIDs.indexOf(b.id));
						// Go through sorted attachments to create previews.
						previewAttachments.forEach(createPreview);
					}
				});
			})
		}

		// Set up triggering window on clicking the link.
		trigger.addEventListener('click', (e) => {
			// Prevent link navigation just in case.
			e.preventDefault();
			// Trigger window opening.
			galleryFrame.open();
		});

		galleryFrame.on('select', (e) => {
			// Get attachment ids from the selected items.
			galleryFrame.state().get('selection').each((attachment) => {
				// Remove existing values if only single image is allowed.
				if (props.multiple === false) {
					selectedIDs = [];
				}
				// Check if the item is already selected.
				if (selectedIDs.includes(attachment.attributes.id)) {
					return;
				}
				// Push id into an array.
				selectedIDs.push(attachment.attributes.id);
				// Create little preview image of all selected images.
				createPreview(attachment.attributes);
			});
			// Assign new value to the input field.
			inputField.value = selectedIDs.join(',');
		});
	},
}

export default GallerySelector;
