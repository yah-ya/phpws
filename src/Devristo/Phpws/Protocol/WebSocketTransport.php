<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chris
 * Date: 10/6/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Devristo\Phpws\Protocol;

use Devristo\Phpws\Framing\WebSocketFrameInterface;
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Evenement\EventEmitter;
use React\Stream\WritableStreamInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

abstract class WebSocketTransport extends EventEmitter implements WebSocketTransportInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     *
     * @var WebSocketConnection
     */
    protected $_socket = null;
    protected $_cookies = array();
    public $parameters = null;
    protected $_role = WebsocketTransportRole::CLIENT;

    protected $_eventManger;

    public function __construct(WritableStreamInterface $socket)
    {
        $this->_socket = $socket;
        $this->_id = uniqid("connection-");
    }

    public function getIp()
    {
        return $this->_socket->getRemoteAddress();
    }

    public function getId()
    {
        return $this->_id;
    }

    protected function setRequest(Request $request){
        $this->request = $request;
    }

    protected function setResponse(Response $response){
        $this->response = $response;
    }

    public function getHandshakeRequest(){
        return $this->request;
    }

    public function getHandshakeResponse(){
        return $this->response;
    }

    public function setRole($role)
    {
        $this->_role = $role;
    }

    public function getSocket()
    {
        return $this->_socket;
    }

    public function setLogger(LoggerInterface $logger){
        $this->logger = $logger;
    }

    public function sendFrame(WebSocketFrameInterface $frame)
    {
        if ($this->_socket->write($frame->encode()) === false)
            return false;

        return true;
    }

    public function sendMessage(WebSocketMessageInterface $msg)
    {
        foreach ($msg->getFrames() as $frame) {
            if ($this->sendFrame($frame) === false)
                return false;
        }

        return true;
    }
}