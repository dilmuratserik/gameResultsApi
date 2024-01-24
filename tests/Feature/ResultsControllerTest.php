<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Member;
use App\Models\Result;

class ResultsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreResult()
    {
        // Создаем пользователя
        $email = 'test@example.com';
        $milliseconds = 1234;

        // Отправляем запрос на сохранение результата
        $response = $this->postJson('/api/results', [
            'email' => $email,
            'milliseconds' => $milliseconds
        ]);

        // Проверяем статус ответа
        $response->assertStatus(204);

        // Проверяем, что пользователь и результат созданы
        $this->assertDatabaseHas('members', ['email' => $email]);
        $this->assertDatabaseHas('results', ['milliseconds' => $milliseconds]);
    }


    public function testTopResults()
    {
        $member = Member::factory()->create(['email' => 'user@example.com']);
        foreach (range(1, 10) as $index) {
            Result::create([
                'member_id' => $member->id,
                'milliseconds' => rand(1000, 2000)
            ]);
        }

        $response = $this->getJson('/api/top-results');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'top' => [
                        '*' => ['email', 'milliseconds']
                    ]
                ]
            ]);


    }

}

