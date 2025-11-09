## Notes

This project fulfills all required requirements:

- File upload UI available at `/upload`
- Recent uploads display + auto refresh using polling
- File upload is queued and processed in background using Laravel Horizon + Redis
- CSV rows are cleaned to remove non-UTF8 characters
- Idempotent import implemented using `unique_key` column
- UPSERT support enabled using DB::table()->upsert()

### Setup

1. Run migration:
   php artisan migrate

2. Start queue worker:
   php artisan horizon

3. Start app:
   php artisan serve

Then open: http://127.0.0.1:8000/upload
