## Omo extension for Magento2
<!--Name of the project -->

Compatible with Community Edition, Commerce and Cloud.

### Features:
* Customer sync
* Orders sync
* Abandoned cart sync

### Composer install the package
```
composer require omotech/magento2-integration
```
Run Magento setup to install the module
```
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

<!-- Write description here -->

## System Requirements
* PHP 7.4/8
* Magento 2.3/2.4
* Elastic Search 7
* RabbitMQ
<!-- mention all the system requirements -->
