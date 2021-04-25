#After cloning the repo

    1. run command -> composer install
    2. run command -> cp .env.example .env
    3. setup mysql db credentials in .env
    4. run command -> php artisan storage:link
    5. to run the jobs -> php artisan queue:work
    6. to run tests -> php artisan test --parallel

