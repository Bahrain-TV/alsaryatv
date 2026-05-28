import re

with open('app/Models/User.php', 'r') as f:
    content = f.read()

# Add FilamentUser interface
content = content.replace(
    "class User extends Authenticatable",
    "use Filament\\Models\\Contracts\\FilamentUser;\nuse Filament\\Panel;\n\nclass User extends Authenticatable implements FilamentUser"
)

# Add canAccessPanel method
filament_method = """
    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
"""

content = content.replace(
    "    public function isSuperAdmin(): bool\n    {\n        return $this->hasRole('super_admin');\n    }\n",
    "    public function isSuperAdmin(): bool\n    {\n        return $this->hasRole('super_admin');\n    }\n" + filament_method
)

with open('app/Models/User.php', 'w') as f:
    f.write(content)
