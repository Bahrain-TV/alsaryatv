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
        $firstNames = [
            'محمد', 'أحمد', 'عبدالله', 'سلمان', 'فهد', 'خالد', 'عبدالرحمن', 'ناصر', 'سلطان', 'علي',
            'إبراهيم', 'يوسف', 'حمد', 'جاسم', 'ياسر', 'سعد', 'صالح', 'مشعل', 'تركي', 'بدر',
            'فاطمة', 'نورة', 'سارة', 'مريم', 'عائشة', 'حصة', 'لطيفة', 'ريم', 'العنود', 'جواهر',
        ];

        $lastNames = [
            'الخليفة', 'آل ثاني', 'الصباح', 'آل نهيان', 'آل سعود', 'الكواري', 'الذوادي', 'الجودر', 'المناعي', 'البوديري',
            'بوجيري', 'السبيعي', 'الدوسري', 'القحطاني', 'العتيبي', 'المطيري', 'الحربي', 'العجمي', 'الشمري', 'الغامدي',
        ];

        $notes = [
            'متصل منتظم يشارك يومياً',
            'يحتاج لتأكيد البيانات عند الفوز',
            'لديه مشكلة في جودة الصوت أحياناً',
            'يشارك باسم العائلة عادةً',
            'فاز في العام الماضي بمبلغ بسيط',
            'من كبار المتابعين للبرنامج',
            'يتحدث بلهجة خفيفة ومحببة للمشاهدين',
            'يطلب دائماً التحية للمذيع',
            'مرشح قوي للفوز لليوم',
            'يشارك بكثافة منذ بداية البرنامج',
        ];

        $firstName = $this->faker->randomElement($firstNames);
        $middleName = $this->faker->randomElement($firstNames);
        $lastName = $this->faker->randomElement($lastNames);

        return [
            'name' => "$firstName $middleName $lastName",
            'phone' => '3'.$this->faker->numerify('#######'), // Gulf style phone starting with 3
            'cpr' => $this->faker->unique()->numerify('#########'),

            'is_family' => $this->faker->boolean(20),
            'is_winner' => false,
            'ip_address' => $this->faker->ipv4(),
            'hits' => $this->faker->numberBetween(1, 150),
            'last_hit' => $this->faker->dateTimeThisMonth(),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive', 'blocked']), // Priority to active
            'notes' => $this->faker->optional(0.6)->randomElement($notes),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
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
