<?php


namespace Maba\Bundle\GentleForceBundle\Tests\Unit\Listener;


use Maba\Bundle\GentleForceBundle\Listener\ListenerConfiguration;
use Maba\Bundle\GentleForceBundle\Listener\RolesMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RolesMatcherTest extends TestCase
{

    private $matcher;
    private $configuration;

    public function setUp(): void
    {
        $this->matcher = new RolesMatcher();
        $this->configuration = (new ListenerConfiguration())->setRoles(['ROLE_A', 'ROLE_B']);
    }

    /**
     * @param bool $expected
     * @param TokenStorageInterface $tokenStorage
     * @dataProvider dataProviderForRolesMatch
     */
    public function testRolesMatch($expected, TokenStorageInterface $tokenStorage)
    {
        $result = $this->matcher->matches($this->configuration, $tokenStorage);
        $this->assertEquals($expected, $result);
    }

    public function dataProviderForRolesMatch()
    {
        $noRoles = new TokenStorage();
        $noRoles->setToken((new AnonymousToken('test', 'test', [])));

        $noMatchingRoles = new TokenStorage();
        $noMatchingRoles->setToken((new AnonymousToken('test', 'test', ['ROLE_Z'])));

        $matchingRoles = new TokenStorage();
        $matchingRoles->setToken((new AnonymousToken('test', 'test', ['ROLE_A'])));
        return [
            'case_no_roles' => [
                false,
                $noRoles,
            ],
            'case_no_matching_roles' => [
                false,
                $noMatchingRoles,
            ],
            'case_matching_roles' => [
                true,
                $matchingRoles,
            ],
        ];
    }

}