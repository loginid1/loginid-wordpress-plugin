import "core-js/features/promise"; // needed for babel compile
import "regenerator-runtime/runtime"; // needed for babel compile

import DirectWeb from "./loginid.direct_web.min.js";
import Browser from "./loginid.browser.min.js";

async function __loginidIsFido2Supported() {
  try {
    return await Browser.isFido2Supported();
  } catch (error) {
    return false;
  }
}

const __loginidAuth = Object.freeze({
  LOGIN: "login",
  REGISTER: "register",
});

/**
 *
 * @param url url in string
 * @param method method in string, POST GET or whatever
 * @param authType 'login' or 'register' use __loginidAuth.LOGIN or __loginidAuth.REGISTER
 * @param {[k: string]: {value: string, element: string}} additionalPayload additional object, optional.
 */
async function __loginidOnAuthenticate(
  url,
  method,
  authType,
  additionalPayload = {}
) {
  const password = document.getElementById("__loginid_input_password").value;
  const username = document.getElementById("__loginid_input_username").value;
  const isFido2Supported = await __loginidIsFido2Supported();

  const payload = {
    email: {
      value: document.getElementById("__loginid_input_email").value,
      element: document.createElement("input"),
    },
    submit: {
      value: authType,
      element: document.createElement("input"),
    },
    shortcode: {
      value: document.getElementById("__loginid_input_shortcode").value,
      element: document.createElement("input"),
    },
    ...additionalPayload,
  };

  if (authType === __loginidAuth.REGISTER) {
    payload['username'] = {
      value: username,
      element: document.createElement("input"),
    };
  }

  if (password.length > 0) {
    payload["password"] = {
      value: password,
      element: document.createElement("input"),
    };
  }

  if (isFido2Supported) {
    payload["fido2"] = {
      value: "supported",
      element: document.createElement("input"),
    };
  }

  const passwordDiv = document.getElementById("__loginid_password_div");
  const isPasswordDisplayed = !passwordDiv.classList.contains(
    "__loginid_hide-password"
  );
  if (!isPasswordDisplayed && !isFido2Supported) {
    passwordDiv.classList = passwordDiv.classList.remove(
      "__loginid_hide-password"
    );
    document.getElementById("__loginid_submit_button").value = "Register";
    return;
  }

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
  document.createElement("form").submit.call(hiddenForm);
}

/**
 * determines if a dom object exists
 *
 * @param Object domObject
 * @return true if defined; false if does not exist
 */
function __loginidIsDefined(domObject) {
  return typeof domObject !== undefined && domObject !== null;
}

/**
 *
 * @param type 'login' or 'register' use __loginidAuth.LOGIN or __loginidAuth.REGISTER
 */
async function __loginidOnPageLoaded(type) {
  // function assumes register from exists
  const email = document.getElementById("__loginid_input_email").value;
  const baseURLInput = document.getElementById("__loginid_input_baseurl");
  const apiKeyInput = document.getElementById("__loginid_input_apikey");

  if (__loginidIsDefined(baseURLInput) && __loginidIsDefined(apiKeyInput)) {
    // this page has been approved for fido2 authentication
    const baseURL = baseURLInput.value;
    const apiKey = apiKeyInput.value;
    let result;
    try {
      const sdk = new DirectWeb(baseURL, apiKey);

      result = await sdk[type](email);
    } catch ({ name, message }) {
      result = { error: { name, message } };
    }
    // console.log("result", result); // TODO: remove this
    localStorage.setItem('last_result', JSON.stringify(result))
    __loginidOnAuthenticate(
      `${window.location.origin}${window.location.pathname}`,
      "POST",
      type,
      {
        loginid: {
          value: JSON.stringify(result),
          element: document.createElement("input"),
        },
      }
    );
  }
  // otherwise page not approved for fido2 authentication
}

// self calling function here to trigger onRegisterPageLoaded()
(function () {
  const registerForm = document.getElementById("__loginid_register_form");
  const loginForm = document.getElementById("__loginid_login_form");
  let type = __loginidIsDefined(registerForm)
    ? __loginidAuth.REGISTER
    : __loginidIsDefined(loginForm)
    ? __loginidAuth.LOGIN
    : false;

  if (type) {
    document.getElementById(`__loginid_${type}_form`).addEventListener("submit", (event) => {
      event.preventDefault();
      __loginidOnAuthenticate(
        `${window.location.origin}${window.location.pathname}`,
        "POST",
        type
      );
    });
    __loginidOnPageLoaded(type);
  }
})();
