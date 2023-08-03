### How to run docker project:

1. Copy config file run:

```cp .env.example .env```

2. Build containers run:

```docker compose build```

3. Start docker containers run:

```docker compose up -d```

4. Enter docker workspace run:

```docker compose exec workspace bash```

5. Install database

```  php artisan dev:fresh ```

6. Add develop domain to hosts file

``` 127.0.0.1 bona.local ```
