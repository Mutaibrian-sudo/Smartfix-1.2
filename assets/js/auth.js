// Register Form Validation
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert("Passwords don’t match!");
    }

    // Phone validation (Kenya)
    const phone = document.getElementById('phone').value;
    if (!phone.match(/^254[17]\d{8}$/)) {
        e.preventDefault();
        alert("Use a valid Kenyan phone (254XXX)");
    }
});

// Login Form Animation
const inputs = document.querySelectorAll('.form-control');
inputs.forEach(input => {
    input.addEventListener('focus', () => {
        input.parentElement.classList.add('focused');
    });
    input.addEventListener('blur', () => {
        if (!input.value) input.parentElement.classList.remove('focused');
    });
});