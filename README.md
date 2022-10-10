## Api Assessment Projet Setup


- clone this repository.
- run 'composer install'.
- run 'cp .env.example .env'.
- run 'php artisan key:generate'.
- Add your database name to .env file.
- run 'php artisan jwt:secret' to generate jwt secret key.
- run 'php artisan migrate' to run migration files.
- run 'php artisan serve' to start the application.
- import the postman collection to view/test the assessment endpoints.