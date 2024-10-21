/* global wpApiSettings */

// External dependencies.
import axios from "axios";

// WordPress functions.
const { __ } = wp.i18n;

document.addEventListener('DOMContentLoaded', () => {
	const userPathStub = '/wp-json/estore/user/';

	const requestConfig = {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		},
	};

	// Find success and error message boxes.
	const successMessageBox = document.querySelector('.success-message');
	const errorMessageBox = document.querySelector('.error-message');

	// Try looking for user data update form.
	const userDataUpdateForm = document.querySelector('form.user-data');
	// Check if the form exists on the page.
	if (userDataUpdateForm) {
		// Find input fields.
		const middleNameInput = userDataUpdateForm.querySelector('#middle-name');
		const firstNameInput = userDataUpdateForm.querySelector('#first-name');
		const lastNameInput = userDataUpdateForm.querySelector('#last-name');
		const phoneInput = userDataUpdateForm.querySelector('#phone');

		// Find submit button.
		const submitButton = document.querySelector('.save-button.save-user');

		// Initialize submit button.
		submitButton.addEventListener('click', () => {
			const requestData = {
				middle_name: middleNameInput.value,
				first_name: firstNameInput.value,
				last_name: lastNameInput.value,
				phone: phoneInput.value,
			}

			// Hide message boxes.
			if (successMessageBox) {
				successMessageBox.classList.add('disabled');
			}
			if (errorMessageBox) {
				errorMessageBox.classList.add('disabled');
			}

			axios.post(userPathStub + 'save-data', requestData, requestConfig).then((response) => {
				if (response.data.status === 200) {
					if (successMessageBox) {
						successMessageBox.classList.remove('disabled');
					}
				}
			}).catch((error) => {
				if (errorMessageBox) {
					errorMessageBox.classList.remove('disabled');
				}
			});
		});
	}

	// Try looking for user email update form.
	const userEmailUpdateForm = document.querySelector('form.user-email');
	// Check if the form exists on the page.
	if (userEmailUpdateForm) {
		// Find input fields.
		const emailInput = userEmailUpdateForm.querySelector('#email');
		const passwordInput = userEmailUpdateForm.querySelector('#password');

		// Find submit button.
		const submitButton = document.querySelector('.save-button.save-email');

		// Initialize submit button.
		submitButton.addEventListener('click', () => {
			const requestData = {
				email: emailInput.value,
				password: passwordInput.value,
			}

			// Hide message boxes.
			if (successMessageBox) {
				successMessageBox.classList.add('disabled');
			}
			if (errorMessageBox) {
				errorMessageBox.classList.add('disabled');
				// Set default message for error message box.
				errorMessageBox.innerHTML = __( 'Не вдалось оновити електронну пошту.', 'estore-theme' );
			}

			axios.post(userPathStub + 'update-email', requestData, requestConfig).then((response) => {
				if (response.data.status === 200 && response.data.success) {
					if (successMessageBox) {
						successMessageBox.classList.remove('disabled');
					}
				} else {
					if (errorMessageBox) {
						// Check if received custom message.
						if (response.data.message) {
							errorMessageBox.innerHTML = response.data.message;
						}
						// Show error message.
						errorMessageBox.classList.remove('disabled');
					}
				}
			}).catch((error) => {
				if (errorMessageBox) {
					errorMessageBox.classList.remove('disabled');
				}
			});
		});
	}
});
