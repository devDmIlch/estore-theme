
const DropdownController = {
	initDropdown(triggerEl, targetEl) {
		// Bail if the trigger/target element is a null.
		if (!targetEl || !triggerEl) {
			return;
		}

		triggerEl.addEventListener('click', () => {
			// Toggle active class for trigger.
			triggerEl.classList.toggle('active');
			// Toggle active class for target.
			targetEl.classList.toggle('active');
		});

		document.addEventListener('click', (e) => {
			// Check relationship of clicked element.
			if (e.target.isSameNode(triggerEl) || e.target.isSameNode(targetEl)) {
				return;
			}
			// Check if clicked inside of the trigger/target element.
			if (triggerEl.contains(e.target) || targetEl.contains(e.target)) {
				return;
			}
			// Remove active classes if clicked outside.
			triggerEl.classList.remove('active');
			targetEl.classList.remove('active');
		});
	}
}

export default DropdownController;
