# Nova Passport Manager
Manage Laravel Passport clients and tokens.

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
