document.getElementById('loginForm').addEventListener('submit', function (e) {
    const username = e.target.username.value;
    const password = e.target.password.value;

    if (!username || !password) {
        e.preventDefault();
        alert('Please fill in all fields!');
    }
    if (window.history.replaceState) {
        window.history.replaceState(null, null, "index.php"); // Redirect to main page
    }
}