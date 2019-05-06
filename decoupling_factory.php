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
    
    class TrafficToolFactory
    {
        public function createTrafficTool($name)
        {
            switch ($name) {
                case 'Car' :
                    return new Car();
                    break;
                case 'Train':
                    return new Train();
                    break;
                default:
                    exit("set traffic tool error");
                    break;
            }
        }
    }
    class Traveller
    {
        protected $trafficTool;
        public function __construct($trafficTool)
        {
            // 通过工厂生产依赖的交通工具实例
            $factory = new TrafficToolFactory();
            $this->trafficTool = $factory->createTrafficTool($trafficTool);
        }
        public function visitOut()
        {
            $this->trafficTool->go();
        }
    }
    $traveller = new Traveller('Car');
    $traveller->visitOut();
