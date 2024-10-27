// Auto count up numbers when the section is in view
const counters = document.querySelectorAll('.stat-number');
const speed = 75; // The higher the number, the slower the count

counters.forEach(counter => {
    const updateCount = () => {
        const target = +counter.getAttribute('data-target'); // Get the target number
        const count = +counter.innerText;
        const format = counter.getAttribute('data-target-format'); // Get the format (K or +)
        
        // Calculate the increment
        const increment = target / speed;

        // If the current count is less than the target, increment the count
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(updateCount, 20);
        } else {
            // Format the final number with "K" or "+"
            let finalCount = target;
            if (target >= 1000) {
                finalCount = (target / 1000).toFixed(1) + 'K'; // Format as 1K, 2K, etc.
            }

            // Add "+" if required
            if (format === '+') {
                finalCount += '+';
            }

            counter.innerText = finalCount; // Display the formatted final number
        }
    };

    // Trigger the count when the section is in view
    const triggerCounting = () => {
        const sectionPosition = document.querySelector('.stats-section').getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (sectionPosition < screenPosition) {
            updateCount();
            window.removeEventListener('scroll', triggerCounting); // Remove event listener once counting starts
        }
    };

    window.addEventListener('scroll', triggerCounting);
});