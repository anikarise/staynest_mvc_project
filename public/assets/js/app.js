document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.getElementById('roleSelect');
    const companyField = document.getElementById('companyField');

    function toggleCompanyField() {
        if (!roleSelect || !companyField) return;
        companyField.style.display = roleSelect.value === 'host' ? 'block' : 'none';
    }

    toggleCompanyField();
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleCompanyField);
    }

    const countryCodeSelect = document.getElementById('countryCodeSelect');
    const phoneInput = document.getElementById('phoneInput');
    const phoneHint = document.getElementById('phoneHint');
    const phoneError = document.getElementById('phoneError');

    function selectedPhoneRules() {
        if (!countryCodeSelect) return null;
        const selected = countryCodeSelect.options[countryCodeSelect.selectedIndex];
        return {
            min: parseInt(selected?.dataset.min || '0', 10),
            max: parseInt(selected?.dataset.max || '20', 10),
            placeholder: selected?.dataset.placeholder || '',
        };
    }

    function setPhoneError(message) {
        if (!phoneError) return;
        phoneError.textContent = message;
        phoneError.hidden = message === '';
    }

    function updatePhonePlaceholder() {
        const rules = selectedPhoneRules();
        if (!rules || !phoneInput) return;
        phoneInput.placeholder = rules.placeholder;
        phoneInput.maxLength = rules.max;
        if (phoneHint) {
            phoneHint.textContent = `Numbers only. Use ${rules.min === rules.max ? rules.min : `${rules.min}-${rules.max}`} digits.`;
        }
    }

    function cleanPhoneInput() {
        if (!phoneInput) return;
        const rules = selectedPhoneRules();
        const maxLength = rules ? rules.max : 20;
        phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, maxLength);
        setPhoneError('');
    }

    function validatePhoneInput() {
        if (!phoneInput) return true;
        cleanPhoneInput();
        const rules = selectedPhoneRules();
        const value = phoneInput.value;

        if (value === '') {
            setPhoneError('Phone number is required.');
            phoneInput.focus();
            return false;
        }

        if (!rules || value.length < rules.min || value.length > rules.max) {
            setPhoneError('Please enter a valid phone number.');
            phoneInput.focus();
            return false;
        }

        setPhoneError('');
        return true;
    }

    if (countryCodeSelect && phoneInput) {
        updatePhonePlaceholder();
        countryCodeSelect.addEventListener('change', () => {
            updatePhonePlaceholder();
            validatePhoneInput();
        });
        phoneInput.addEventListener('beforeinput', (event) => {
            if (event.data && /\D/.test(event.data)) {
                event.preventDefault();
            }
        });
        phoneInput.addEventListener('input', cleanPhoneInput);

        const registerForm = phoneInput.closest('form');
        if (registerForm) {
            registerForm.addEventListener('submit', (event) => {
                if (!validatePhoneInput()) {
                    event.preventDefault();
                }
            });
        }
    }

    const hostContactCountryCodeSelect = document.getElementById('hostContactCountryCodeSelect');
    const hostContactPhoneInput = document.getElementById('hostContactPhoneInput');
    const hostContactPhoneHint = document.getElementById('hostContactPhoneHint');
    const hostContactPhoneError = document.getElementById('hostContactPhoneError');
    const hostContactEmailInput = document.getElementById('hostContactEmailInput');
    const hostContactEmailError = document.getElementById('hostContactEmailError');

    function selectedHostContactPhoneRules() {
        if (!hostContactCountryCodeSelect) return null;
        const selected = hostContactCountryCodeSelect.options[hostContactCountryCodeSelect.selectedIndex];
        return {
            min: parseInt(selected?.dataset.min || '0', 10),
            max: parseInt(selected?.dataset.max || '20', 10),
            placeholder: selected?.dataset.placeholder || '',
        };
    }

    function setHostContactPhoneError(message) {
        if (!hostContactPhoneError) return;
        hostContactPhoneError.textContent = message;
        hostContactPhoneError.hidden = message === '';
    }

    function updateHostContactPhonePlaceholder() {
        const rules = selectedHostContactPhoneRules();
        if (!rules || !hostContactPhoneInput) return;
        hostContactPhoneInput.placeholder = rules.placeholder;
        hostContactPhoneInput.maxLength = rules.max;
        if (hostContactPhoneHint) {
            hostContactPhoneHint.textContent = `Numbers only. Use ${rules.min === rules.max ? rules.min : `${rules.min}-${rules.max}`} digits.`;
        }
    }

    function cleanHostContactPhoneInput() {
        if (!hostContactPhoneInput) return;
        const rules = selectedHostContactPhoneRules();
        const maxLength = rules ? rules.max : 20;
        hostContactPhoneInput.value = hostContactPhoneInput.value.replace(/\D/g, '').slice(0, maxLength);
        setHostContactPhoneError('');
    }

    function validateHostContactPhoneInput() {
        if (!hostContactPhoneInput) return true;
        cleanHostContactPhoneInput();
        const rules = selectedHostContactPhoneRules();
        const value = hostContactPhoneInput.value;

        if (value === '') {
            setHostContactPhoneError('Phone number is required.');
            hostContactPhoneInput.focus();
            return false;
        }

        if (!rules || value.length < rules.min || value.length > rules.max) {
            setHostContactPhoneError('Please enter a valid phone number.');
            hostContactPhoneInput.focus();
            return false;
        }

        setHostContactPhoneError('');
        return true;
    }

    function setHostContactEmailError(message) {
        if (!hostContactEmailError) return;
        hostContactEmailError.textContent = message;
        hostContactEmailError.hidden = message === '';
    }

    function validateHostContactEmailInput() {
        if (!hostContactEmailInput) return true;
        const value = hostContactEmailInput.value.trim();
        const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

        if (value === '') {
            setHostContactEmailError('Contact email is required.');
            hostContactEmailInput.focus();
            return false;
        }

        if (!isValidEmail) {
            setHostContactEmailError('Enter a valid contact email address.');
            hostContactEmailInput.focus();
            return false;
        }

        setHostContactEmailError('');
        return true;
    }

    if (hostContactCountryCodeSelect && hostContactPhoneInput) {
        updateHostContactPhonePlaceholder();
        hostContactCountryCodeSelect.addEventListener('change', () => {
            updateHostContactPhonePlaceholder();
            validateHostContactPhoneInput();
        });
        hostContactPhoneInput.addEventListener('beforeinput', (event) => {
            if (event.data && /\D/.test(event.data)) {
                event.preventDefault();
            }
        });
        hostContactPhoneInput.addEventListener('input', cleanHostContactPhoneInput);
        if (hostContactEmailInput) {
            hostContactEmailInput.addEventListener('input', () => setHostContactEmailError(''));
        }

        const hostForm = hostContactPhoneInput.closest('form');
        if (hostForm) {
            hostForm.addEventListener('submit', (event) => {
                const isEmailValid = validateHostContactEmailInput();
                const isPhoneValid = validateHostContactPhoneInput();
                if (!isEmailValid || !isPhoneValid) {
                    event.preventDefault();
                    if (!isEmailValid && hostContactEmailInput) {
                        hostContactEmailInput.focus();
                    }
                }
            });
        }
    }

    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (!confirm(element.dataset.confirm || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        const propertySelect = bookingForm.querySelector('select[name="property_id"]');
        const checkIn = bookingForm.querySelector('input[name="check_in_date"]');
        const checkOut = bookingForm.querySelector('input[name="check_out_date"]');
        const preview = document.getElementById('bookingTotalPreview');
        const durationError = document.getElementById('bookingDurationError');
        const maxBookingNights = 60;

        function setBookingDurationError(message) {
            if (durationError) {
                durationError.textContent = message;
                durationError.hidden = message === '';
            }
            if (checkOut) {
                checkOut.setCustomValidity(message);
            }
        }

        function updateBookingTotal() {
            if (!propertySelect || !checkIn || !checkOut || !preview) return;
            const selected = propertySelect.options[propertySelect.selectedIndex];
            const price = parseFloat(selected?.dataset.price || '0');
            const start = checkIn.value ? new Date(checkIn.value + 'T00:00:00') : null;
            const end = checkOut.value ? new Date(checkOut.value + 'T00:00:00') : null;

            setBookingDurationError('');

            if (!price || !start || !end || end <= start) {
                preview.textContent = 'DKK 0.00';
                return;
            }

            const nights = Math.max(1, Math.round((end - start) / (1000 * 60 * 60 * 24)));
            if (nights > maxBookingNights) {
                preview.textContent = 'DKK 0.00';
                setBookingDurationError('Bookings cannot be longer than 60 days.');
                return;
            }

            preview.textContent = 'DKK ' + (nights * price).toFixed(2);
        }

        [propertySelect, checkIn, checkOut].forEach((field) => {
            if (field) field.addEventListener('change', updateBookingTotal);
        });
        bookingForm.addEventListener('submit', (event) => {
            updateBookingTotal();
            if (checkOut && !checkOut.checkValidity()) {
                event.preventDefault();
                checkOut.reportValidity();
            }
        });
        updateBookingTotal();
    }
});
