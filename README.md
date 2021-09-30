# Croatian fiscalization plugin

**Note**: This package is still under development

Enables your shop to communicate with Croatian FINA when a customer makes a purchase.

You do not need to send data to FINA if a customer makes offline purchase (bank transfer or paying on delivery).
This is configurable via plugin configuration.

Every order will get its bill sequence, bill unique identifier and issuer security code according to FINA standards.
You will see in order detail view (inside admin) those data. If for any reason those data
could not be fiscalized, you will see log of errors that happened. You try to resend data by
clicking the button under error logs. 

## Configuration

Before setting up configurations you will need to have 3 things:
- root certificate
- personal certificate
- personal certificate passphrase

You will get those things from FINA. You can find more info [here](https://github.com/robier/fiscalization).

```yaml
robier_sylius_croatian_fiscalization:
  environment: demo # 'demo' or 'production'
  disable_on_payment_codes:
    - bank_transfer
    - cash_on_delivery # depends on your configuration, by default it will be applied to all payment types 
  resending:
    max_attempts: 3
  certificate:
    demo:
      root_path: '%kernel.project_dir%/var/certificates/demo/root.txt'
      private_path: '%kernel.project_dir%/var/certificates/demo/FISKAL_1.p12'
      passphrase: '**********'
    production:
      root_path: '%kernel.project_dir%/var/certificates/production/root.txt'
      private_path: '%kernel.project_dir%/var/certificates/production/FISKAL_1.p12'
      passphrase: '**********'
  company:
    oib: 76915349123
    inside_tax_registry: true
  operator:
    oib: 28841868149 # if null (~), company.oib will be used instead
```

## Installation

Install application via composer:
```bash
composer require robier/sylius-croatian-fiscalization-plugin
```

Enable bundle in your sylius instance:
```php
<?php

return [
    # ...,
    Robier\SyliusCroatianFiscalizationPlugin\RobierSyliusCroatianFiscalizationPlugin::class => ['all' => true],
    # ...,
];
```

Copy configuration to your project:
```yaml
# config/packages/robier_sylius_croatian_fiscalization.yaml
imports:
  - { resource: "@RobierSyliusCroatianFiscalizationPlugin/Resources/config/config.yaml" }

robier_sylius_croatian_fiscalization:
  environment: demo # 'demo' or 'production'
  disable_on_payment_codes:
    - bank_transfer
    - cash_on_delivery # depends on your configuration, by default it will be applied to all payment types 
  resending:
    max_attempts: 3
  certificate:
    demo:
      root_path: '%kernel.project_dir%/var/certificates/demo/root.txt'
      private_path: '%kernel.project_dir%/var/certificates/demo/FISKAL_1.p12'
      passphrase: '**********'
    production:
      root_path: '%kernel.project_dir%/var/certificates/production/root.txt'
      private_path: '%kernel.project_dir%/var/certificates/production/FISKAL_1.p12'
      passphrase: '**********'
  company:
    oib: 76915349123
    inside_tax_registry: true
  operator:
    oib: 28841868149 # if null (~), company.oib will be used instead
```

Add certificates provided by FINA somewhere outside the git jurisdiction and change paths in package configuration file
- `certificate.demo.*`
- `certificate.production.*`

Import routes
```yaml
# config/routes/robier_sylius_croatian_fiscalization_plugin.yaml
robier_sylius_croatian_fiscalization_plugin:
  resource: "@RobierSyliusCroatianFiscalizationPlugin/Resources/config/routing.yaml"
```

Add new tables to the database:
```bash
bin/console doctrine:schema:update --dump-sql
# and if you are satisfied with updates then run
bin/console doctrine:schema:update --force
```

Create starting bill sequence by running command
```bash
bin/console robier:croatian-fiscalization:set-bill-sequence 1/POS1/1
```
Sequence should be defined by company internal act document but this library for now
only supports numbers like this is single toll device. Only first number increases
with every bill sent to FINA.

Clear your application cache
```bash
bin/console clear:cache
```

Setup TAX in your admin. You need to create TAX with code `vat` if you want it to be applied
on the bill that is sent to the FINA. Delivery is added as TAX exemption, as delivery
company will apply TAX on their bill.


## Local development (Docker)

Start project with

```bash
docker/env dev on
```

Enter container

```bash
docker/enter dev:php
# or
docker/run dev:node
# or
docker/enter dev:mysql
```

Stop project

```bash
docker/env dev off
```

Remove all containers and volumes

```bash
docker/env dev down -v -t0
```

When docker environment started, you can open your project by http://localhost:8889 in your browser.

You can change nginx port by adding `docker/.env` and override the value ie.
```dotenv
# docker/.env file
NGINX_HTTP_PORT=8888
```
Any value existing in `docker/.env.dist` can be overridden like that.

Default mysql credentials are:
```dotenv
MYSQL_USER=sylius
MYSQL_PASSWORD=nopassword
MYSQL_DATABASE=sylius
```

### TODO
[] add tests
[] add better documentation
[] add docker installation script
    [] setup database
    [] setup fixtures
    [] open project in browser when setup finishes

## Documentation

For a comprehensive guide on Sylius Plugins development please go to Sylius documentation,
there you will find the <a href="https://docs.sylius.com/en/latest/plugin-development-guide/index.html">Plugin Development Guide</a>, that is full of examples.

## Quickstart Installation

1. Run `composer create-project sylius/plugin-skeleton ProjectName`.

2. From the plugin skeleton root directory, run the following commands:

    ```bash
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn build)
    $ (cd tests/Application && APP_ENV=test bin/console assets:install public)
    
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:database:create)
    $ (cd tests/Application && APP_ENV=test bin/console doctrine:schema:create)
    ```

To be able to setup a plugin's database, remember to configure you database credentials in `tests/Application/.env` and `tests/Application/.env.test`.

## Usage

### Running plugin tests

  - PHPUnit

    ```bash
    vendor/bin/phpunit
    ```

  - PHPSpec

    ```bash
    vendor/bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    vendor/bin/behat --strict --tags="~@javascript"
    ```

  - Behat (JS scenarios)
 
    1. [Install Symfony CLI command](https://symfony.com/download).
 
    2. Start Headless Chrome:
    
      ```bash
      google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
      ```
    
    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:
    
      ```bash
      symfony server:ca:install
      APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
      ```
    
    4. Run Behat:
    
      ```bash
      vendor/bin/behat --strict --tags="@javascript"
      ```
    
  - Static Analysis
  
    - Psalm
    
      ```bash
      vendor/bin/psalm
      ```
      
    - PHPStan
    
      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

  - Coding Standard
  
    ```bash
    vendor/bin/ecs check src
    ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```
    
- Using `dev` environment:

    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
