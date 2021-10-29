<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function itRegisterAUser()
    {
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password(8);
        $response = $this->postJson('/api/user/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password
        ]);
        $response->assertCreated()
            ->assertJsonPath('data.name', $name)
            ->assertJsonPath('data.email', $email);
    }

    /**
     * @test
     */
    public function itCanNotLoginAUnknownUser()
    {
        $email = $this->faker->email;
        $password = $this->faker->password(8);
        $response = $this->postJson('/api/user/login', ['email' => $email, 'password' => $password]);
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function itLoginAUser()
    {
        $name = $this->faker->name;
        $email = $this->faker->email;
        $password = $this->faker->password(8);
        $registerResponse = $this->postJson('/api/user/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password
        ]);
        $registerResponse->assertCreated()->assertJsonPath('data.name', $name)->assertJsonPath('data.email', $email);

        $loginResponse = $this->postJson('/api/user/login', ['email' => $email, 'password' => $password]);
        $loginResponse->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('data.email', $email);
    }
}
