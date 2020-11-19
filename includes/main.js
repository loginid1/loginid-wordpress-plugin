function __loginidOnRegister(url, method) {
  let data = {
    email: {
      value: document.getElementById("__loginid_input_email").value,
      element: document.createElement("input"),
    },
    username: {
      value: document.getElementById("__loginid_input_username").value,
      element: document.createElement("input"),
    },
    password: {
      value: document.getElementById("__loginid_input_password").value,
      element: document.createElement("input"),
    },
    debug: {
      value: "debug",
      element: document.createElement("input"),
    },
  };

  console.log("register", url, method, data);

  // generates hidden form to make the post request
  const hiddenForm = document.createElement("form");
  hiddenForm.setAttribute("action", url);
  hiddenForm.setAttribute("method", method);
  hiddenForm.style="display: none;"
  for (const [key, { value, element }] of Object.entries(data)) {
    element.setAttribute("type", "hidden");
    element.setAttribute("name", key);
    element.setAttribute("value", value);
    hiddenForm.appendChild(element);
  }
  document.body.appendChild(hiddenForm)
  hiddenForm.submit();
}

const registerForm = document.getElementById("__loginid_register_form");
if (typeof registerForm !== undefined && registerForm !== null) {

  registerForm.addEventListener("submit", (event) => {
    event.preventDefault();
    __loginidOnRegister(`${window.location.origin}${window.location.pathname}`, "POST");
  });
}
