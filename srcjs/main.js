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
 * @param type 'login' or 'register' use __loginidAuth.LOGIN or __loginidAuth.REGISTER
 * @param {[k: string]: {value: string, element: string}} additionalPayload additional object, optional.
 */
async function __loginidOnAuthenticate(
  url,
  method,
  type,
  additionalPayload = {}
) {
  const password = document.getElementById(`__loginid_input_password_${type}`).value;
  const isFido2Supported = await __loginidIsFido2Supported();

  const payload = {
    email: {
      value: document.getElementById(`__loginid_input_email_${type}`).value,
      element: document.createElement("input"),
    },
    submit: {
      value: type,
      element: document.createElement("input"),
    },
    shortcode: {
      value: document.getElementById(`__loginid_input_shortcode_${type}`).value,
      element: document.createElement("input"),
    },
    _wpnonce: {
      value: document.getElementById(`__loginid_input_nonce_${type}`).value,
      element: document.createElement("input"),
    },
    ...additionalPayload,
  };

  if (type === __loginidAuth.REGISTER) {
    const username = document.getElementById(`__loginid_input_username_${type}`).value;
    payload["username"] = {
      value: username,
      element: document.createElement("input"),
    };
    const optIn = document.getElementById(
      "__loginid_register-passwordless-opt-in"
    ).checked;
    payload["opt-in"] = {
      value: optIn,
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
    document.getElementById("__loginid_submit_button").value =
      String(type).charAt(0).toUpperCase() + String(type).slice(1);
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
  const udataInput = document.getElementById(`__loginid_input_udata_${type}`);
  const baseURLInput = document.getElementById(`__loginid_input_baseurl_${type}`);
  const apiKeyInput = document.getElementById(`__loginid_input_apikey_${type}`);

  if (
    __loginidIsDefined(baseURLInput) &&
    __loginidIsDefined(apiKeyInput) &&
    __loginidIsDefined(udataInput)
    ) {
    // this page has been approved for fido2 authentication
    const baseURL = baseURLInput.value;
    const apiKey = apiKeyInput.value;
    const udata = udataInput.value;
    let result;
    try {
      const sdk = new DirectWeb(baseURL, apiKey);

      result = await sdk[type](udata);
      // localStorage.setItem('response', JSON.stringify(result));
    } catch ({ name, message, code, errs }) {
      result = { error: { name, message, code, errs } };
    }
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

/**
 * on user click the user an authenticator on this device button on profile page
 */
async function __loginidOnProfilePageAddAuthenticator() {
  // function assumes register from exists
  const udataInput = document.getElementById("__loginid_input_udata");
  const baseURLInput = document.getElementById("__loginid_input_baseurl");
  const apiKeyInput = document.getElementById("__loginid_input_apikey");
  const nonceInput = document.getElementById("__loginid_input_nonce");
  const output = document.getElementById(
    "__loginid_use_an_authenticator_on_this_device_response"
  );
  if (
    __loginidIsDefined(baseURLInput) &&
    __loginidIsDefined(apiKeyInput) &&
    __loginidIsDefined(udataInput) &&
    __loginidIsDefined(output) &&
    __loginidIsDefined(nonceInput)
  ) {
    if (await __loginidIsFido2Supported()) {
      const baseURL = baseURLInput.value;
      const apiKey = apiKeyInput.value;
      const udata = udataInput.value;
      const nonce = nonceInput.value;
      let result;
      try {
        const sdk = new DirectWeb(baseURL, apiKey);
        result = await sdk.register(udata);
      } catch ({ name, message, code, errs }) {
        result = { error: { name, message, code, errs } };
      }

      // thing is, if your browser supports fido2 it probably also supports fetch()
      // because fido2 is newer than ES6 anyway.
      try {
        const response = await fetch(
          `${window.origin}/wp-admin/admin-ajax.php`,
          {
            method: "POST",
            mode: "same-origin",
            headers: { "Content-type": "application/x-www-form-urlencoded" },
            body: `action=loginid_save_to_profile&nonce=${nonce}&loginid=${JSON.stringify(
              result
            )}`,
          }
        );
        output.innerText = await response.text();
      } catch (error) {
        output.innerText = "Failed to make wordpress request";
      }
    } else {
      output.innerText = "Fido2 not supported";
    }
  }
}

async function __loginidOnProfilePageRemoveAuthenticator() {
  const nonceInput = document.getElementById("__loginid_input_nonce");
  const output = document.getElementById(
    "__loginid_remove_authenticator_response"
  );
  if (__loginidIsDefined(nonceInput)) {
    const nonce = nonceInput.value;
    try {
      const response = await fetch(`${window.origin}/wp-admin/admin-ajax.php`, {
        method: "POST",
        mode: "same-origin",
        headers: { "Content-type": "application/x-www-form-urlencoded" },
        body: `action=loginid_remove_from_profile&nonce=${nonce}`,
      });
      output.innerText = await response.text();
    } catch (error) {
      console.log("error", error);
      output.innerText = "Failed to make wordpress request";
    }
  }
}

function __loginidPerformInitialization(type) {
  if (type) {
    document.getElementById(`__loginid_submit_button_${type}`).value = ((type) => {
      if (type === __loginidAuth.REGISTER) {
        return "Register";
      } else {
        return "Login";
      }
    })(type);

    document
      .getElementById(`__loginid_${type}_form`)
      .addEventListener("submit", (event) => {
        event.preventDefault();
        __loginidOnAuthenticate(
          `${window.location.origin}${window.location.pathname}`,
          "POST",
          type
        );
      });
      
    __loginidOnPageLoaded(type);
    const usePasswordLink = document.getElementById(
      "__loginid_use_password_instead"
    );
    if (__loginidIsDefined(usePasswordLink)) {
      usePasswordLink.style.display = "block";
      usePasswordLink.addEventListener("click", (event) => {
        event.preventDefault();
        usePasswordLink.style.display = "none";

        const passwordDiv = document.getElementById("__loginid_password_div");
        passwordDiv.style.display = "block";
        passwordDiv.className = passwordDiv.className.replace(
          /\b__loginid_hide-password\b/g,
          ""
        );
      });
    }
  }
}

// self calling function here to trigger onRegisterPageLoaded()
(function () {
  const registerForm = document.getElementById("__loginid_register_form");
  const loginForm = document.getElementById("__loginid_login_form");

  if(__loginidIsDefined(registerForm)) {
    __loginidPerformInitialization( __loginidAuth.REGISTER)
  }

  if(__loginidIsDefined(loginForm)) {
    __loginidPerformInitialization( __loginidAuth.LOGIN)
  }


  const useAnAuthenticatorOnThisDevice = document.getElementById(
    "__loginid_use_an_authenticator_on_this_device"
  );
  if (__loginidIsDefined(useAnAuthenticatorOnThisDevice)) {
    try {
      document.getElementById("__loginid_set_authenticator").style.display =
        "table-row";
    } catch (error) {
      console.log("non-fatal-error", error);
    }
    useAnAuthenticatorOnThisDevice.addEventListener("click", (event) => {
      event.preventDefault();
      __loginidOnProfilePageAddAuthenticator();
    });
  }

  const removeAuthenticator = document.getElementById(
    "__loginid_remove_authenticator_button"
  );
  if (__loginidIsDefined(removeAuthenticator)) {
    try {
      document.getElementById("__loginid_remove_authenticator").style.display =
        "table-row";
    } catch (error) {
      console.log("non-fatal-error", error);
    }

    removeAuthenticator.addEventListener("click", (event) => {
      event.preventDefault();
      __loginidOnProfilePageRemoveAuthenticator();
    });
  }
})();
