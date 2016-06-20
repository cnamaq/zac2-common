<?php
/**
 * Created by PhpStorm.
 * User: fohl
 * Date: 27/04/16
 * Time: 12:24
 */

namespace Zac2\Common;

use Symfony\Component\DependencyInjection\Container;

abstract class DicAware
{
    /**
     * @var Container
     */
    protected $dic;

    /**
     * DicAware constructor.
     * @param Container $dic
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @return Container
     */
    public function getDic()
    {
        return $this->dic;
    }

    /**
     * @param string $fileName
     * @param null $path
     * @return array
     */
    protected function getConfig($fileName, $path = null)
    {
        $extension = explode('.', $fileName);
        switch (end($extension)) {
            case 'yml' :
                $reader = new \Zend\Config\Reader\Yaml(array('Spyc','YAMLLoadString'));
                return $reader->fromFile($this->getConfigPath($path) . $fileName);
            case 'ini' :
                break;
            case 'xml' :
                break;
            default:
                break;
        }
    }

    /**
     * @param null $path
     * @return string
     */
    protected function getConfigPath($path = null)
    {
        $env = (APPLICATION_ENV == 'devubuntu') ? 'development' : APPLICATION_ENV;
        $path = APPLICATION_PATH . '/configs/' . $env . '/' . $path . '/';

        return str_replace('//', '/', $path);
    }
    
}
