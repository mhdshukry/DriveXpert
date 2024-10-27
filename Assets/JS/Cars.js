        // Array of car details: images, brands, models
        const cars = [
            {
                image: 'Assets/Images/car5.png', // Replace with your actual image path
                brand: 'LEXUS',
                model: 'LC SERIES'
            },
            {
                image: 'Assets/Images/car2.png', // Replace with your actual image path
                brand: 'BMW',
                model: 'M SERIES'
            },
            {
                image: 'Assets/Images/car1.png', // Replace with your actual image path
                brand: 'MERCEDES',
                model: 'S CLASS'
            },
            {
                image: 'Assets/Images/car4.png', // Replace with your actual image path
                brand: 'AUDI',
                model: 'A SERIES'
            }
        ];

        let currentIndex = 0;
const carImageElement = document.querySelector('.car-image1');
const carBrandElement = document.querySelector('.car-brand');
const carModelElement = document.querySelector('.car-model');

// Function to update car information based on the current index
function updateCarInfo(direction) {
    const car = cars[currentIndex];

    // Remove any previous animations
    carImageElement.classList.remove('slide-in-left', 'slide-in-right', 'slide-out-left', 'slide-out-right');
    carBrandElement.classList.remove('slide-in-vertical', 'slide-out-vertical');
    carModelElement.classList.remove('slide-in-vertical', 'slide-out-vertical');

    // Add exit animations based on direction
    if (direction === 'next') {
        carImageElement.classList.add('slide-out-left');
    } else if (direction === 'prev') {
        carImageElement.classList.add('slide-out-right');
    }
    carBrandElement.classList.add('slide-out-vertical');
    carModelElement.classList.add('slide-out-vertical');

    setTimeout(() => {
        // Update the car information
        carImageElement.src = car.image;
        carBrandElement.textContent = car.brand;
        carModelElement.textContent = car.model;

        // Remove exit animations
        carImageElement.classList.remove('slide-out-left', 'slide-out-right');
        carBrandElement.classList.remove('slide-out-vertical');
        carModelElement.classList.remove('slide-out-vertical');

        // Add entry animations based on direction
        if (direction === 'next') {
            carImageElement.classList.add('slide-in-right');
        } else if (direction === 'prev') {
            carImageElement.classList.add('slide-in-left');
        }
        carBrandElement.classList.add('slide-in-vertical');
        carModelElement.classList.add('slide-in-vertical');
    }, 800); // Wait for the exit animation to complete
}

// Event listeners for next and previous buttons
document.querySelector('.slider-next').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % cars.length; // Go to the next car, loop if at the end
    updateCarInfo('next');
});

document.querySelector('.slider-prev').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + cars.length) % cars.length; // Go to the previous car, loop if at the beginning
    updateCarInfo('prev');
});