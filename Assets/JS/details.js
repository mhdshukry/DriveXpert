function toggleSpecs() {
    const specs = document.getElementById("carSpecs");

    if (specs.classList.contains('visible')) {
        specs.classList.remove('visible');
        setTimeout(() => {
            specs.style.display = 'none'; // Ensures display none after fade out
        }, 500); // Matches the transition duration
    } else {
        specs.style.display = 'block'; // Immediately set display block for smooth fade in
        setTimeout(() => specs.classList.add('visible'), 10); // Slight delay for transition
    }
}