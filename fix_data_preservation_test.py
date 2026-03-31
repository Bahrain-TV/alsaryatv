import re

with open('tests/Feature/DataPreservationTest.php', 'r') as f:
    content = f.read()

content = content.replace('use Tests\\TestCase;', 'use Tests\\TestCase;\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;')
content = content.replace('class DataPreservationTest extends TestCase\n{', 'class DataPreservationTest extends TestCase\n{\n    use RefreshDatabase;\n')

with open('tests/Feature/DataPreservationTest.php', 'w') as f:
    f.write(content)
