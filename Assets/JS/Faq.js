document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const faqItem = button.parentElement.parentElement;

        // Hide all other FAQ answers first
        document.querySelectorAll('.faq-item').forEach(item => {
            if (item !== faqItem) {
                item.classList.remove('active');
                item.querySelector('.faq-toggle').textContent = '+'; // Reset button to '+'
            }
        });

        // Toggle the clicked FAQ item
        faqItem.classList.toggle('active');

        // Change button text based on whether FAQ is active
        if (faqItem.classList.contains('active')) {
            button.textContent = '-'; // Show '-' if active
        } else {
            button.textContent = '+'; // Revert back to '+' if not active
        }
    });
});