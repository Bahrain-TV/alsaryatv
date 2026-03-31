import re

with open('tests/Feature/FilamentDashboardFeatureTest.php', 'r') as f:
    content = f.read()

# Add role => admin flag to the factory creation
content = content.replace(
    "'is_admin' => true,\n        ]);",
    "'is_admin' => true,\n            'role' => 'admin',\n        ]);"
)

with open('tests/Feature/FilamentDashboardFeatureTest.php', 'w') as f:
    f.write(content)
