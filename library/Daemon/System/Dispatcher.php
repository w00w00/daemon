<?php
namespace Daemon\System;

use Daemon\Action\Action;
use Daemon\System\Daemon\Starter;

/**
 * The dispatcher dispatches an requested action, wich gota be an instance of
 * action interface, and the shell arguments are used to do so
 *
 * @todo use the escapeshellargs to improve security
 * @author Jhonatan Teixeira
 */
class Dispatcher
{
    protected $_class;
    protected $_arguments = array();
    protected static $_inst;

    /**
     * construtor, pega os argumentos passados para o cli e inicia um daemon
     *
     * @global array $argv
     */
    private function __construct()
    {
        global $argv;
        array_shift($argv);
        
        if (is_array($argv) and count($argv) > 0) {
            if (!isset($this->_class)) {
                $this->setClass(array_shift($argv));
            }

            $this->setArguments($argv);
        } else {
            die("nothing set to run\n");
        }
    }

    /**
     * implementa singleton
     *
     * @return Dispatcher
     */
    public static function getInstance()
    {
        if (!self::$_inst instanceof  self) {
            self::$_inst = new self();
        }

        return self::$_inst;
    }

    /**
     * previne clonagem do singleton
     */
    private function __clone()
    {
        
    }

    /**
     * seta o nome da classe de daemon
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->_class = ucfirst((string) $class);
    }

    /**
     * seta os argumntos vindos do shell
     *
     * @param array $args 
     */
    public function setArguments(array $args)
    {
        $this->_arguments = $args;
    }

    /**
     * pega o nome do daemon
     *
     * @return string
     */
    public function getDaemonName()
    {
        return $this->_class;
    }

    /**
     * retorna os argumentos passados
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Retorna um argumento especifico
     *
     * @param string $name
     * @return string
     */
    public function getArgument($name)
    {
        return $this->_arguments[$name];
    }

    /**
     * pega o valor de um argumento
     *
     * @param string $name
     * @return string
     */
    public function getArgumentValue($name)
    {
        reset($this->_arguments);
        while ($curr = current($this->_arguments)) {
            if ($curr == $name) {
                return next($this->_arguments);
            }

            next($this->_arguments);
        }

        return false;
    }

    /**
     * verifica se um determinado argumento existe
     *
     * @param string $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return isset($this->_arguments[$name]);
    }

    /**
     * verifica se algum argumento foi passado pelo shell
     *
     * @return bool
     */
    public function hasArguments()
    {
        return (bool) $this->_arguments;
    }

    /**
     * Faz dispatch de um determinado daemon conforme solicitado via shell
     */
    public function dispatchDaemon()
    {
        $daemon = Daemon::factory($this->_class, $this);

        $systemDaemon = new Starter($this);
        $systemDaemon->start();
        $daemon->run();
        $systemDaemon->stop();
    }

    public function dispatchAction()
    {
        Action::factory($this->_class, $this);
    }
}