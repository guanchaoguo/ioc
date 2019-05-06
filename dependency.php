<?php
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
class Train implements Visit
{
    public function go()
    {
        echo "Take train";
    }
}
// 实现该类需要依赖交通工具实例
class Traveller
{
    protected $trafficTool;
    public function __construct($trafficTool)
    {
       // 依赖产生
        $this->trafficTool = new Car();
    }
    public function visitOut()
    {
        $this->trafficTool->go();
    }
}
$traveller = new Traveller();
$traveller->visitOut();
