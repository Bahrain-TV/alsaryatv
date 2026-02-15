# Seeders Data Directory

This directory contains CSV files used for seeding initial data in production.

## Callers Seed CSV

**File**: `callers_seed.csv`

This CSV file is used to seed initial caller data when the database is completely empty.

### CSV Format

```csv
Name,Phone,CPR,Status,Is Winner,Hits,Last Hit,Created At,Updated At
```

### Columns

| Column | Description | Required | Default |
|--------|-------------|----------|---------|
| Name | Caller's full name | Yes | - |
| Phone | Phone number | Yes | - |
| CPR | Civil Personal Registration number (Bahraini ID) | Yes | - |
| Status | active, inactive, or blocked | No | active |
| Is Winner | 0 or 1 (boolean) | No | 0 |
| Hits | Number of times called | No | 0 |
| Last Hit | Last call timestamp (YYYY-MM-DD HH:MM:SS) | No | null |
| Created At | Creation timestamp | No | current time |
| Updated At | Last update timestamp | No | current time |

### Usage

1. **For initial deployment**: Leave the CSV empty (headers only) or add initial caller data
2. **For data import**: Export caller data from production backup, place in this file
3. **Seeding**: Run `php artisan db:seed --class=CallerSeeder`

### Important Notes

- The seeder will **ONLY** run if the `callers` table is completely empty (0 records)
- This prevents accidental data overwrites in production
- If the CSV file doesn't exist, an empty one will be created automatically
- Failed imports are logged to Laravel logs

### Example Row

```csv
"حسن أحمد الجودر","39123456","950123456","active",0,5,"2026-02-15 10:30:00","2026-01-01 00:00:00","2026-02-15 10:30:00"
```

### Exporting Current Data

To export current production data for use as seed data:

```bash
php artisan app:persist-data --export-csv
```

Then copy the latest export from `storage/app/private/backups/callers/` to this directory as `callers_seed.csv`.
