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

        function updateBookingTotal() {
            if (!propertySelect || !checkIn || !checkOut || !preview) return;
            const selected = propertySelect.options[propertySelect.selectedIndex];
            const price = parseFloat(selected?.dataset.price || '0');
            const start = checkIn.value ? new Date(checkIn.value + 'T00:00:00') : null;
            const end = checkOut.value ? new Date(checkOut.value + 'T00:00:00') : null;

            if (!price || !start || !end || end <= start) {
                preview.textContent = 'DKK 0.00';
                return;
            }

            const nights = Math.max(1, Math.round((end - start) / (1000 * 60 * 60 * 24)));
            preview.textContent = 'DKK ' + (nights * price).toFixed(2);
        }

        [propertySelect, checkIn, checkOut].forEach((field) => {
            if (field) field.addEventListener('change', updateBookingTotal);
        });
        updateBookingTotal();
    }
});
