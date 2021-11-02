## About Minimal eCommerce

### Description

This is a minimal ecommerce application's API

### Requirement

1. php 7.3^
2. composer

### Installation

1. clone the repo
2. Ready the env file
3. Configure the database
4. run 'php artisan migrate'
5. run 'composer update'
6. run 'php artisan db:seed'
7. An admin will be created by seeder.
    1. email:    admin@admin.com
    2. password:    admin

### PHPUnit Test

1. run 'php artisan test'

### API Instruction

1. Admin
    1. Create Product for Buyers to view
    2. Update the product

2. Buyer
    1. Buyer register/login
    2. Place order with product
    3. Edit the order until admin approve
