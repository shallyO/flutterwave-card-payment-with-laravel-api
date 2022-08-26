<?php

namespace Tests\Unit;

use Tests\TestCase;


class CreateCustomerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    public function test_create_customer(){

        $response = $this->postJson('api/v1/createCustomer', [
            "fullname" => "Philip Tomson",
            "email" => "phil9h7e@gmail.com",
            "phonenumber"=> "07932906080"

        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);


    }
}
