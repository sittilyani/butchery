            document.addEventListener('DOMContentLoaded', () => {
                const hamburger = document.querySelector('.hamburger');
                const mainNav = document.querySelector('.main-nav');
                const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
                const dateTimeDisplay = document.getElementById('date-time');

                // Function to update date and time
                function updateDateTime() {
                        const now = new Date();
                        const options = {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                        };
                        dateTimeDisplay.textContent = now.toLocaleDateString('en-US', options);
                }

                // Update date and time initially and every second
                if (dateTimeDisplay) {
                        updateDateTime();
                        setInterval(updateDateTime, 1000);
                }


                // Hamburger menu toggle
                if (hamburger && mainNav) {
                        hamburger.addEventListener('click', () => {
                                mainNav.classList.toggle('is-active');
                                hamburger.classList.toggle('is-active');
                        });
                }

                // Dropdown toggle for mobile
                dropdownToggles.forEach(toggle => {
                        toggle.addEventListener('click', (e) => {
                                // Prevent default link behavior if it's just a toggle
                                if (window.innerWidth <= 768) { // Only apply for mobile breakpoint
                                        e.preventDefault();
                                        const parentDropdown = toggle.closest('.dropdown');
                                        if (parentDropdown) {
                                                parentDropdown.classList.toggle('is-active');
                                                // Close other open dropdowns in mobile view
                                                document.querySelectorAll('.dropdown.is-active').forEach(openDropdown => {
                                                        if (openDropdown !== parentDropdown) {
                                                                openDropdown.classList.remove('is-active');
                                                        }
                                                });
                                        }
                                }
                        });
                });

                // Close mobile menu when a link is clicked
                document.querySelectorAll('.main-nav .nav-link').forEach(link => {
                        link.addEventListener('click', () => {
                                if (window.innerWidth <= 768) {
                                        mainNav.classList.remove('is-active');
                                        hamburger.classList.remove('is-active');
                                        // Close any open dropdowns
                                        document.querySelectorAll('.dropdown.is-active').forEach(openDropdown => {
                                                openDropdown.classList.remove('is-active');
                                        });
                                }
                        });
                });
        });
