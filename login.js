let menu = document.querySelector("#menu-btn");
let navbar = document.querySelector(".header .navbar");
menu.onclick = ()=>{
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}
window.onscroll = ()=>{
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');
}

function toggleSignupForm() {
  document.querySelector('title').textContent = 'Sign Up';
  document.getElementById('login-form').style.display = 'none';
  document.getElementById('signup-form').style.display = 'block';
}
document.getElementById('toggle-signup').addEventListener('click', toggleSignupForm);


function toggleLoginForm() {
  document.querySelector('title').textContent = 'Login';
  document.getElementById('signup-form').style.display = 'none';
  document.getElementById('login-form').style.display = 'block';
}
document.getElementById('toggle-login').addEventListener('click', toggleLoginForm);

function isValidPassword(password) {
  return password.length >= 6; 
}

document.getElementById('login').addEventListener('submit', function (e) {
  e.preventDefault();

  const username = document.getElementById('login-username').value.trim();
  const password = document.getElementById('login-password').value.trim();
  const errorDiv1 = document.getElementById('login-error');

  errorDiv1.style.display = 'none';
  errorDiv1.textContent = '';

  if (!username) {
    showError( errorDiv1, 'Please enter your username.')
    return;
  }

  if (!password) {
    showError(errorDiv1, 'Please enter your password.');
    return;
  }
  this.submit();
});

// sign up js
document.getElementById('signup').addEventListener('submit', function (e) {
  e.preventDefault(); 

  const name = document.getElementById('signup-name').value.trim();
  const email = document.getElementById('signup-email').value.trim();
  const username = document.getElementById('signup-username').value.trim();
  const password = document.getElementById('signup-password').value.trim();
  const phone = document.getElementById('signup-phone').value.trim();
  const address = document.getElementById('signup-address').value.trim();

  const errorDiv = document.getElementById('signup-error');
  errorDiv.style.display = 'none'; 
  errorDiv.textContent = ''; 

  function isValidEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
  }

  function isValidPassword(password) {
      return password.length >= 6; 
  }

  if (!name) {
      showError(errorDiv, 'Please enter your full name.');
      return;
  }
  if (!email) {
      showError(errorDiv, 'Please enter your email.');
      return;
  }
  if (!isValidEmail(email)) {
      showError(errorDiv, 'Please enter a valid email address.');
      return;
  }
  if (!username) {
      showError(errorDiv, 'Please enter a username.');
      return;
  }
  if (!password) {
      showError(errorDiv, 'Please enter a password.');
      return;
  }
  if (!isValidPassword(password)) {
      showError(errorDiv, 'Password must be at least 6 characters long.');
      return;
  }
  if (!phone) {
      showError(errorDiv, 'Please enter your phone number.');
      return;
  }
  if (!address) {
      showError(errorDiv, 'Please enter your address.');
      return;
  }

  this.submit();
});


function showError(errorDiv, message) {
  errorDiv.style.display = 'block';
  errorDiv.textContent = message;
}