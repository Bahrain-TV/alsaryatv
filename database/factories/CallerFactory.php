<?php

namespace Database\Factories;

use App\Models\Caller;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Caller::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Arabic names for realistic Bahraini/Gulf caller data
        $arabicNames = [
            'أحمد محمد علي', 'فاطمة علي أحمد', 'محمد سالم عبدالله', 'نور علي محمد',
            'علي عبدالرحمن سالم', 'هند محمد علي', 'سارة خالد محمد', 'يوسف محمد علي',
            'حمد عبدالله محمد', 'رحاب علي أحمد', 'محمود سالم عبدالرحمن', 'ليلى محمد حمد',
            'خالد علي محمد', 'دينا أحمد محمود', 'عبدالله محمد علي', 'رانيا سالم عبدالله',
            'سليمان حمد علي', 'آية محمد سالم', 'عمر علي محمد', 'جنى أحمد علي',
            'راشد محمد سالم', 'سندس علي محمود', 'ناصر خالد أحمد', 'فرح محمد علي',
            'إبراهيم سالم عبدالله', 'ندى علي محمد', 'جاسم محمود علي', 'ريم أحمد خالد',
            'عبدالعزيز علي محمد', 'هيا محمد سالم', 'محمود علي أحمد', 'شيخة سالم محمد',
            'حسن محمد علي', 'حلا علي محمود', 'سامي خالد محمد', 'لمى أحمد علي',
            'مرتضى علي سالم', 'قمر محمد خالد', 'زايد محمد علي', 'نسرين علي أحمد',
            'طارق أحمد محمود', 'ريتاج محمد علي', 'معاذ علي محمد', 'سلام أحمد خالد',
            'فهد محمد علي', 'لارا علي محمود', 'إياد خالد محمد', 'عزيزة محمد علي',
            'وليد أحمد سالم', 'ضحى علي محمد', 'بندر محمود علي', 'ياسمين أحمد محمد',
            'قصي علي محمود', 'مريم محمد خالد', 'عاصم أحمد علي', 'ليندا سالم محمد',
            'هاني محمد علي', 'نادية علي أحمد', 'فايز خالد محمود', 'رفيقة محمد علي',
        ];

        return [
            'name' => $this->faker->randomElement($arabicNames),
            'phone' => $this->faker->phoneNumber(),
            'cpr' => $this->faker->unique()->numerify('#########'),

            'is_family' => false,
            'is_winner' => false,
            'ip_address' => $this->faker->ipv4(),
            'hits' => $this->faker->numberBetween(1, 100),
            'last_hit' => $this->faker->dateTimeThisYear(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'blocked']),
            'notes' => $this->faker->optional(0.7)->sentence(),
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the caller belongs to a family registration.
     */
    public function family(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_family' => true,
            ];
        });
    }

    /**
     * Indicate that the caller is a winner.
     */
    public function winner(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_winner' => true,
            ];
        });
    }
}
