import re

with open('tests/Feature/DataPreservationTest.php', 'r') as f:
    content = f.read()

# Add test data setup
setup_method = """
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure there is at least some data for the persist-data command
        Caller::factory()->count(5)->create(['is_winner' => false, 'status' => 'active']);
        Caller::factory()->count(2)->create(['is_winner' => true, 'status' => 'active']);
    }
"""

if "protected function setUp" not in content:
    content = content.replace('class DataPreservationTest extends TestCase\n{\n    use RefreshDatabase;\n', 'class DataPreservationTest extends TestCase\n{\n    use RefreshDatabase;\n' + setup_method)

with open('tests/Feature/DataPreservationTest.php', 'w') as f:
    f.write(content)
