import re

with open('scripts/ci-screenshots.js', 'r') as f:
    content = f.read()

# Fix the submit button locator which caused timeout
content = content.replace(
    "const submitBtn = page.locator('button[type=\"submit\"]').first();\n      if (await submitBtn.count() > 0) {\n        await submitBtn.click();\n      } else {\n        await page.keyboard.press('Enter');\n      }",
    "const submitBtn = page.locator('button[type=\"submit\"]').first();\n      if (await submitBtn.count() > 0) {\n        await submitBtn.click({ timeout: 5000 }).catch(e => page.keyboard.press('Enter'));\n      } else {\n        await page.keyboard.press('Enter');\n      }"
)

with open('scripts/ci-screenshots.js', 'w') as f:
    f.write(content)
