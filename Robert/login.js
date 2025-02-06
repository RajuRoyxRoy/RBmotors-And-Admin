// Predefined credentials for each company
const credentials = {
    'admin': {
        'username': 'admin_user',
        'password': 'admin_pass'
    },
    'rb_motors': {
        'username': 'rb_user',
        'password': 'rb_pass'
    },
    'rb_station': {
        'username': 'rb_station_user',
        'password': 'rb_station_pass'
    },
    'sb_station': {
        'username': 'sb_user',
        'password': 'sb_pass'
    }
};

document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    const company = document.getElementById('company').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const message = document.getElementById('message');

    // Validate the credentials
    if (credentials[company]) {
        const validUser = credentials[company].username;
        const validPass = credentials[company].password;

        if (username === validUser && password === validPass) {
            // Successful login
            message.style.color = 'green';
            message.textContent = `Login successful! Welcome, ${username} from ${company}.`;
            // Redirect to the company's dashboard or another page
            if (company === 'admin') {
                window.location.href = 'admin.html';
            } else if (company === 'rb_motors') {
                window.location.href = 'rb-motors.html'; // Replace with the actual dashboard URL
            } else if (company === 'rb_station') {
                window.location.href = 'rb-filling-station.html'; // Replace with the actual dashboard URL
            } else if (company === 'sb_station') {
                window.location.href = 'sb-filling-station.html'; // Replace with the actual dashboard URL
            }
            // window.location.href = 'dashboard.html';
        } else {
            // Invalid credentials
            message.style.color = 'red';
            message.textContent = 'Invalid username or password.';
        }
    } else {
        // Invalid company selected
        message.style.color = 'red';
        message.textContent = 'Invalid company selected.';
    }
});
