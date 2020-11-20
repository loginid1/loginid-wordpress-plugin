import "core-js/features/promise"; // needed for babel compile
import "regenerator-runtime/runtime";  // needed for babel compile

import DirectWeb from "./loginid.direct_web.min.js";
import Browser from './loginid.browser.min.js'

async function __loginidIsFido2Supported() {
  try {
    return await Browser.isFido2Supported();
  } catch (error) {
    return false;
  }
}

async function __loginidOnRegister(url, method) {
  const password = document.getElementById("__loginid_input_password").value;

  if ( await __loginidIsFido2Supported() || password.length > 0) {
    console.log('fido2 supported or ', password, ' > 0')
    const payload = {
      email: {
        value: document.getElementById("__loginid_input_email").value,
        element: document.createElement("input"),
      },
      username: {
        value: document.getElementById("__loginid_input_username").value,
        element: document.createElement("input"),
      },
      password: {
        value: password,
        element: document.createElement("input"),
      },
      debug: {
        value: "debug",
        element: document.createElement("input"),
      },
      submit: {
        value: "register",
        element: document.createElement("input"),
      }
    };

    // generates hidden form to make the post request
    const hiddenForm = document.createElement("form");
    hiddenForm.setAttribute("action", url);
    hiddenForm.setAttribute("method", method);
    hiddenForm.style = "display: none;";
    for (const [key, { value, element }] of Object.entries(payload)) {
      element.setAttribute("type", "hidden");
      element.setAttribute("name", key);
      element.setAttribute("value", value);
      hiddenForm.appendChild(element);
    }
    document.body.appendChild(hiddenForm);
    // hiddenForm.submit();
    // document.createElement('form').submit.call(hiddenForm);
  } else {
    document.getElementById('__loginid_password_div').style = 'display: block;'
    console.log('here')
  }
}

const registerForm = document.getElementById("__loginid_register_form");
if (typeof registerForm !== undefined && registerForm !== null) {
  registerForm.addEventListener("submit", (event) => {
    event.preventDefault();
    __loginidOnRegister(
      `${window.location.origin}${window.location.pathname}`,
      "POST"
    );
  });
}

async function onRegisterPageLoaded() {
  const email = document.getElementById("__loginid_input_email").value;
  const baseURL = document.getElementById("__loginid_input_baseurl").value;
  const apiKey = document.getElementById("__loginid_input_apikey").value;
  try {
    const sdk = new DirectWeb(baseURL, apiKey);
    console.log("success");
    const response = await sdk.register(email);
  } catch (error) {
    console.log("error");
  }
}
