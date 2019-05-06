<?php
class Container
{
    // 用于提供实例的回调函数
    protected $bindings = [];

    // 绑定接口和生成相应实例的回调函数
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (! $concrete instanceof Closure) {
                // 如果提供的参数不是回调函数，则产生默认的回调函数
            $concrete = $this->getClosure($abstract, $concrete);
        }
        $this->bindings[$abstract] = compact('concrete', 'shared');
    }
    
    // 默认生成实例的回调函数
    protected function getClosure($abstract, $concrete)
    {
        // 生成实例的回调函数，$container 一般为IOC容器对象，在调用回调生成实例时提供
        // 即 build函数中的 $concrete($this)
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            $method = ($abstract == $concrete) ? 'build' : 'make';

            // 调用的是容器的build或者make方法生成实例
            return $container->$method($concrete, $parameters);
        };
    }
    // 生成实例对象，首先解决接口和要实例化类之间的依赖关系
     public function make($abstract)
    {
        $concrete = $this->getConcrete($abstract);
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }
        return $object;
    }
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }
    // 获取绑定的回调函数
    protected function getConcrete($abstract)
    {
        if (!isset($this->bindings[$abstract])) {
            return $abstract;
        }

        return $this->bindings[$abstract]['concrete'];
    }

    // 实例化对象
    public function build($concrete)
    {
        // 服务能否被服务提供者注册为实例
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        // $concrete就是类名
        $reflector = new ReflectionClass($concrete);
        if (! $reflector->isInstantiable()) {
            echo $message = "Target [$concrete] is not instantiable.";
        }

        // 获取构造信息
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $concrete;
        }

        // 获取构造函数依赖的输入参数
        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies(
            $dependencies
        );

        return $reflector->newInstanceArgs($instances);
    }

    // 解决通过反射机制实例化对象时的依赖
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];
        foreach ($dependencies as $dependency) {
            $results[] = is_null($class = $dependency->getClass())? NULL: $this->resolveClass($dependency);
        }
        return $results;
    }
    protected function resolveClass(ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);
    }
}


interface Visit
{
    public function go();
}


class Car implements Visit
{
    public function go()
    {
        echo "Drive car";
    }
}

interface Travel
{
    
    public function visitOut();

}

class Traveller implements Travel
{
    protected $trafficTool;
    public function __construct(Visit $trafficTool)
    {
        $this->trafficTool = $trafficTool;
    }
    public function visitOut()
    {
        $this->trafficTool->go();
    }
}

$app = new Container();
// 容器的填充
$app->bind('Visit', "Car");
$app->bind("Travel", "Traveller");
// 通过容器实现依赖注入，完成类的实例化
$tra = $app->make("Traveller");
$tra->visitOut();
