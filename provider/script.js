// JavaScript validation for form submission
document.getElementById('ad-form').addEventListener('submit', function(event) {
    const name = document.getElementById('name').value;
    const description = document.getElementById('description').value;
    const price = document.getElementById('price').value;
    const availability = document.getElementById('availability').value;

    if (!name || !description || !price || !availability) {
        alert('Please fill in all required fields.');
        event.preventDefault(); // Prevent form submission
    }
});

// JavaScript validation for editing the ad
document.getElementById('edit-ad-form').addEventListener('submit', function(event) {
    const name = document.getElementById('name').value;
    const description = document.getElementById('description').value;
    const price = document.getElementById('price').value;

    if (!name || !description || !price) {
        alert('Please fill in all required fields.');
        event.preventDefault(); // Prevent form submission
    }
});
