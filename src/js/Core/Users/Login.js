/* global wpApiSettings */

import axios from "axios";

const loginController = {
	submitButton: null,

	pathStub: '/wp-json/estore/login',

	requestConfig: {
		headers: {
			'X-WP-Nonce': wpApiSettings.nonce,
		},
	},

	isNullOrWhitespace( input ) {
		return !input || !input.trim();
	},

	initRegistrationCheck() {
		// Create counter for missing fields.
		let totalFieldsMissing = 0;

		// Find rules section.
		const passwordRules = document.querySelector('.password-rules');
		// Find the rules elements.
		const ruleValidLength = passwordRules.querySelector('.rule.valid-length');
		const ruleContainsLatin = passwordRules.querySelector('.rule.contains-latin');
		const ruleContainsNumber = passwordRules.querySelector('.rule.contains-number');
		const ruleContainsUppercase = passwordRules.querySelector('.rule.contains-uppercase');
		const ruleContainsLowercase = passwordRules.querySelector('.rule.contains-lowercase');
		// Find the password security rating elements.
		const passwordSecBar = passwordRules.querySelector('.password-strength-bar');

		// Save entered password to check whether 'repeat-password' input is valid.
		let currEnteredPass = '';

		document.querySelectorAll('.input-area').forEach(inputArea => {
			// Find input field.
			const inputField = inputArea.querySelector('input');
			// Find 'missing field' message element.
			const fieldMissingEl = inputArea.querySelector('.missing-field-notice');

			// Save the input field type for additional checks.
			const inputFieldType = inputField.getAttribute('name');

			// Crate a flag to track field validity change.
			let prevValueOk = !this.isNullOrWhitespace(inputField.value);
			// Add value to the number of the missing fields.
			if (prevValueOk) {
				++totalFieldsMissing;
			}

			inputField.addEventListener('keyup', () => {
				let valueOk = inputField.value.length > 0;

				// Check whether the email is valid.
				if (inputFieldType === 'email' && valueOk) {
					// Do a simple check for '@' symbol.
					valueOk = /.+[@].+[.].+/.test(inputField.value);
				}

				// Check whether password match.
				if (inputFieldType === 'password-repeat' && valueOk) {
					valueOk = currEnteredPass === inputField.value;
				}

				// Initialize 'missing field' message functionality.
				if (fieldMissingEl) {
					if (valueOk) {
						fieldMissingEl.classList.add('disabled');
					} else {
						fieldMissingEl.classList.remove('disabled');
					}
				}

				if (inputFieldType === 'password') {
					// Display password rules section.
					passwordRules.classList.remove('disabled');

					let validPassword = true;

					// Check the length of the password.
					if (inputField.value.length > 7 && inputField.value.length < 31) {
						ruleValidLength.classList.add('disabled');
					} else {
						ruleValidLength.classList.remove('disabled');
						// Make password invalid.
						validPassword = false;
					}

					// Check whether the password contains a number.
					if (/[0-9]/.test(inputField.value)) {
						ruleContainsNumber.classList.add('disabled');
					} else {
						ruleContainsNumber.classList.remove('disabled');
						// Make password invalid.
						validPassword = false;
					}

					// Check whether the password contains a lowercase letter.
					if (/[a-z]/.test(inputField.value)) {
						ruleContainsLowercase.classList.add('disabled');
					} else {
						ruleContainsLowercase.classList.remove('disabled');
						// Make password invalid.
						validPassword = false;
					}

					// Check whether the password contains an uppercase letter.
					if (/[A-Z]/.test(inputField.value)) {
						ruleContainsUppercase.classList.add('disabled');
					} else {
						ruleContainsUppercase.classList.remove('disabled');
						// Make password invalid.
						validPassword = false;
					}

					// Check whether the password contains only latin letter.
					if (inputField.value.length === 0 || /^[a-zA-Z0-9~_&*%@$]+$/.test(inputField.value)) {
						ruleContainsLatin.classList.add('disabled');
					} else {
						ruleContainsLatin.classList.remove('disabled');
						// Make password invalid.
						validPassword = false;
					}

					valueOk = validPassword;

					// Calculate password complexity level based on the number of symbols.
					const complexityLevel = validPassword ? Math.min(4, Math.max(1, Math.round((inputField.value.length - 6) / 4))) : 0;
					// Update password security bar.
					if (passwordSecBar) {
						passwordSecBar.setAttribute('level', String(complexityLevel));
					}

					currEnteredPass = inputField.value ?? '';
				}

				// Update the counter on the missing fields if the validity was changed.
				if (valueOk !== prevValueOk) {
					valueOk ? --totalFieldsMissing : ++totalFieldsMissing;
					// Update validity flag.
					prevValueOk = valueOk;
				}

				// Update classes for 'submit' button.
				if (totalFieldsMissing > 0) {
					this.submitButton.classList.add('inactive');
				} else {
					this.submitButton.classList.remove('inactive');
				}
			});
		});
	},

	initSimpleCheck() {
		// Create counter for missing fields.
		let totalFieldsMissing = 0;

		document.querySelectorAll('.input-area').forEach(inputArea => {
			// Find input field.
			const inputField = inputArea.querySelector('input');
			// Find 'missing field' message element.
			const fieldMissingEl = inputArea.querySelector('.missing-field-notice');

			// Save the input field type for additional checks.
			const inputFieldType = inputField.getAttribute('name');


			// Crate a flag to track field validity change.
			let prevValueOk = !this.isNullOrWhitespace(inputField.value);
			// Add value to the number of the missing fields.
			if (prevValueOk) {
				++totalFieldsMissing;
			}

			inputField.addEventListener('keyup', () => {
				let valueOk = inputField.value.length > 0;

				// Check whether the email is valid.
				if (inputFieldType === 'email' && valueOk) {
					// Do a simple check for '@' symbol.
					valueOk = /.+[@].+[.].+/.test(inputField.value);
				}

				// Initialize 'missing field' message functionality.
				if (fieldMissingEl) {
					if (valueOk) {
						fieldMissingEl.classList.add('disabled');
					} else {
						fieldMissingEl.classList.remove('disabled');
					}
				}

				// Update the counter on the missing fields if the validity was changed.
				if (valueOk !== prevValueOk) {
					valueOk ? --totalFieldsMissing : ++totalFieldsMissing;
					// Update validity flag.
					prevValueOk = valueOk;
				}

				// Update classes for 'submit' button.
				if (totalFieldsMissing > 0) {
					this.submitButton.classList.add('inactive');
				} else {
					this.submitButton.classList.remove('inactive');
				}
			});
		});
	},

	initSubmitButton(route, redirectOnSuccess = false) {
		// Find all of the input fields beforehand.
		const inputFields = document.querySelectorAll('.text-input');
		// Find notice fields before hand.
		const errorField = document.querySelector('.login-error-message');
		const successField = document.querySelector('.login-success-message');

		// Find redirect link supplied in the url.
		const redirectLink = (new URLSearchParams(window.location.search)).get('redirect_url') ?? window.location.origin;
		// Find login form.
		const loginForm = document.querySelector('.login-form');


		this.submitButton.addEventListener('click', () => {
			// Create an object to pass as request data.
			const loginData = {};
			// Create flag to make additional checks before submitting.
			let inputFieldsNotEmpty = true;

			// Hide error and success message boxes.
			errorField.classList.add('disabled');
			successField.classList.add('disabled');

			inputFields.forEach((field) => {
				loginData[field.getAttribute('name')] = field.value;

				if (this.isNullOrWhitespace(field.value)) {
					inputFieldsNotEmpty = false;
				}
			});

			if (!inputFieldsNotEmpty) {
				return;
			}

			/* Add captcha check */

			axios.post(this.pathStub + route, loginData, this.requestConfig).then(response => {
				if (response.data.status !== 200) {
					return;
				}

				// If returned soft error, display it to the user.
				if (!response.data.success) {
					// Update error message.
					errorField.innerHTML = response.data.error;
					// Show error message box.
					errorField.classList.remove('disabled');
				} else {
					if (redirectOnSuccess) {


						window.location.href = redirectLink;
					} else {
						// Update error message.
						successField.innerHTML = response.data.message;
						// Show error message box.
						successField.classList.remove('disabled');
						// Remove submit button and form.
						loginForm.remove();
						this.submitButton.remove();
					}
				}
			});
		});
	},

	initLogin() {
		// Find the submit button.
		this.submitButton = document.querySelector('.login-submit');
		// Initialize simple field input content checking.
		this.initSimpleCheck();
		// Initialize login button.
		this.initSubmitButton('/verify-user', true);
	},

	initRegister() {
		// Find the submit button.
		this.submitButton = document.querySelector('.register-submit');
		// Initialize password validity check.
		this.initRegistrationCheck();
		// Initialize register button.
		this.initSubmitButton('/register');
	},

	initRecovery() {
		// Find the submit button.
		this.submitButton = document.querySelector('.recovery-submit');

		if (this.submitButton.classList.contains('password-reset')) {
			// Initialize simple field input content checking.
			this.initRegistrationCheck();
			// Initialize recovery button.
			this.initSubmitButton('/reset-password');
		} else {
			// Initialize simple field input content checking.
			this.initSimpleCheck();
			// Initialize recovery button.
			this.initSubmitButton('/recovery');
		}
	}
}

document.addEventListener('DOMContentLoaded', () => {
	if (document.body.classList.contains('login')) {
		loginController.initLogin();
	}

	if (document.body.classList.contains('register')) {
		loginController.initRegister();
	}

	if (document.body.classList.contains('recovery')) {
		loginController.initRecovery();
	}
});
