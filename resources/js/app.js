
import './bootstrap';

import Pikaday from 'pikaday';
import moment from 'moment';

// Initialize Pikaday Date Picker
document.addEventListener('DOMContentLoaded', function() {
    var picker = new Pikaday({
        field: document.getElementById('datepicker'),
        format: 'YYYY-MM-DD',
        onSelect: function(date) {
            var selectedDate = moment(date).format('YYYY-MM-DD');
            fetchAvailableSlots(selectedDate);
        }
    });

    function fetchAvailableSlots(selectedDate) {
        fetch(`/get-available-slots?selectedDate=${selectedDate}`)
            .then(response => response.json())
            .then(slots => {
                const slotsContainer = document.getElementById('slots');
                slotsContainer.innerHTML = ''; // Clear previous slots

                if (slots.length === 0) {
                    slotsContainer.innerHTML = '<p>No slots available.</p>';
                } else {
                    slots.forEach(slot => {
                        const button = document.createElement('button');
                        button.textContent = `${slot.time} ${slot.available ? '' : '(Full)'}`;
                        button.disabled = !slot.available;
                        if (slot.available) {
                            button.addEventListener('click', () => bookSlot(selectedDate, slot.time));
                        }
                        slotsContainer.appendChild(button);
                    });
                }
            });
    }

    function bookSlot(date, time) {
        fetch('/book-slot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                selectedDate: date,
                selectedTime: time,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.success);
                fetchAvailableSlots(date); // Refresh slots after booking
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
