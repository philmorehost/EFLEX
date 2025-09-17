<?php
// tests/UserTest.php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $pdo;
    private $userModel;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        // Get a fresh, clean database for each test
        $this->pdo = createTestDatabase();
        $this->userModel = new User($this->pdo);
    }

    public function testCreateAndFindUser()
    {
        // 1. Arrange: Define the user data
        $username = 'johndoe';
        $email = 'john.doe@example.com';
        $password = 'password123';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userType = 'buyer';

        // 2. Act: Create the user
        $result = $this->userModel->create($username, $email, $passwordHash, $userType);

        // Assert that creation was successful
        $this->assertTrue($result);

        // 3. Act: Find the user by email
        $foundUser = $this->userModel->findByEmail($email);

        // Assert that the user was found and data is correct
        $this->assertNotFalse($foundUser);
        $this->assertEquals($username, $foundUser['username']);
        $this->assertEquals($email, $foundUser['email']);
        $this->assertEquals($userType, $foundUser['user_type']);

        // 4. Act: Find a non-existent user
        $notFoundUser = $this->userModel->findByEmail('nonexistent@example.com');

        // Assert that the user was not found
        $this->assertFalse($notFoundUser);
    }

    public function testFindById()
    {
        // Arrange: Create a user first
        $this->userModel->create('janedoe', 'jane.doe@example.com', 'hash', 'seller');
        $lastId = $this->pdo->lastInsertId();

        // Act: Find the user by ID
        $foundUser = $this->userModel->findById($lastId);

        // Assert
        $this->assertNotFalse($foundUser);
        $this->assertEquals('janedoe', $foundUser['username']);
    }
}
