# Questions data handling

Guidelines for keeping question content secure and deployment-safe.

- Never commit plaintext `PROJECT_DOCS/questions.json`.
- Use `php artisan questions:encrypt` to produce `PROJECT_DOCS/questions.json.enc` which is safe to commit.
- On deploy, run `php artisan questions:import --file=PROJECT_DOCS/questions.json.enc` (the seeder runs this automatically during `db:seed`).
- To edit: modify local copy of `PROJECT_DOCS/questions.json` (do not commit), then run `php artisan questions:encrypt` and verify the `.enc` file, then commit `.enc`.
- For local safety, enable git hooks: `git config core.hooksPath .githooks` to enable the pre-commit script that prevents committing plaintext.
