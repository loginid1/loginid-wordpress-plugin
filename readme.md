<!--
*** To avoid retyping too much info. Do a search and replace for the following:
*** twitter_handle
-->





<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- PROJECT LOGO -->
<br />
<p align="center">
  <a href="https://github.com/loginid1/loginid-directweb-plugin">
    TODO:<img src="images/logo.png" alt="Logo" width="80" height="80">
  </a>

  <h3 align="center">LoginID DirectWeb Plugin</h3>

  <p align="center">
    For Wordpress
    <br />
    <a href="https://github.com/loginid1/loginid-directweb-plugin"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/loginid1/loginid-directweb-plugin/issues">Report Bug</a>
    ·
    <a href="https://github.com/loginid1/loginid-directweb-plugin/issues">Request Feature</a>
  </p>
</p>



<!-- TABLE OF CONTENTS -->
## Table of Contents

- [Table of Contents](#table-of-contents)
- [About The Project](#about-the-project)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [Usage](#usage)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)
- [Acknowledgements](#acknowledgements)



<!-- ABOUT THE PROJECT -->
## About The Project

***TODO:***
[![Product Screen Shot][product-screenshot]](https://example.com)

Wordpress Plugin to create a passwordless login experience using LoginID's Direct Web API



<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

As this is a wordpress plugin this project requires wordpress to run

### Installation

1. 
```sh
cd htdocs/wp-contents/plugins
```
2. Download or clone this repo
```sh
git clone https://github.com/loginid1/loginid-directweb-plugin.git
```
3. install php dependencies
```sh
composer install
```
4. Open up wordpress and configure the plugin
5. (Optional) Editing javascript. Edit stuff in srcjs and don't edit javascript in includes (its minified and generated from files in srcjs anyway). You'll need nodejs to run though. 
6. (Optional) Build your own javascript files
```sh
npm i
npm run dev
```

<!-- USAGE EXAMPLES -->
## Usage

This plugin will disable wp-login and wp-register and replace them with custom login and register pages that is compatible with LoginID Direct Web login process.



<!-- ROADMAP -->
## Roadmap

See the [open issues](https://github.com/loginid1/loginid-directweb-plugin/issues) for a list of proposed features (and known issues).


<!-- CONTRIBUTING -->
## Contributing

***TODO:*** Contribution policy

<!-- LICENSE -->
## License

Distributed under the GPLv3 License. See `LICENSE` for more information.



<!-- CONTACT -->
## Contact

LoginID - dev@loginid.io
<!-- - [@twitter_handle](https://twitter.com/twitter_handle) -->

Project Link: [https://github.com/loginid1/loginid-directweb-plugin](https://github.com/loginid1/loginid-directweb-plugin)


<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements
* [The Best Readme Template on Github](https://github.com/othneildrew/Best-README-Template)
* [Wordpress Starter Plugin](https://github.com/arunbasillal/WordPress-Starter-Plugin)


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/loginid1/loginid-directweb-plugin.svg?style=flat-square
[contributors-url]: https://github.com/loginid1/loginid-directweb-plugin/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/loginid1/loginid-directweb-plugin.svg?style=flat-square
[forks-url]: https://github.com/loginid1/loginid-directweb-plugin/network/members
[stars-shield]: https://img.shields.io/github/stars/loginid1/loginid-directweb-plugin.svg?style=flat-square
[stars-url]: https://github.com/loginid1/loginid-directweb-plugin/stargazers
[issues-shield]: https://img.shields.io/github/issues/loginid1/loginid-directweb-plugin.svg?style=flat-square
[issues-url]: https://github.com/loginid1/loginid-directweb-plugin/issues
[license-shield]: https://img.shields.io/github/license/loginid1/loginid-directweb-plugin.svg?style=flat-square
[license-url]: https://github.com/loginid1/loginid-directweb-plugin/blob/master/LICENSE
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=flat-square&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/loginid
[product-screenshot]: images/screenshot.png