function toggleForgotPassword() {
    const company = document.getElementById('company').value;
    const forgotPasswordDiv = document.getElementById('forgot-password');
    forgotPasswordDiv.style.display = company ? 'block' : 'none';
}
