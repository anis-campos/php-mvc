<?php

namespace Psr\Container {

    /**
     * Describes the interface of a container that exposes methods to read its entries.
     * @see https://www.php-fig.org/psr/psr-11/
     * @package Psr\Container
     */
    interface ContainerInterface
    {
        /**
         * Finds an entry of the container by its identifier and returns it.
         *
         * @param string $id Identifier of the entry to look for.
         *
         * @return mixed Entry.
         * @throws ContainerExceptionInterface Error while retrieving the entry.
         * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
         */
        public function get($id);

        /**
         * Returns true if the container can return an entry for the given identifier.
         * Returns false otherwise.
         *
         * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
         * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
         *
         * @param string $id Identifier of the entry to look for.
         *
         * @return bool
         */
        public function has($id);

        /**
         * Register a single instance to store to the container. All dependencies are resolved on first 'get'
         * @param string $abstract Abstract class to register with a implementation, or singleton if not abstract and implementation is null.
         * @param string|null $implementation Implementation of $abstract class or null
         * @return ContainerInterface fluent interface
         */
        public function single(string $abstract, string $implementation = null): ContainerInterface;

        /**
         * Register a type to be created a each get, using a factory if given
         * @param string $abstract
         * @param Factory|null $factory Factory to create instance of $abstract. if null, will be resolved by reflection
         *
         * @return ContainerInterface fluent interface
         */
        public function factory(string $abstract, Factory $factory = null): ContainerInterface;
    }

    /**
     * Base interface representing a generic exception in a container.
     * @package Psr\Container
     */
    interface ContainerExceptionInterface
    {
    }

    /**
     * No entry was found in the container.
     * @package Psr\Container
     */
    interface NotFoundExceptionInterface extends ContainerExceptionInterface
    {
    }

    /**
     * Interface Factory to customize creation of a class
     * @package Psr\Container
     */
    interface Factory
    {
        /**
         * @param ContainerInterface $container to resolve dependencies
         * @return mixed Instance
         */
        public function create(ContainerInterface $container);
    }

}

namespace Psr\Container\Implementation {

    use Exception;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\ContainerInterface;
    use Psr\Container\Factory;
    use Psr\Container\NotFoundExceptionInterface;
    use ReflectionClass;
    use ReflectionException;
    use ReflectionParameter;
    use Throwable;

    /**
     * Class NotFoundException thrown when an 'abstract' class is not registered
     * @package Psr\Container\Implementation
     */
    class NotFoundException extends Exception implements NotFoundExceptionInterface
    {

        /**
         * NotFoundException constructor.
         * @param string $abstract
         */
        public function __construct(string $abstract)
        {
            parent::__construct("The class '$abstract' has not been registered and cannot be resolved'");

        }
    }

    /**
     * Class RegistrationException thrown when a class is wrongly registered
     * @package Psr\Container\Implementation
     */
    class RegistrationException extends Exception implements ContainerExceptionInterface
    {
        /**
         * NotFoundException constructor.
         * @param Registration $registration
         * @param Throwable $reason
         */
        public function __construct(Registration $registration, Throwable $reason = null)
        {
            $abstract = $registration->getAbstract();
            if ($registration instanceof FactoryRegistration) {
                if ($registration->getFactory() == null) {
                    parent::__construct("The class '$abstract' is registered to the default factory but cannot be resolved'", 0, $reason);
                } else
                    parent::__construct("The class '$abstract' is registered to the default factory but cannot be resolved'", 0, $reason);

            } else if ($registration instanceof SingleRegistration)
                parent::__construct("The class '$abstract' is registered to '{$registration->getImplementation()}' but cannot be resolved'", 0, $reason);

        }
    }

    /**
     * Class ParameterNotFoundException thrown when the dependecy of a class cannot be resolved
     * @package Psr\Container\Implementation
     */
    class ParameterNotFoundException extends Exception implements NotFoundExceptionInterface
    {

        /**
         * NotFoundException constructor.
         * @param string $parameterName
         * @param int $parameterPos
         * @param string $className
         * @param Throwable $origin
         * @param string $constructor
         */
        public function __construct(string $parameterName, int $parameterPos, string $className, Throwable $origin, string $constructor)
        {
            parent::__construct("The class '$className' needs a unregistered parameter name={$parameterName}  at position={$parameterPos} in constructor:\n\t=>$constructor", 0, $origin);
        }
    }


    /**
     * Class Container is the Dependency Injection container of the framework
     * @package Psr\Container\Implementation
     */
    class Container implements ContainerInterface
    {

        /**
         * @var Registration[]
         */
        protected $registrations = array();


        /**
         * Finds an entry of the container by its identifier and returns it.
         *
         * @param string $id Identifier of the entry to look for.
         *
         * @return mixed Entry.
         * @throws ContainerExceptionInterface Error while retrieving the entry.
         *
         * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
         */
        public function get($id)
        {
            if (!$this->has($id)) throw new NotFoundException($id);

            $instance = $this->resolve($id);

            return $instance;
        }

        /**
         * Returns true if the container can return an entry for the given identifier.
         * Returns false otherwise.
         *
         * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
         * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
         *
         * @param string $id Identifier of the entry to look for.
         *
         * @return bool
         */
        public function has($id)
        {
            $registration = $this->getRegistrationOrNull($id);
            return isset($registration) || //is registered
                ($registration instanceof SingleRegistration && $registration->isResolved()) || // was instanced previously
                $this->canBeResolved($id);
        }

        /**
         * Check if a class is abstract and registered or constructable
         * @param string $className
         * @return bool
         */
        private function canBeResolved(string $className)
        {

            // if class is not found ( with auto loading enabled ) set and return null
            if (!class_exists($className, true)) {
                return false;
            }

            try {
                $reflection = new ReflectionClass($className);
                if (!$reflection->isInstantiable()) {
                    return false;
                }
                return true;

            } catch (ReflectionException $e) {
                //this is not supposed to happen
                return false;
            }

        }

        /**
         * Register a single instance to store to the container. All dependencies are resolved on first 'get'
         * @param string $abstract Abstract class to register with a implementation, or singleton if not abstract and implementation is null.
         * @param string|null $implementation Implementation of $abstract class or null
         * @return ContainerInterface fluent interface
         */
        public function single(string $abstract, string $implementation = null): ContainerInterface
        {
            $this->registrations[$abstract] = new SingleRegistration($abstract, $implementation);
            return $this;
        }

        /**
         * Register a type to be created a each get, using a factory if given
         * @param string $abstract
         * @param Factory|null $factory Factory to create instance of $abstract. if null, will be resolved by reflection
         *
         * @return ContainerInterface fluent interface
         */
        public function factory(string $abstract, Factory $factory = null): ContainerInterface
        {
            $this->registrations[$abstract] = new FactoryRegistration($abstract, $factory);
            return $this;
        }

        /**
         * Try to resolve a class and its dependencies
         * @param string $className
         * @return mixed
         * @throws ContainerExceptionInterface
         */
        private function resolve(string $className)
        {
            $registration = $this->getRegistrationOrNull($className);
            if (isset($registration) && ($e = $registration->getException())) return $e;

            $instance = null;
            if ($registration instanceof SingleRegistration) {
                $instance = $this->resolveSingle($registration);
            } elseif ($registration instanceof FactoryRegistration) {
                $instance = $this->resolveFactory($registration);
            } elseif ($instance == null) {
                // if class is not found ( with auto loading enabled ) store exception and return

                $reflection = null;
                try {
                    $reflection = new ReflectionClass($className);
                } catch (ReflectionException $e) {
                    //this is not supposed to happen
                }

                $constructor = $reflection->getConstructor();
                if ($constructor == null) {
                    return $reflection->newInstance();
                }

                $params = $constructor->getParameters();

                try {
                    $resolvedParams = $this->resolveParams($params, $className, $constructor->__toString());
                } catch (ContainerExceptionInterface $e) {
                    $registration->setException($e);
                    return $e;
                }

                return $reflection->newInstanceArgs($resolvedParams);
            }

            if ($instance instanceof ContainerExceptionInterface) {
                throw $instance;
            }

            return $instance;
        }


        /**
         * Creates singletons as it resolves a class only once, and store the result ( instance or exception ).
         * @param SingleRegistration $registration
         * @return mixed|ContainerExceptionInterface
         */
        private function resolveSingle(SingleRegistration $registration)
        {
            if (($e = $registration->getException())) return $e;

            $implementation = $registration->getImplementation();

            //this return already resolved instance or exception
            if ($registration->isResolved()) {
                return $registration->getInstance();
            }

            // if class is not found ( with auto loading enabled ) store exception and return
            if (!class_exists($implementation, true)) {
                $e = new RegistrationException($registration, new Exception("Implementation class isn't loaded"));
                $registration->setException($e);
                return $e;
            }

            $reflection = null;
            try {
                $reflection = new ReflectionClass($implementation);
                if (!$reflection->isInstantiable()) {
                    $e = new RegistrationException($registration, new Exception("Implementation cannot be instantiated'"));
                    $registration->setException($e);
                    return $e;
                }

            } catch (ReflectionException $e) {
                //this is not supposed to happen
            }


            $constructor = $reflection->getConstructor();
            if ($constructor instanceof ReflectionClass) {
                return $reflection->newInstance();
            }

            $params = $constructor->getParameters();

            try {
                $resolvedParams = $this->resolveParams($params, $implementation, $constructor->__toString());
            } catch (ContainerExceptionInterface $e) {
                $registration->setException($e);
                return $e;
            }

            return $reflection->newInstanceArgs($resolvedParams);

        }

        /**
         * Return a new instance or exception at every resolution
         * @param FactoryRegistration $registration
         * @return ContainerExceptionInterface|mixed
         */
        private function resolveFactory(FactoryRegistration $registration)
        {
            $factory = $registration->getFactory();

            $implementation = $registration->getAbstract();
            if ($factory == null) {

                if (!$this->canBeResolved($implementation)) {
                    $e = new NotFoundException($implementation);
                    $registration->setException($e);
                }

                // if class is not found ( with auto loading enabled ) store exception and return
                if (!class_exists($implementation, true)) {
                    $e = new RegistrationException($registration, new Exception("Implementation class isn't loaded"));
                    $registration->setException($e);
                    return $e;
                }

                $reflection = null;
                try {
                    $reflection = new ReflectionClass($implementation);
                    if (!$reflection->isInstantiable()) {
                        $e = new RegistrationException($registration, new Exception("Implementation cannot be instantiated'"));
                        $registration->setException($e);
                        return $e;
                    }

                } catch (ReflectionException $e) {
                    //this is not supposed to happen
                }


                $constructor = $reflection->getConstructor();
                if ($constructor instanceof ReflectionClass) {
                    return $reflection->newInstance();
                }

                $params = $constructor->getParameters();

                try {
                    $resolvedParams = $this->resolveParams($params, $implementation, $constructor->__toString());
                } catch (ContainerExceptionInterface $e) {
                    $registration->setException($e);
                    return $e;
                }

                return $reflection->newInstanceArgs($resolvedParams);
            } else {
                return $factory->create($this);
            }
        }


        /**
         * @param ReflectionParameter[] $params
         * @param string $className
         * @param string $constructor
         * @return array
         * @throws ContainerExceptionInterface
         */
        private function resolveParams(array $params, string $className, string $constructor)
        {
            $resolved = [];


            foreach ($params as $index => $param) {
                try {
                    //this is null if the parameter is not typed
                    $hintedClass = $param->getClass();
                    if ($hintedClass == null) {
                        if ($param->isOptional()) {
                            if ($param->isDefaultValueAvailable()) {
                                $resolved[] = $param->getDefaultValue();
                            } else {
                                throw new Exception("Default value is not available");
                            }
                        } else
                            throw new Exception("Default value is not available");
                    } else {
                        $this->get($hintedClass);
                    }

                } catch (Exception $ex) {
                    throw new ParameterNotFoundException($param->getName(), $index, $className, $ex, $constructor);
                }
            }
            return $resolved;
        }

        /**
         * Get registration or null
         * @param $id
         * @return Registration|null
         */
        private function getRegistrationOrNull($id): ?Registration
        {
            return array_key_exists($id, $this->registrations) ? $this->registrations[$id] : null;
        }
    }

    class SingleRegistration extends Registration
    {
        /**
         * @var string
         */
        private $implementation;


        /**
         * @var mixed
         */
        private $instance;

        /**
         * SingleRegistration constructor.
         * @param string $abstract
         * @param string $implementation
         */
        public function __construct(string $abstract, string $implementation)
        {
            parent::__construct($abstract, self::TYPE_SINGLE);
            $this->implementation = $implementation;
        }

        /**
         * @return bool
         */
        public function isResolved(): bool
        {
            return isset($this->instance);
        }

        /**
         * Target implementing abstract class
         * @return string|null
         */
        public function getImplementation(): string
        {
            return $this->implementation;
        }

        /**
         * @return mixed
         */
        public function getInstance()
        {
            return $this->instance;
        }

        /**
         * @return ContainerExceptionInterface|null
         */
        public function getException(): ContainerExceptionInterface
        {
            return $this->exception;
        }

        /**
         * @param ContainerExceptionInterface|null $exception
         */
        public function setException(ContainerExceptionInterface $exception)
        {
            $this->exception = $exception;
        }

        /**
         * @param mixed|null $instance
         */
        public function setInstance($instance)
        {
            $this->instance = $instance;
        }


    }

    class FactoryRegistration extends Registration
    {
        /**
         * @var Factory|null
         */
        private $factory;

        /**
         * FactoryRegistration constructor.
         * @param string $abstract
         * @param Factory|null $factory
         */
        public function __construct(string $abstract, Factory $factory = null)
        {
            parent::__construct($abstract, self::TYPE_FACTORY);
            $this->factory = $factory;
        }


        /**
         * @return Factory|null
         */
        public function getFactory()
        {
            return $this->factory;
        }


    }


    abstract class Registration
    {

        const TYPE_SINGLE = 0;
        const TYPE_FACTORY = 1;

        protected $abstract;
        protected $type;
        /**
         * @var ContainerExceptionInterface | null
         */
        protected $exception = null;


        /**
         * Registration constructor.
         * @param $abstract
         * @param $type
         */
        public function __construct($abstract, $type)
        {
            $this->abstract = $abstract;
            $this->type = $type;
        }

        public function setException(ContainerExceptionInterface $exception)
        {
            $this->exception = $exception;
        }

        /**
         * @return ContainerExceptionInterface|null
         */
        public function getException(): ContainerExceptionInterface
        {
            return $this->exception;
        }

        /**
         * Get abstract class name
         * @return string
         */
        final  public function getAbstract()
        {
            return $this->abstract;
        }


        /**
         * TYPE_SINGLE or TYPE_FACTORY
         * @return int
         */
        final public function getType(): int
        {
            return $this->type;
        }

    }

}