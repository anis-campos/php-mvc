<?php


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\Implementation\Container;

class ContainerTest extends TestCase
{
    public function testRegisterFactoryIsFluent(): void
    {
        $c = new Container();
        $b = $c->factory('a');
        $this->assertInstanceOf(Container::class, $b);
        $this->assertSame($c, $b);
    }

    public function testCanFindRegistration(): void
    {
        $c = new Container();
        $c->factory('a');
        $this->assertTrue($c->has('a'));
    }

    public function testHasReturnFalseIfNoRegistration(): void
    {
        $c = new Container();
        $this->assertFalse($c->has('a'));
    }

    public function testHasReturnTrueOnConcreteType(): void
    {
        $c = new Container();
        $this->assertTrue($c->has(Container::class));
    }

    public function testHasReturnFalseOnInterface(): void
    {
        $c = new Container();
        $this->assertFalse($c->has(ContainerInterface::class));
    }

    public function testRegisterSingleIsFluent(): void
    {
        $c = new Container();
        $b = $c->single('a', 'b');
        $this->assertInstanceOf(Container::class, $b);
        $this->assertSame($c, $b);
    }

    public function testCanResolveItself()
    {
        $c = new Container();
        $resolve = $c->get(Container::class);
        $this->assertInstanceOf(Container::class, $resolve);

    }
}