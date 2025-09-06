document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    form.addEventListener('submit', function(e) {
        if (!username.value.trim() || !password.value.trim()) {
            e.preventDefault();
            alert('Please fill in all fields');
            return;
        }

        // Optional: Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Logging in...';
    });

    // Real-time validation
    username.addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    password.addEventListener('blur', function() {
        if (!this.value.trim()) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});