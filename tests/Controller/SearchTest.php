<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearchTest extends WebTestCase
{
    public function testMaterielSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/materiels?q=test');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="q"]');
        $this->assertSame('test', $crawler->filter('input[name="q"]')->attr('value'));
    }

    public function testMagasinSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/magasins?q=test');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="q"]');
        $this->assertSame('test', $crawler->filter('input[name="q"]')->attr('value'));
    }

    public function testMouvementSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/mouvements?q=test');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="q"]');
        $this->assertSame('test', $crawler->filter('input[name="q"]')->attr('value'));
    }

    public function testFournisseurSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fournisseurs?q=test');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="q"]');
        $this->assertSame('test', $crawler->filter('input[name="q"]')->attr('value'));
    }

    public function testStockMagasinSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/stock-magasins?q=test');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="q"]');
        $this->assertSame('test', $crawler->filter('input[name="q"]')->attr('value'));
    }
}
