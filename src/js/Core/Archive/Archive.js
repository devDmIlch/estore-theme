/* global wpApiSettings, archivePrefs */

// External Modules.
import axios from "axios";

// Internal Modules.
import StoreItemControl from "../Components/StoreItemCard";
import Dropdown from "../Ext/Dropdown";
import OrderCardControl from "../Components/OrderCard";

class ArchiveController {

	// Private Fields.

	/**
	 * Endpoint path
	 **/
	static #path = '/wp-json/estore/archive/posts';

	/**
	 * Whether initial request has to be called.
	 **/
	#isInitialRequest = true;

	/**
	 * Archive DOM element.
	 **/
	#archiveDOM;

	/**
	 * Archive Content Section.
	 **/
	#archiveContent;

	/**
	 * Archive Filters Section.
	 **/
	#archiveFilters;

	/**
	 * Archive Pagination Section.
	 **/
	#archivePagination;

	/**
	 * Archive Sort Options Section.
	 **/
	#archiveSort;

	/**
	 * Archive Selected Filters Section.
	 * */
	#archiveSelected;

	/**
	 * Archive Preferences object.
	 **/
	#prefs;

	/**
	 * Request headers.
	 **/
	#requestConfig = {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		},
	};

	/**
	 * Special flag to prevent execution of spammy requests.
	 **/
	#spamDelay = false;

	/**
	 * Expiration time for the spam delay.
	 **/
	#spamDelayTimout = 50;

	/**
	 * Fetch request fields.
	 **/
	#fetchRequest = null;


	// Constructor.

	constructor(archiveDOM) {
		// Save archive parent element.
		this.#archiveDOM = archiveDOM;
		// Set initial preferences.
		this.#prefs = this.GetDefaultArchivePrefs();

		// Find sections of the archive to fill in.
		this.#archiveContent = this.#archiveDOM.querySelector('.archive-content');
		this.#archiveFilters = this.#archiveDOM.querySelector('.archive-filters');
		this.#archivePagination = this.#archiveDOM.querySelector('.archive-pagination');
		this.#archiveSelected = this.#archiveDOM.querySelector('.archive-selected');
		this.#archiveSort = this.#archiveDOM.querySelector('.archive-sort');

		// Fetch initial data.
		this.FetchPosts({loadPagination: true});
	}


	// Private Methods.

	/**
	 * Returns default archive preferences based on global 'archivePrefs' variable.
	 **/
	GetDefaultArchivePrefs = () => {
		let prefs;

		switch (archivePrefs.type) {
			case 'store-item':
				prefs = {
					post_type: ['store-item'],
					page: 1,
					number: 12,
					params: {
						'brand': {
							type: 'tax',
						},
						'item-category': {
							type: 'tax',
						},
						'price': {
							type: 'price',
						}
					},
					sort_options: [ 'new', 'name', 'price_asc', 'price_desc' ],
					template: 'store-item',
					active_filters: true,
				};

				// Taxonomy details are sent if querying taxonomy archive.
				if (archivePrefs.tax) {
					prefs.necessary = archivePrefs.tax;
				}
				break;
			case 'search':
				prefs = {
					post_type: ['store-item'],
					page: 1,
					number: 12,
					params: {
						'brand': {
							type: 'tax',
						},
						'item-category': {
							type: 'tax',
						},
						'price': {
							type: 'price',
						}
					},
					sort_options: [ 'new', 'name', 'price_asc', 'price_desc' ],
					template: 'store-item',
					active_filters: true,
				};

				// Query search archive.
				if (archivePrefs.search) {
					prefs.search = archivePrefs.search;
				}
				break;
			case 'sale':
				prefs = {
					post_type: ['store-item'],
					page: 1,
					number: 1000,
					params: {},
					template: 'store-item',
					sort_selected: 'post__in',
				};

				// Add limitation on queryable posts.
				if (archivePrefs.post__in) {
					prefs.post__in = archivePrefs.post__in;
				}
				break;
			case 'orders':
				prefs = {
					post_type: ['user-order'],
					page: 1,
					number: 5,
					params: {},
					template: 'order',
					sort_selected: 'post__in',
				};

				// Add limitation on queryable posts.
				if (archivePrefs.post__in) {
					prefs.post__in = archivePrefs.post__in;
				}
		}

		return prefs;
	}

	/**
	 * Fetches posts via REST API.
	 **/
	FetchPosts = (params = {}) => {
		// Check whether the spam.
		if (this.#spamDelay) {
			return;
		}

		// Set spam delay to true.
		this.#spamDelay = true;
		// Reset the flag after the delay expires.
		setTimeout(() => this.#spamDelay = false, this.#spamDelayTimout);

		// Check whether there is a request in a process of execution.
		if (this.#fetchRequest) {

		}

		// Load filters only on initial request.
		this.#prefs.filters = this.#isInitialRequest;
		this.#prefs.sorter = this.#isInitialRequest;

		// Load pagination if specified in the arguments.
		this.#prefs.pagination = params.loadPagination === true;
		// Reset page if specified in the arguments.
		if (params.resetPage) {
			this.#prefs.page = 1;
		}

		// Fetch new request.
		this.#fetchRequest = axios.post(ArchiveController.#path, this.#prefs, this.#requestConfig).then((response) => {
			// Check if the previous element is loader to remove it after first page load.
			if (this.#archiveDOM.previousElementSibling !== null && this.#archiveDOM.previousElementSibling.classList.contains('dynamic-archive-loader')) {
				this.#archiveDOM.previousElementSibling.classList.add('hide');
				// Remove element from the page after animation has stopped playing.
				setTimeout(() => {
					this.#archiveDOM.previousElementSibling.remove();
				}, 100);


			}
			// Remove 'initial-load' class to reveal content.
			this.#archiveDOM.classList.remove('initial-load');

			// If initial search request fails override all of the content.
			if (this.#isInitialRequest && this.#prefs.search && response.data.empty) {
				this.#archiveDOM.innerHTML = '';
				this.#archiveDOM.insertAdjacentHTML('beforeend', response.data.content);
				// Bail from the function.
				return;
			}

			// Load posts retrieved from endpoint.
			this.InsertPosts(response);
		});
	}

	/**
	 * Inserts posts into archive based on the request response.
	 **/
	InsertPosts = (response) => {
		// Bail if the response isn't a-ok.
		if (response.data.status !== 200) {
			return;
		}
		// Insert HTML into the content section.
		if (this.#archiveContent) {
			// Remove existing content in the section.
			this.#archiveContent.innerHTML = '';
			// Insert new content.
			this.#archiveContent.insertAdjacentHTML('afterbegin', response.data.content);
			// Initialize actions related to the post cards.
			this.InitPostsActions();

			// Replace filters if they were rendered.
			if (this.#archiveFilters && response.data.filters) {
				this.#archiveFilters.innerHTML = '';
				this.#archiveFilters.insertAdjacentHTML('afterbegin', response.data.filters);
				// Initialize actions related to the filters.
				this.InitFiltersActions();
			}

			// Replace pagination if they were rendered.
			if (this.#archivePagination && response.data.pagination) {
				this.#archivePagination.innerHTML = '';
				// Insert only if the content exists.
				if (response.data.empty !== true) {
					this.#archivePagination.insertAdjacentHTML('afterbegin', response.data.pagination);
					// Initialize actions related to the pagination.
					this.InitPaginationActions();
				}
			}

			// Insert selected items into the section.
			if (this.#archiveSelected && response.data.active_filters !== null) {
				this.#archiveSelected.innerHTML = '';
				this.#archiveSelected.insertAdjacentHTML('beforeend', response.data.active_filters);
				// Initialize selected control actions.
				this.InitSelectedActions();
			}

			// Insert sorter into the section.
			if (this.#archiveSort && response.data.sorter) {
				this.#archiveSort.innerHTML = '';
				this.#archiveSort.insertAdjacentHTML('beforeend', response.data.sorter);
				// Initialize sorter actions.
				this.InitSorterActions();
			}

			// Set initial request flag to false.
			this.#isInitialRequest = false;
		}
	}

	InitPostsActions = () => {
		switch (archivePrefs.type) {
			case 'store-item':
			case 'search':
			case 'sale':
				this.#archiveContent.querySelectorAll('.card-store-item').forEach(cardDOM => {
					StoreItemControl.initStoreItemCard(cardDOM);
				});
				break;
			case 'orders':
				this.#archiveContent.querySelectorAll('.card-order').forEach(cardDOM => {
					OrderCardControl.initOrderCard(cardDOM);
				});
				break;
		}
	}

	InitFiltersActions = () => {
		// Check whether the selected object is prepared.
		if (typeof this.#prefs.selected === 'undefined') {
			this.#prefs.selected = {};
		}

		this.#archiveFilters.querySelectorAll('.filter-option').forEach(filterOption => {
			// Get option value.
			const optionValue = Number(filterOption.getAttribute('option'));
			// Get option filter name.
			const optionFilter = filterOption.closest('.filter-section').getAttribute('filter');

			// Check whether there is an array for this filter to avoid potential errors.
			if (typeof this.#prefs.selected[optionFilter] === 'undefined') {
				this.#prefs.selected[optionFilter] = [];
			}

			// Initialize action for buttons.
			filterOption.addEventListener('click', () => {
				// Base update on element state to avoid overcomplicating logic.
				const isActive = filterOption.classList.toggle('active');
				// Toggle active option in the preferences array.
				if (isActive) {
					// Bail if there is a mishap, or someone screwing around with HTML.
					if (this.#prefs.selected[optionFilter].indexOf(optionValue) > -1) {
						return;
					}
					// Add element to an array of selected values.
					this.#prefs.selected[optionFilter].push(optionValue);
				} else {
					// Bail if there is a mishap, or someone screwing around with HTML.
					if (this.#prefs.selected[optionFilter].indexOf(optionValue) < 0) {
						return;
					}
					// Remove item from the array of selected values.
					this.#prefs.selected[optionFilter].splice(this.#prefs.selected[optionFilter].indexOf(optionValue), 1);
				}
				// Fetch posts with updated parameters.
				this.FetchPosts({loadPagination: true, resetPage: true});
			});
		});
	}

	InitPaginationActions = () => {
		// Save these buttons, they are going to be referenced on clicking their neighbours.
		const pageJumpButtons = this.#archivePagination.querySelectorAll('.pag-page');
		const pageNavButtons = this.#archivePagination.querySelectorAll('.pag-nav');
		// Also save dummies for showing page skips.
		const pageDummyButtons = this.#archivePagination.querySelectorAll('.dummy');

		// Initialize direct page switching.
		pageJumpButtons.forEach(navItem => {
			const navTarget = Number(navItem.getAttribute('value'));
			// Add action on click.
			navItem.addEventListener('click', () => {
				// Remove 'active' class from the old page button.
				pageJumpButtons[this.#prefs.page - 1].classList.remove('active');

				// Remove classes from the nearby elements.
				pageJumpButtons[this.#prefs.page - 1].previousElementSibling.classList.remove('is-active-neighbour');
				pageJumpButtons[this.#prefs.page - 1].nextElementSibling.classList.remove('is-active-neighbour');
				// Remove 'shown' class from button dummies.
				pageDummyButtons.forEach(dummyButton => {
					dummyButton.classList.remove('shown');
				});

				// Update page in fetch parameters.
				this.#prefs.page = navTarget;
				// Add 'active' class to the new button.
				navItem.classList.add('active');

				// Add classes to the nearby elements.
				navItem.previousElementSibling.classList.add('is-active-neighbour');
				navItem.nextElementSibling.classList.add('is-active-neighbour');

				if (navTarget - 2 > 1) {
					pageDummyButtons[0].classList.add('shown');
				}
				if (navTarget + 2 < pageJumpButtons.length) {
					pageDummyButtons[1].classList.add('shown');
				}

				// Fetch updated content.
				this.FetchPosts();
			});
		});

		// Initialize navigation between pages.
		pageNavButtons.forEach(navItem => {
			const navType = navItem.getAttribute('value');
			// Add action on click.
			navItem.addEventListener('click', () => {
				switch (navType) {
					case 'first':
						// Check whether we aren't already on the first page.
						if (this.#prefs.page === 1) {
							return;
						}
						// Just simulate clicking the first page button.
						pageJumpButtons[0].click();
						break;
					case 'prev':
						// Check whether there is a room to maneuver.
						if (this.#prefs.page === 1) {
							return;
						}
						// Simulate clicking the previous page button, pagination starts with 1, therefore we deduct 2.
						pageJumpButtons[this.#prefs.page - 2].click();
						break;
					case 'next':
						// Check whether there is a room to maneuver.
						if (this.#prefs.page === pageJumpButtons.length) {
							return;
						}
						// Simulate clicking the previous page button, pagination starts with 1, therefore we do not add anything.
						pageJumpButtons[this.#prefs.page].click();
						break;
					case 'last':
						// Check whether we aren't already on the last page.
						if (this.#prefs.page === pageJumpButtons.length) {
							return;
						}
						// Just simulate clicking the last page button.
						pageJumpButtons[pageJumpButtons.length - 1].click();
						break;
				}
			});
		});
	}

	InitSelectedActions = () => {
		this.#archiveSelected.querySelectorAll('.active-filter').forEach(activeFilterControl => {
			// Find name of the filter.
			const filterName = activeFilterControl.getAttribute('filter');
			// Find all of the active options within this filter.
			const filterOptions = activeFilterControl.querySelectorAll('.active-option');
			// Save number of option to later remove the whole filter controls is all options were removed.
			let optionNum = filterOptions.length;

			// Initialize action for each filter option.
			filterOptions.forEach(activeOptionControl => {
				// Find value of the option.
				const filterValue = Number(activeOptionControl.getAttribute('option'));
				// Add action to the 'close' button.
				activeOptionControl.querySelector('.remove-option').addEventListener('click', () => {
					// Remove the control.
					activeOptionControl.remove();
					// Deduct from the number of active filters.
					--optionNum;
					// Bail if there is a mishap, or someone screwing around with HTML.
					if (typeof this.#prefs.selected === 'undefined' || this.#prefs.selected[filterName].indexOf(filterValue) < 0) {
						return;
					}
					// Remove item from the array of selected values.
					this.#prefs.selected[filterName].splice(this.#prefs.selected[filterName].indexOf(filterValue), 1);
					// Remove active class from the related filter.
					if (this.#archiveFilters) {
						// Find filter section with option related to this selected filter.
						const filterSection = this.#archiveFilters.querySelector('.filter-section[filter="' + filterName + '"]');
						if (filterSection) {
							const filterOption = filterSection.querySelector('.filter-option[option="' + filterValue + '"]');
							if (filterOption) {
								filterOption.classList.remove('active');
							}
						}
					}
					// If removed the last active option remove the whole active filter.
					if (optionNum < 1) {
						activeFilterControl.remove();
					}
					// Fetch posts with updated parameters.
					this.FetchPosts({loadPagination: true, resetPage: true});
				});
			});
		})
	}

	InitSorterActions = () => {
		// Find a element that displays currently selected sorting option.
		const selectedOptionText = this.#archiveSort.querySelector('.sort-option-selected');
		// Save currently selected sorting option.
		let currentSort = this.#archiveSort.querySelector(this.#prefs.sort_selected ? '.sort-option[option="' +  this.#prefs.sort_selected  + '"]' : '.sort-option');

		// Initialize actions for every sort option.
		this.#archiveSort.querySelectorAll('.sort-option').forEach(sortOption => {
			const sortValue = sortOption.getAttribute('option');
			// Initialize clicking.
			sortOption.addEventListener('click', () => {
				// Check whether clicked an already selected sorting option.
				if (this.#prefs.sort_selected === sortValue) {
					return;
				}
				// Update value in the parameters.
				this.#prefs.sort_selected = sortValue;
				// Update text indicator of selected option.
				selectedOptionText.innerHTML = sortOption.innerHTML;
				// Remove active class from current sorting option.
				currentSort.classList.remove('active');
				// Add active class for newly selected sorting option.
				sortOption.classList.add('active');
				// Save newly selected sorting option as currently selected.
				currentSort = sortOption;
				// Reload Archive Content.
				this.FetchPosts({resetPage: true});
			});
		});

		// Initialize dropdown.
		Dropdown.initDropdown(this.#archiveSort.querySelector('.dropdown-trigger'), this.#archiveSort.querySelector('.dropdown-target'));
	}
}

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.estore-archive').forEach((el) => {
		new ArchiveController(el);
	});
});
