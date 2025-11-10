// Array of car details: images, brands, models and specs
const assetPrefix = document.body?.dataset.assetPrefix ?? "";

const cars = [
  {
    image: "Assets/Images/uploads/car5.png",
    brand: "LEXUS",
    model: "LC SERIES",
    logo: "Assets/Images/uploads/lexus.png",
    seatCount: 5,
    maxSpeed: 200,
    efficiency: "14.2 km/l",
    pricePerDay: 50,
  },
  {
    image: "Assets/Images/uploads/car2.png",
    brand: "BMW",
    model: "X5",
    logo: "Assets/Images/uploads/bmw.png",
    seatCount: 5,
    maxSpeed: 250,
    efficiency: "8.5 km/l",
    pricePerDay: 90,
  },
  {
    image: "Assets/Images/uploads/car4.png",
    brand: "MERCEDES",
    model: "E-CLASS",
    logo: "Assets/Images/uploads/benz.png",
    seatCount: 5,
    maxSpeed: 260,
    efficiency: "10.2 km/l",
    pricePerDay: 95,
  },
  {
    image: "Assets/Images/uploads/car7.png",
    brand: "AUDI",
    model: "A6",
    logo: "Assets/Images/uploads/audi.png",
    seatCount: 5,
    maxSpeed: 240,
    efficiency: "9.5 km/l",
    pricePerDay: 85,
  },
];

let currentIndex = 0;
const carImageElement = document.querySelector(".car-image1");
const carBrandElement = document.querySelector(".car-brand");
const carModelElement = document.querySelector(".car-model");

const specLogoElement = document.querySelector('[data-spec="logo"]');
const specBrandElement = document.querySelector('[data-spec="brand"]');
const specSeatsElement = document.querySelector('[data-spec="seats"]');
const specSpeedElement = document.querySelector('[data-spec="speed"]');
const specEfficiencyElement = document.querySelector(
  '[data-spec="efficiency"]'
);
const specPriceElement = document.querySelector('[data-spec="price"]');

function updateSpecDetails(car) {
  if (specLogoElement) {
    specLogoElement.src = assetPrefix + car.logo;
    specLogoElement.alt = `${car.brand} logo`;
  }
  if (specBrandElement) {
    specBrandElement.textContent = car.brand;
  }
  if (specSeatsElement) {
    specSeatsElement.textContent = `${car.seatCount} Seats`;
  }
  if (specSpeedElement) {
    specSpeedElement.textContent = `${car.maxSpeed} Km/h`;
  }
  if (specEfficiencyElement) {
    specEfficiencyElement.textContent = car.efficiency;
  }
  if (specPriceElement) {
    specPriceElement.textContent = `$${car.pricePerDay}/day`;
  }
}

// Function to update car information based on the current index
function updateCarInfo(direction) {
  const car = cars[currentIndex];

  if (!carImageElement || !carBrandElement || !carModelElement) {
    updateSpecDetails(car);
    return;
  }

  if (!direction) {
    carImageElement.src = assetPrefix + car.image;
    carBrandElement.textContent = car.brand;
    carModelElement.textContent = car.model;
    updateSpecDetails(car);
    return;
  }

  // Remove any previous animations
  carImageElement.classList.remove(
    "slide-in-left",
    "slide-in-right",
    "slide-out-left",
    "slide-out-right"
  );
  carBrandElement.classList.remove("slide-in-vertical", "slide-out-vertical");
  carModelElement.classList.remove("slide-in-vertical", "slide-out-vertical");

  // Add exit animations based on direction
  if (direction === "next") {
    carImageElement.classList.add("slide-out-left");
  } else if (direction === "prev") {
    carImageElement.classList.add("slide-out-right");
  }
  carBrandElement.classList.add("slide-out-vertical");
  carModelElement.classList.add("slide-out-vertical");

  setTimeout(() => {
    // Update the car information
    carImageElement.src = assetPrefix + car.image;
    carBrandElement.textContent = car.brand;
    carModelElement.textContent = car.model;
    updateSpecDetails(car);

    // Remove exit animations
    carImageElement.classList.remove("slide-out-left", "slide-out-right");
    carBrandElement.classList.remove("slide-out-vertical");
    carModelElement.classList.remove("slide-out-vertical");

    // Add entry animations based on direction
    if (direction === "next") {
      carImageElement.classList.add("slide-in-right");
    } else if (direction === "prev") {
      carImageElement.classList.add("slide-in-left");
    }
    carBrandElement.classList.add("slide-in-vertical");
    carModelElement.classList.add("slide-in-vertical");
  }, 800); // Wait for the exit animation to complete
}

// Event listeners for next and previous buttons
const nextButton = document.querySelector(".slider-next");
const prevButton = document.querySelector(".slider-prev");

if (nextButton) {
  nextButton.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % cars.length; // Go to the next car, loop if at the end
    updateCarInfo("next");
  });
}

if (prevButton) {
  prevButton.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + cars.length) % cars.length; // Go to the previous car, loop if at the beginning
    updateCarInfo("prev");
  });
}

// Ensure initial details are in sync when the page loads
updateCarInfo();
