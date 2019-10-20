# Nova Passport Manager
Manage Laravel Passport clients and tokens.

![Passport Manager for Laravel Nova](https://repository-images.githubusercontent.com/203880869/911a9c80-f295-11e9-8b40-9c5a7aefebd2)

## Requirements
- PHP >= 7.1.3
- Laravel 5.8.* (https://laravel.com)
- Laravel Nova 2.* (https://nova.laravel.com)

## Installation
1. ```sh
   composer require "genealabs/nova-passport-manager:*"
   ```
2. Add the tool to your `app\Providers\NovaServiceProvider.php`:
   ```php
    public function tools()
    {
        return [
            // ...
            new GeneaLabs\NovaPassportManager\NovaPassportManager,
            // ...
        ];
    }
   ```

## Usage
### Nova Tools
#### Passport Management
<img width="1633" alt="Screen Shot 2019-08-22 at 4 55 38 PM" src="https://user-images.githubusercontent.com/1791050/63557564-171ca000-c4fe-11e9-99af-d0b07342c310.png">
