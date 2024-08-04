<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/books', content: '{}');
        $this->assertJsonStringEqualsJsonString('{"error":{"title":"This value should not be null."}}', $client->getResponse()->getContent());
        
        $crawler = $client->request('POST', '/books', content: '{"title": ""}');
        $this->assertJsonStringEqualsJsonString('{"error":{"title":"This value is too short. It should have 3 characters or more."}}', $client->getResponse()->getContent());
        
        $crawler = $client->request('POST', '/books', content: '{"title": "Book", "year": "111"}');
        $this->assertJsonStringEqualsJsonString('{"error":{"year":"This value should have exactly 4 characters."}}', $client->getResponse()->getContent());
        
        $crawler = $client->request('POST', '/books', content: '{"title": "Book", "year": "1924"}');
        $this->assertStringContainsString('book_id', $client->getResponse()->getContent());
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $book_id = $response['book_id'];
        
        $crawler = $client->request('DELETE', "/books/$book_id");
        $this->assertStringContainsString('success', $client->getResponse()->getContent());
        
        $crawler = $client->request('DELETE', "/books/$book_id");
        $this->assertStringContainsString('not found', $client->getResponse()->getContent());
        
    }
}
