Getting Started With Configurable Page
=======================================

This module allows CMS users to create pages with customisable fields that are selectable from an admin defined list. Fields are created and managed by the module [satrun77/editablefield](https://github.com/satrun77/silverstripe-editablefield)

## Table of Contents

- [1 Installation](#installation)
- [2 How to use it?](user-docs.md)
- [3 License](#license)

## Installation
> Requirements SilverStripe 3.1+

### 1. Download silverstripe-configurablepage with composer
``` bash
$ php composer.phar require satrun77/configurablepage
```

### 2. Download silverstripe-gridfieldeditablemanymanyextracolumns manually
- Open the following url in your browser. It should download a zip file containing the module files.
[https://github.com/satrun77/silverstripe-gridfieldeditablemanymanyextracolumns/zipball/master/](https://github.com/satrun77/silverstripe-gridfieldeditablemanymanyextracolumns/zipball/master/)

- Uncompress the file, move the folder to the root directory of your Silverstripe application, and rename it to **gridfieldeditablemanymanyextracolumns**.
- **Note:** This module is not managed by composer. You will need to upgrade the module manually.

### 2. Clear CMS cache
* Login as administrator
* Navigation to http://yousite.com/dev/build

## License

This bundle is under the MIT license. View the [LICENSE.md](../../../LICENSE.md) file for the full copyright and license information.
