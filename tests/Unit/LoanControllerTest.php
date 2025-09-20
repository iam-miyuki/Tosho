<?php

namespace App\Tests;

use App\Kernel;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoanControllerTest extends WebTestCase
{
    
    public function testGetLoanIndexPage(): void
    {
        $client = static::createClient(); 
        $res = $client->request('GET', '/loan/');

        //dd($res);
        //TODO mock db repositories ...
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div[data-tab="family"]'); // or another selector from your html view
    }
    

    
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }



    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        $env = $options['env'] ?? 'test';
        $debug = (bool) ($options['debug'] ?? true);

        return new Kernel($env, $debug);
    }
}
