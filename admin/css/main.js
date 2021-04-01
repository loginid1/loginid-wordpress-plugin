const detectHttps = () => {
  const httpsBanner = document.getElementById("__loginid_tls_warning_banner");
  if (!!httpsBanner) {
    const origin = window.origin;
    console.log(origin);
    if (!origin.match(/^http:\/\/localhost(:\d{0,4})?.*$/g)) {
      if (!origin.match(/^https/)) {
        httpsBanner.style.display = "block";
      }
    }
  }
};

window.addEventListener("load", function () {
  detectHttps();
});
