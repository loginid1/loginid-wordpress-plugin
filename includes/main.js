function __loginidOnRegister(event, url, method) {
  event.preventDefault();
  console.log("register", url, method);

  const username = document.getElementById("__loginid_input_email");
  const email = document.getElementById("__loginid_input_username");
  const password = document.getElementById("__loginid_input_password");

  let data = { username, password, email };

  fetch(url, {
    method,
    body: JSON.stringify(data),
  });
}
