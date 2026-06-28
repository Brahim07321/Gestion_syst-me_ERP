name: Laravel CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: stock_test
          MYSQL_ROOT_PASSWORD: root
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping -h 127.0.0.1 -uroot -proot"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=5

    env:
      APP_ENV: testing
      APP_KEY: base64:SomeRandomKeyHereSomeRandomKeyHereSomeRandomKey=
      APP_DEBUG: true
      APP_URL: http://127.0.0.1

      DB_CONNECTION: mysql
      DB_HOST: 127.0.0.1
      DB_PORT: 3306
      DB_DATABASE: stock_test
      DB_USERNAME: root
      DB_PASSWORD: root

      CACHE_STORE: array
      SESSION_DRIVER: array
      QUEUE_CONNECTION: sync
      MAIL_MAILER: array
      BROADCAST_CONNECTION: log
      FILESYSTEM_DISK: local

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, bcmath, pdo_mysql, xml, ctype, fileinfo, tokenizer
          coverage: none

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Prepare Laravel
        run: |
          cp .env.example .env
          php artisan config:clear

      - name: Run migrations
        run: php artisan migrate:fresh --force

      - name: Run tests
        run: php artisan test