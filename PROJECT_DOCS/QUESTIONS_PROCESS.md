# Questions data handling

Guidelines for keeping question content secure and deployment-safe.

- Never commit plaintext `PROJECT_DOCS/questions.json`.
- Use `php artisan questions:encrypt` to produce `PROJECT_DOCS/questions.json.enc` which is safe to commit.
- `publish.sh` will now detect a local plaintext `PROJECT_DOCS/questions.json`, encrypt it locally (via `php artisan questions:encrypt`) and upload `PROJECT_DOCS/questions.json.enc` to the server automatically during publish. If an `.enc` already exists, `publish.sh` will upload that instead.
- On deploy, `deploy.sh` will preserve `PROJECT_DOCS/questions.json.enc` (it no longer removes the whole `PROJECT_DOCS` folder) and will run `php artisan questions:import --file=PROJECT_DOCS/questions.json.enc --truncate` (requires `APP_KEY` on server) immediately after running migrations.
- To edit: modify local copy of `PROJECT_DOCS/questions.json` (do not commit), then run `php artisan questions:encrypt` and verify the `.enc` file, then commit `.enc`.
- For local safety, enable git hooks: `git config core.hooksPath .githooks` to enable the pre-commit script that prevents committing plaintext.
