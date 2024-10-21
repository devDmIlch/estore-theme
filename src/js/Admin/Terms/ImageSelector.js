
// Internal dependencies.
import GalleryFileSelector from "../GallerySelector/GalleryFileSelector";

document.addEventListener('DOMContentLoaded', () => {
	// Search page for the term selector area.
	const termSelectorNode = document.querySelector('.term-thumbnail-selector');
	// Initialize selector if the node exists.
	if (termSelectorNode) {
		GalleryFileSelector.initControls(termSelectorNode, {multiple: false});
	}
});