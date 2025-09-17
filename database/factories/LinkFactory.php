<?php


namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => Str::random(7),
            'target_url' => $this->faker->url(),
            'max_clicks' => null,
            'click_count' => 0,
            'expires_at' => null,
            'is_active' => true,
            'last_access_at' => null,
        ];
    }
}
