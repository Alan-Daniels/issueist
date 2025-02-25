# Issueist

## Installing

1. Clone the repository
```
git clone https://github.com/Alan-Daniels/issueist.git
cd ./issueist
```

2. Install the dependencies
```
composer install
npm install
npm run build
```

3. copy `.env.example` to `.env` and generate the application key
```
cp .env.example .env
php artisan key:generate
```

4. Create the database & it's schema
```
touch ./database/database.sqlite
php artisan migrate:fresh
```

5. Add your github personal token to `.env` as `GITHUB_PERSONAL_TOKEN`.
Ensure the token has Read access to issues and metadata.

6. Run the development server with `composer run dev`.

## License

The Laravel framework as well as this project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
