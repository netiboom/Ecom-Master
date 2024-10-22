const navbarToggle = document.getElementById('navbar-toggle');
const mobileMenu = document.getElementById('mobile-menu');
const dropdownMenu = document.getElementById('dropdown-menu');
const servicesBtn = document.getElementById('services-btn');

navbarToggle.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
});

servicesBtn.addEventListener('click', () => {
    dropdownMenu.classList.toggle('hidden');
});

document.addEventListener('click', (event) => {
    const targetElement = event.target;
    if (!servicesBtn.contains(targetElement) && !dropdownMenu.contains(targetElement)) {
        dropdownMenu.classList.add('hidden');
    }
});

