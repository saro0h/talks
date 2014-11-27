<?php

namespace PoleDev\AppBundle\Tests\SecurityPHPUnit;

use PoleDev\AppBundle\Security\GithubAuthenticator;

class GithubAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateToken()
    {
        $guzzleRequest = $this->getMockBuilder('Guzzle\Http\Message\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $guzzleResponse = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $guzzleRequest
            ->method('send')
            ->willReturn($guzzleResponse)
        ;

        $guzzleResponse
            ->expects($this->once())
            ->method('json')
            ->willReturn(array('access_token' => 'a_fake_access_token'))
        ;

        $client = $this->getMockBuilder('Guzzle\Service\Client')->getMock();

        $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $router->method('generate')
            ->with('admin',[], true)
            ->willReturn('http://domain.name')
        ;
        $endpoint = '/login/oauth/access_token';

        $client->method('post')
            ->with($endpoint, [], [
                'client_id' => '',
                'client_secret' => '',
                'code' => '',
                'redirect_uri' => 'http://domain.name'
            ])
            ->willReturn($guzzleRequest)
        ;

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();


        $githubAuthenticator = new GithubAuthenticator($client, $router, $logger, '', '');
        $token = $githubAuthenticator->createToken($request, 'secure_area');

        $this->assertSame('a_fake_access_token', $token->getCredentials());
        $this->assertSame('secure_area', $token->getProviderKey());
        $this->assertSame('anon.', $token->getUser());
        $this->assertSame(array(), $token->getRoles());
        $this->assertSame(false, $token->isAuthenticated());
        $this->assertSame(array(), $token->getAttributes());
    }
}
