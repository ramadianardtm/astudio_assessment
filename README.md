# ASTUDIO Assessment - Ramadian Arditama Harianto
Assessment for PHP Laravel Developer position at ASTUDIO
# Setup Instruction
After finish cloning the project do these steps:
1. Run the following command to install required dependencies:
   ```sh
   composer install
   ```
2. Reset database migrations and generate seeders
   ```sh
   php artisan migrate:fresh --seed
   ```
3. Generate application key
   ```sh
   php artisan key:generate
   ```
4. Install and setup Laravel Passport
   ```sh
   php artisan passport:install
   ```
5. Start the development server
   ```sh
   php artisan serve
   ```
